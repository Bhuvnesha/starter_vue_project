<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeCrud extends Command
{
    protected $signature = 'make:crud {name} {--fields=}';
    protected $description = 'Generate full CRUD with Inertia + Vue (PRO version)';

    public function handle()
    {
        $name = $this->argument('name');
        $fieldsOption = $this->option('fields');

        if (!$fieldsOption) {
            $this->error("Please provide --fields=");
            return;
        }

        $fields = collect(explode(',', $fieldsOption))->map(function ($field) {
            [$name, $type] = explode(':', $field);
            return ['name' => $name, 'type' => $type];
        });

        $model = Str::studly($name);
        $plural = Str::plural(Str::kebab($name));

        $this->call('make:model', ['name' => $model, '-m' => true]);
        $this->call('make:controller', ['name' => "{$model}Controller"]);

        $this->updateMigration($model, $fields);
        $this->updateModel($model, $fields);
        $this->createController($model, $plural, $fields);
        $this->createVue($model, $plural, $fields);
        $this->addRoutes($model, $plural);

        $this->info("🔥 CRUD PRO generated successfully!");
    }

    private function generateFormFields($fields)
    {
        return collect($fields)
            ->map(fn ($f) => "{$f['name']}: ''")
            ->implode(",\n    ");
    }

    private function createController($model, $plural, $fields)
{
    $modelVar = \Illuminate\Support\Str::camel($model);
    $path = app_path("Http/Controllers/{$model}Controller.php");

    $validation = collect($fields)
        ->map(fn ($f) => "'{$f['name']}' => 'required'")
        ->implode(",\n            ");

    $content = <<<PHP
<?php

namespace App\Http\Controllers;

use App\Models\\$model;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class {$model}Controller extends Controller
{
    public function index(Request \$request)
    {
        \$query = {$model}::query();

        if (\$request->search) {
            \$query->where('name', 'like', '%' . \$request->search . '%');
        }

        \$items = \$query->latest()->paginate(5)->withQueryString();

        return Inertia::render('{$model}/Index', [
            'items' => \$items,
            'filters' => \$request->only('search')
        ]);
    }

    public function create()
    {
        return Inertia::render('{$model}/Create');
    }

    public function store(Request \$request)
    {
        \$data = \$request->validate([
            $validation
        ]);

        {$model}::create(\$data);

        return redirect()->route('{$plural}.index')->with('success', 'Created successfully');
    }

    public function edit({$model} \${$modelVar})
    {
        return Inertia::render('{$model}/Edit', [
            'item' => \${$modelVar}
        ]);
    }

    public function update(Request \$request, {$model} \${$modelVar})
    {
        \$data = \$request->validate([
            $validation
        ]);

        \${$modelVar}->update(\$data);

        return redirect()->route('{$plural}.index')->with('success', 'Updated successfully');
    }

    public function destroy({$model} \${$modelVar})
    {
        \${$modelVar}->delete();

        return back()->with('success', 'Deleted successfully');
    }

    public function bulkDelete(Request \$request)
    {
        {$model}::whereIn('id', \$request->ids)->delete();

        return back()->with('success', 'Selected items deleted');
    }

    public function export(): StreamedResponse
    {
        \$items = {$model}::all();

        \$headers = ['Content-Type' => 'text/csv'];

        \$callback = function() use (\$items) {
            \$file = fopen('php://output', 'w');

            fputcsv(\$file, ['ID', 'Created At']);

            foreach (\$items as \$item) {
                fputcsv(\$file, [\$item->id, \$item->created_at]);
            }

            fclose(\$file);
        };

        return response()->stream(\$callback, 200, \$headers);
    }
}
PHP;

    \Illuminate\Support\Facades\File::put($path, $content);
}

    private function updateMigration($model, $fields)
    {
        $table = Str::snake(Str::pluralStudly($model));
        $file = collect(File::files(database_path('migrations')))
            ->first(fn ($f) => str_contains($f->getFilename(), $table));

        $schema = "\$table->id();\n";

        foreach ($fields as $field) {
            $schema .= "\$table->{$field['type']}('{$field['name']}');\n";
        }

        $schema .= "\$table->timestamps();";

        $content = File::get($file);

        $content = preg_replace(
            '/Schema::create\(.*?\{(.*?)\}\);/s',
            "Schema::create('$table', function (Blueprint \$table) {\n            $schema\n        });",
            $content
        );

        File::put($file, $content);
    }

    private function updateModel($model, $fields)
    {
        $path = app_path("Models/{$model}.php");

        $fillable = collect($fields)
            ->pluck('name')
            ->map(fn ($f) => "'$f'")
            ->implode(', ');

        $content = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class $model extends Model
{
    use HasFactory;

    protected \$fillable = [$fillable];
}
PHP;

        File::put($path, $content);
    }

   

    private function createVue($model, $plural, $fields)
    {
        $path = resource_path("js/Pages/$model");
        \Illuminate\Support\Facades\File::ensureDirectoryExists($path);

        \Illuminate\Support\Facades\File::put("$path/Index.vue", $this->indexVue($model, $plural, $fields));
        \Illuminate\Support\Facades\File::put("$path/Create.vue", $this->formVue($model, $plural, $fields, 'create'));
        \Illuminate\Support\Facades\File::put("$path/Edit.vue", $this->formVue($model, $plural, $fields, 'edit'));
    }

   private function formVue($model, $plural, $fields, $type)
{
    $formFields = $this->generateFormFields($fields);

    $inputs = collect($fields)->map(function ($f) {
        return "<input v-model=\"form.{$f['name']}\" placeholder=\"{$f['name']}\" class=\"border p-2 w-full mb-2\" />";
    })->implode("\n");

    return <<<VUE
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    item: Object
});

const form = useForm({
    $formFields
});

if (props.item) {
    Object.assign(form, props.item);
}

function submit() {
    if (props.item) {
        form.put('/$plural/' + props.item.id);
    } else {
        form.post('/$plural');
    }
}
</script>

<template>
<AdminLayout>

<h1 class="text-xl mb-4">$type $model</h1>

<form @submit.prevent="submit">
$inputs

<button class="bg-blue-600 text-white px-4 py-2 rounded">
Submit
</button>
</form>

</AdminLayout>
</template>
VUE;
}


    private function indexVue($model, $plural, $fields)
    {
        $columns = collect($fields)
            ->pluck('name')
            ->map(fn ($f) => "<td class='p-3'>{{ item.$f }}</td>")
            ->implode("\n");

        $headers = collect($fields)
            ->pluck('name')
            ->map(fn ($f) => "<th class='p-3'>" . ucfirst($f) . "</th>")
            ->implode("\n");

        return <<<VUE
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ items:Object, filters:Object });

const search = ref(props.filters.search || '');
const selected = ref([]);
const selectAll = ref(false);

watch(search, (value) => {
    router.get('/$plural', { search: value }, { preserveState:true, replace:true });
});

function toggleAll() {
    if (selectAll.value) {
        selected.value = props.items.data.map(i => i.id);
    } else {
        selected.value = [];
    }
}

function bulkDelete() {
    if (!confirm('Delete selected items?')) return;

    router.post('/$plural/bulk-delete', {
        ids: selected.value
    });
}

function exportCSV() {
    window.location.href = '/$plural/export';
}
</script>

<template>
<AdminLayout>

<div class="flex justify-between mb-4">
<h1 class="text-xl font-bold">$model</h1>

<div class="flex gap-2">
<Link href="/$plural/create" class="bg-blue-600 text-white px-4 py-2 rounded">+ Create</Link>
<button @click="exportCSV" class="bg-green-600 text-white px-4 py-2 rounded">Export</button>
</div>
</div>

<input v-model="search" class="border p-2 mb-4 w-full" placeholder="Search..." />

<div v-if="selected.length" class="mb-4">
<button @click="bulkDelete" class="bg-red-600 text-white px-4 py-2 rounded">
Delete Selected ({{ selected.length }})
</button>
</div>

<table class="w-full bg-white shadow rounded">
<thead>
<tr>
<th><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
$headers
<th>Actions</th>
</tr>
</thead>

<tbody>
<tr v-for="item in items.data" :key="item.id">
<td><input type="checkbox" :value="item.id" v-model="selected" /></td>
$columns
<td class="flex gap-2">
<Link :href="\`/$plural/\${item.id}/edit\`" class="text-blue-600">Edit</Link>
<button @click="router.delete('/$plural/'+item.id)" class="text-red-600">Delete</button>
</td>
</tr>
</tbody>
</table>

<Pagination :links="items.links" />

</AdminLayout>
</template>
VUE;
    }

    private function addRoutes($model, $plural)
    {
        $controller = "{$model}Controller";

        $routesPath = base_path('routes/web.php');
        $routesContent = File::get($routesPath);

        // Prevent duplicate routes
        if (str_contains($routesContent, $controller)) {
            $this->warn("⚠️ Routes already exist for $model");
            return;
        }

        $routeBlock = <<<PHP

    // $model CRUD Routes
    Route::resource('$plural', \\App\\Http\\Controllers\\$controller::class);
    Route::post('$plural/bulk-delete', [\\App\\Http\\Controllers\\$controller::class, 'bulkDelete'])->name('$plural.bulkDelete');
    Route::get('$plural/export', [\\App\\Http\\Controllers\\$controller::class, 'export'])->name('$plural.export');

    PHP;

        File::append($routesPath, $routeBlock);

        $this->info("✅ Routes added successfully!");
    }
}
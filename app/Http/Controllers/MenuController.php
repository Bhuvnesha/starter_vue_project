<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $items = $query->latest()->paginate(5)->withQueryString();

        return Inertia::render('Menu/Index', [
            'items' => $items,
            'filters' => $request->only('search')
        ]);
    }

    public function create()
    {
        return Inertia::render('Menu/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'description' => 'required'
        ]);

        Menu::create($data);

        return redirect()->route('menus.index')->with('success', 'Created successfully');
    }

    public function edit(Menu $menu)
    {
        return Inertia::render('Menu/Edit', [
            'item' => $menu
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'description' => 'required'
        ]);

        $menu->update($data);

        return redirect()->route('menus.index')->with('success', 'Updated successfully');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return back()->with('success', 'Deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        Menu::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Selected items deleted');
    }

    public function export(): StreamedResponse
    {
        $items = Menu::all();

        $headers = ['Content-Type' => 'text/csv'];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Created At']);

            foreach ($items as $item) {
                fputcsv($file, [$item->id, $item->created_at]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
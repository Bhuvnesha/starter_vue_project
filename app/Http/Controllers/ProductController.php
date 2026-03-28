<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $items = $query->latest()->paginate(5)->withQueryString();

        return Inertia::render('Product/Index', [
            'items' => $items,
            'filters' => $request->only('search')
        ]);
    }

    public function create()
    {
        return Inertia::render('Product/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'price' => 'required'
        ]);

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Created successfully');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Product/Edit', [
            'item' => $product
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required',
            'price' => 'required'
        ]);

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        Product::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Selected items deleted');
    }

    public function export(): StreamedResponse
    {
        $items = Product::all();

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
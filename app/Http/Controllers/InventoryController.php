<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $products = Product::query()
            ->select(['id', 'code', 'barcode', 'name', 'quantity', 'unit', 'sale_price', 'minimum_stock'])
            ->when($term, fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('name')->paginate(18)->withQueryString();

        return view('inventory.index', [
            'products' => $products,
            'term' => $term,
            'stats' => [
                'products' => Product::count(),
                'units' => Product::sum('quantity'),
                'low' => Product::whereColumn('quantity', '<=', 'minimum_stock')->count(),
                'value' => Product::selectRaw('COALESCE(SUM(quantity * cost_price), 0) as total')->value('total'),
            ],
        ]);
    }

    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $products = Product::query()
            ->select(['id', 'code', 'barcode', 'name', 'quantity', 'unit', 'sale_price', 'minimum_stock'])
            ->when($term, fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('name')->limit(18)->get()
            ->map(fn (Product $product) => [
                'id' => $product->id, 'name' => $product->name, 'code' => $product->code,
                'barcode' => $product->barcode, 'quantity' => (float) $product->quantity,
                'unit' => $product->unit, 'sale_price' => (float) $product->sale_price,
                'low_stock' => $product->isLowStock(), 'edit_url' => route('inventory.edit', $product),
            ]);

        return response()->json([
            'query' => $term, 'count' => $products->count(), 'products' => $products,
            'create_url' => route('inventory.create', ['name' => $term]),
        ]);
    }

    public function create()
    {
        return view('inventory.form', ['product' => new Product(), 'movement' => true]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'code' => ['nullable', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:80'], 'unit' => ['required', 'string', 'max:12'],
            'quantity' => ['required', 'numeric', 'min:0'], 'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'], 'sale_price' => ['nullable', 'numeric', 'min:0'],
            'ncm' => ['nullable', 'string', 'max:20'], 'cfop' => ['nullable', 'string', 'max:20'], 'notes' => ['nullable', 'string'],
        ]);
        $data['minimum_stock'] = $data['minimum_stock'] ?? 0;
        $data['cost_price'] = $data['cost_price'] ?? 0;
        $data['sale_price'] = $data['sale_price'] ?? 0;
        $data['name'] = mb_strtoupper($data['name'], 'UTF-8');
        $product = Product::create($data);
        return redirect()->route('inventory.index', ['q' => $product->name])->with('success', 'Produto cadastrado com sucesso.');
    }

    public function edit(Product $product)
    {
        return view('inventory.form', ['product' => $product, 'movement' => true]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'code' => ['nullable', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:80'], 'unit' => ['required', 'string', 'max:12'],
            'quantity' => ['required', 'numeric', 'min:0'], 'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'], 'sale_price' => ['nullable', 'numeric', 'min:0'],
            'ncm' => ['nullable', 'string', 'max:20'], 'cfop' => ['nullable', 'string', 'max:20'], 'notes' => ['nullable', 'string'],
        ]);
        $data['minimum_stock'] = $data['minimum_stock'] ?? 0;
        $data['cost_price'] = $data['cost_price'] ?? 0;
        $data['sale_price'] = $data['sale_price'] ?? 0;
        $data['name'] = mb_strtoupper($data['name'], 'UTF-8');
        $product->update($data);
        return back()->with('success', 'Cadastro atualizado com sucesso.');
    }

    public function movement(Request $request, Product $product)
    {
        $data = $request->validate(['type' => ['required', 'in:entry,exit'], 'quantity' => ['required', 'numeric', 'gt:0'], 'reason' => ['nullable', 'string', 'max:120'], 'notes' => ['nullable', 'string']]);
        if ($data['type'] === 'exit' && $data['quantity'] > $product->quantity) return back()->withErrors(['quantity' => 'A saída não pode ser maior que o estoque atual.']);
        DB::transaction(function () use ($product, $data) {
            StockMovement::create(['product_id' => $product->id, ...$data]);
            $product->increment('quantity', $data['type'] === 'entry' ? $data['quantity'] : -$data['quantity']);
        });
        return back()->with('success', 'Movimentação registrada.');
    }
}

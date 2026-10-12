<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Support\SecureRouteId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class InventoryController extends Controller
{
    private const EXPORT_COLUMNS = [
        'code' => 'Código',
        'barcode' => 'Código de barras',
        'name' => 'Produto',
        'quantity' => 'Quantidade em estoque',
        'unit' => 'Unidade',
        'cost_price' => 'Preço de custo',
        'sale_price' => 'Preço de venda',
        'minimum_stock' => 'Estoque mínimo',
        'ncm' => 'NCM',
        'cfop' => 'CFOP',
        'cest' => 'CEST',
        'notes' => 'Observações',
    ];

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

    public function missingSalePrice(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $products = Product::query()
            ->select(['id', 'code', 'barcode', 'name', 'quantity', 'unit', 'sale_price', 'minimum_stock'])
            ->where('quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('sale_price')->orWhere('sale_price', '<=', 0);
            })
            ->when($term, fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->paginate(18)
            ->withQueryString();

        return view('reports.missing-sale-price', [
            'products' => $products,
            'term' => $term,
            'heading' => 'Produtos sem preço de venda',
            'title' => 'Relatório · Produtos sem preço de venda',
        ]);
    }

    public function outOfStock(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $products = Product::query()
            ->select(['id', 'code', 'barcode', 'name', 'quantity', 'unit', 'sale_price', 'minimum_stock'])
            ->where('quantity', '<=', 0)
            ->when($term, fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->paginate(18)
            ->withQueryString();

        return view('reports.out-of-stock', [
            'products' => $products,
            'term' => $term,
            'heading' => 'Produtos sem estoque',
            'title' => 'Relatório · Produtos sem estoque',
        ]);
    }

    public function exportForm()
    {
        return view('reports.export-products', [
            'columns' => self::EXPORT_COLUMNS,
            'heading' => 'Exportar produtos',
            'title' => 'Exportar produtos · Excel',
        ]);
    }

    public function export(Request $request)
    {
        $allowedColumns = array_keys(self::EXPORT_COLUMNS);
        $data = $request->validate([
            'columns' => ['required', 'array', 'min:1'],
            'columns.*' => ['string', 'in:'.implode(',', $allowedColumns)],
        ]);

        $selectedColumns = array_values(array_unique($data['columns']));
        $products = Product::query()
            ->select($selectedColumns)
            ->orderBy('name')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Produtos');

        foreach ($selectedColumns as $index => $column) {
            $letter = $this->excelColumnLetter($index + 1);
            $sheet->setCellValue($letter.'1', self::EXPORT_COLUMNS[$column]);
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        foreach ($products as $rowIndex => $product) {
            foreach ($selectedColumns as $columnIndex => $column) {
                $sheet->setCellValue(
                    $this->excelColumnLetter($columnIndex + 1).($rowIndex + 2),
                    $product->{$column},
                );
            }
        }

        $sheet->getStyle('A1:'.$this->excelColumnLetter(count($selectedColumns)).'1')->getFont()->setBold(true);
        $writer = new Xls($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            'produtos-'.now()->format('Y-m-d-His').'.xls',
            ['Content-Type' => 'application/vnd.ms-excel'],
        );
    }

    private function excelColumnLetter(int $column): string
    {
        $letter = '';
        while ($column > 0) {
            $remainder = ($column - 1) % 26;
            $letter = chr(65 + $remainder).$letter;
            $column = intdiv($column - 1, 26);
        }

        return $letter;
    }

    public function bulkEdit(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $products = Product::query()
            ->select(['id', 'code', 'barcode', 'name', 'quantity', 'unit', 'ncm', 'cfop'])
            ->when($term, fn ($query) => $query->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%");
            }))
            ->orderBy('name')
            ->limit(100)
            ->get();

        return view('inventory.bulk-edit', [
            'products' => $products,
            'term' => $term,
            'heading' => 'Edição em lote',
            'title' => 'Edição em lote · NCM e CFOP',
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'selected_tokens' => ['required', 'array', 'min:1'],
            'selected_tokens.*' => ['required', 'string'],
            'ncm' => ['nullable', 'string', 'max:20'],
            'cfop' => ['nullable', 'integer', 'between:0,9999'],
        ]);

        $updated = DB::transaction(function () use ($data) {
            $count = 0;
            $changes = [
                'ncm' => $data['ncm'] ?? null,
                'cfop' => $data['cfop'] ?? null,
            ];
            if ((string) ($changes['cfop'] ?? '') !== '5405') {
                $changes['cest'] = null;
            }

            foreach (array_unique($data['selected_tokens']) as $token) {
                $productId = SecureRouteId::decrypt($token);
                Product::whereKey($productId)->firstOrFail()->update($changes);
                $count++;
            }

            return $count;
        });

        if ($updated === 0) {
            return back()->withErrors(['items' => 'Selecione pelo menos um produto para atualizar.']);
        }

        return back()->with('success', "{$updated} produto(s) atualizado(s) com sucesso.");
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
            'ncm' => ['nullable', 'string', 'max:20'], 'cfop' => ['nullable', 'integer', 'between:0,9999'], 'cest' => ['nullable', 'integer', 'between:0,99999999'], 'notes' => ['nullable', 'string'],
        ]);
        if (filled($data['cest'] ?? null) && (string) ($data['cfop'] ?? '') !== '5405') {
            return back()->withInput()->withErrors(['cest' => 'O CEST só pode ser preenchido quando o CFOP for 5405.']);
        }
        $data['cest'] = (string) ($data['cfop'] ?? '') === '5405' ? ($data['cest'] ?? null) : null;
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
            'ncm' => ['nullable', 'string', 'max:20'], 'cfop' => ['nullable', 'integer', 'between:0,9999'], 'cest' => ['nullable', 'integer', 'between:0,99999999'], 'notes' => ['nullable', 'string'],
        ]);
        if (filled($data['cest'] ?? null) && (string) ($data['cfop'] ?? '') !== '5405') {
            return back()->withInput()->withErrors(['cest' => 'O CEST só pode ser preenchido quando o CFOP for 5405.']);
        }
        $data['cest'] = (string) ($data['cfop'] ?? '') === '5405' ? ($data['cest'] ?? null) : null;
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

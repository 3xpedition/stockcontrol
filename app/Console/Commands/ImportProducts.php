<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportProducts extends Command
{
    protected $signature = 'estoque:import {file : Caminho do arquivo XLSX} {--replace : Substitui os produtos importados anteriormente}';
    protected $description = 'Importa produtos da aba Cadastro Produtos';

    public function handle(): int
    {
        $file = $this->argument('file');
        if (! is_file($file)) { $this->error("Arquivo não encontrado: {$file}"); return self::FAILURE; }
        $sheet = IOFactory::load($file)->getSheetByName('Cadastro Produtos');
        if (! $sheet) { $this->error('Aba Cadastro Produtos não encontrada.'); return self::FAILURE; }
        $rows = $sheet->toArray(null, true, true, true);
        if ($this->option('replace')) {
            Product::query()->delete();
        }
        $count = 0;
        foreach (array_slice($rows, 1) as $offset => $row) {
            $name = trim((string) ($row['C'] ?? ''));
            if ($name === '') continue;
            Product::updateOrCreate(['source_row' => $offset + 2], [
                'code' => trim((string) ($row['A'] ?? '')) ?: null,
                'barcode' => trim((string) ($row['B'] ?? '')) ?: null, 'name' => $name,
                'quantity' => $this->number($row['D'] ?? 0), 'unit' => trim((string) ($row['E'] ?? 'UN')) ?: 'UN',
                'cost_price' => $this->number($row['F'] ?? 0), 'sale_price' => $this->number($row['G'] ?? 0),
                'minimum_stock' => $this->number($row['H'] ?? 0), 'notes' => $row['I'] ?? null,
                'ncm' => $row['J'] ?? null, 'cfop' => $row['K'] ?? null,
            ]); $count++;
        }
        $this->info("{$count} produtos importados da aba Cadastro Produtos.");
        return self::SUCCESS;
    }

    private function number(mixed $value): float
    {
        if (is_numeric($value)) return (float) $value;
        return (float) str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', (string) $value));
    }
}

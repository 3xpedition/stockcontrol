<?php

use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/produtos/busca', [InventoryController::class, 'search'])->name('inventory.search');
Route::get('/produtos/novo', [InventoryController::class, 'create'])->name('inventory.create');
Route::post('/produtos', [InventoryController::class, 'store'])->name('inventory.store');
Route::get('/produtos/{product}/editar', [InventoryController::class, 'edit'])->name('inventory.edit');
Route::put('/produtos/{product}', [InventoryController::class, 'update'])->name('inventory.update');
Route::post('/produtos/{product}/movimentacoes', [InventoryController::class, 'movement'])->name('inventory.movement');
Route::get('/relatorios/produtos-sem-preco-de-venda', [InventoryController::class, 'missingSalePrice'])->name('reports.missing-sale-price');
Route::get('/relatorios/produtos-sem-estoque', [InventoryController::class, 'outOfStock'])->name('reports.out-of-stock');
Route::get('/relatorios/exportar-produtos', [InventoryController::class, 'exportForm'])->name('reports.export-form');
Route::post('/relatorios/exportar-produtos', [InventoryController::class, 'export'])->name('reports.export');
Route::get('/produtos/edicao-em-lote', [InventoryController::class, 'bulkEdit'])->name('inventory.bulk-edit');
Route::put('/produtos/edicao-em-lote', [InventoryController::class, 'bulkUpdate'])->name('inventory.bulk-update');

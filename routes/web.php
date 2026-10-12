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

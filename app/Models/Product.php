<?php

namespace App\Models;

use App\Support\SecureRouteId;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'source_row', 'code', 'barcode', 'name', 'quantity', 'unit', 'cost_price',
        'sale_price', 'minimum_stock', 'notes', 'ncm', 'cfop', 'cest',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'minimum_stock' => 'decimal:3',
            'cfop' => 'integer',
            'cest' => 'integer',
        ];
    }

    public function isLowStock(): bool
    {
        return (float) $this->quantity <= (float) $this->minimum_stock;
    }

    public function getRouteKey(): string
    {
        return SecureRouteId::encrypt($this->getKey());
    }
}

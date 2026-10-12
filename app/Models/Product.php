<?php

namespace App\Models;

use App\Support\SecureRouteId;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        ];
    }

    protected function ncm(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): ?string => $this->numericCode($value),
        );
    }

    protected function cest(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): ?string => $this->numericCode($value),
        );
    }

    public function isLowStock(): bool
    {
        return (float) $this->quantity <= (float) $this->minimum_stock;
    }

    public function getRouteKey(): string
    {
        return SecureRouteId::encrypt($this->getKey());
    }

    private function numericCode(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $value);

        return $digits === '' ? null : $digits;
    }
}

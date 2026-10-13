<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

class ProductNameNormalizationTest extends TestCase
{
    public function test_special_characters_are_removed_when_setting_a_product_name(): void
    {
        $product = Product::make(['name' => ' Café @ Premium # 500g! ']);

        $this->assertSame('Cafe Premium 500g', $product->name);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->index();
            $table->string('barcode')->nullable()->index();
            $table->string('name')->index();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->string('unit', 12)->default('UN');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->decimal('minimum_stock', 12, 3)->default(0);
            $table->text('notes')->nullable();
            $table->string('ncm', 20)->nullable();
            $table->string('cfop', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('products'); }
};

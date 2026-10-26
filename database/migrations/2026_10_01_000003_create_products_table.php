<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('name', 150)->index();
            $table->decimal('purchase_price', 14, 2)->default(0);
            $table->decimal('selling_price', 14, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['category_id', 'stock']);
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
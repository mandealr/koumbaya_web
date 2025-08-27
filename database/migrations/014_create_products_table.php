<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description');
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('XAF');
            $table->string('image', 255)->nullable();
            $table->bigInteger('category_id')->unsigned();
            $table->bigInteger('merchant_id')->unsigned();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->enum('sale_mode', ['direct', 'lottery'])->default('direct');
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Index pour performance
            $table->index('category_id');
            $table->index('merchant_id');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('sale_mode');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
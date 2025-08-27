<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->json('images')->nullable(); // Stockage des URL d'images
            $table->decimal('price', 15, 2); // Prix de vente direct
            $table->decimal('ticket_price', 8, 2); // Prix du ticket de tombola
            $table->integer('min_participants')->default(300); // Minimum 300 comme spécifié
            $table->integer('stock_quantity')->default(1);
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('merchant_id'); // ID du commerçant
            $table->enum('status', ['draft', 'active', 'sold', 'expired'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            
            $table->primary('id');
            $table->index('category_id');
            $table->index('merchant_id');
            $table->index('status');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

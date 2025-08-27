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
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_id')->unique(); // new_user, lottery_winner, etc.
            $table->string('name');
            $table->text('description');
            $table->boolean('enabled')->default(true);
            $table->string('category')->default('general'); // general, user, lottery, payment
            $table->timestamps();
            
            $table->index(['category', 'enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_types');
    }
};

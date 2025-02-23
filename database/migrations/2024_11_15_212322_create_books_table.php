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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->string('title');
            $table->longText('description');
            $table->string('cover');
            $table->foreignId('first_category_id')->constrained('first_categories', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('second_category_id')->nullable()->constrained('second_categories', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('third_category_id')->nullable()->constrained('third_categories', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->string('publisher');
            $table->string('published_year');
            $table->string('isbn');
            $table->string('language');
            $table->boolean('electronic_available');
            $table->decimal('hard_copy_price', 10, 2)->nullable();
            $table->decimal('electronic_copy_price', 10, 2)->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};

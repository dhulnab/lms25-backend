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
        Schema::create('book_for_borrow_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('status', ['requested', 'borrowed', 'available', 'deleted'])->default('available');
            $table->string('condition')->nullable();
            $table->string('serial_number')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_for_borrow_copies');
    }
};

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
        Schema::create('book_borrowings', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('book_copy_id')->constrained('book_for_borrow_copies', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->timestamp('borrow_start');
            $table->timestamp('borrow_end');
            $table->timestamp('return_date')->nullable();
            $table->boolean('returned')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_borrowings');
    }
};

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
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('borrow_id')->constrained('book_borrowings', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->decimal('penalty_amount', 10, 2);
            $table->enum('penalty_status', ['unpaid', 'paid', 'waived']);
            $table->timestamp('assessed_at');
            $table->timestamp('paid_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};

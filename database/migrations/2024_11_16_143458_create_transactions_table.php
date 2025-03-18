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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('action', ['borrow', 'purchase', 'penalty_payment', 'balance_update']);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('book_id')->nullable()->constrained('books')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('penalty_id')->nullable()->constrained('penalties')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('sold_copy_id')->nullable()->constrained('book_for_sell_copies')->onDelete('cascade')->onUpdate('cascade');
            $table->decimal('amount', 10, 2);
            $table->mediumText('details')->nullable();
            $table->enum('status', ['pending', 'done', 'fail'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

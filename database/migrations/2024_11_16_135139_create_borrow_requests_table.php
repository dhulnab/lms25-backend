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
        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('copy_id')->constrained('book_for_borrow_copies', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('borrow_start_date');
            $table->timestamp('borrow_end_date');
            $table->timestamp('request_date');
            $table->timestamp('request_expiry_date');
            $table->enum('status', ['approved', 'rejected'])->default('approved');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_requests');
    }
};

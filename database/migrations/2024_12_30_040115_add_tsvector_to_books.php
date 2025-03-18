<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE books ADD COLUMN tsv tsvector GENERATED ALWAYS AS ( to_tsvector('english', coalesce(title, '') || ' ' || coalesce(description, '') || ' ' || coalesce(author, ''))) STORED");
            DB::statement('CREATE INDEX books_tsv_idx ON books USING gin(tsv)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE books DROP COLUMN tsv");
            DB::statement('DROP INDEX IF EXISTS books_tsv_idx');
        }
    }
};

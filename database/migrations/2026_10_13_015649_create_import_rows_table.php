<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id');
            $table->unsignedInteger('row_number');
            $table->char('fingerprint', 64);
            $table->string('status', 20);
            $table->string('error_message', 1000)->nullable();
            $table->timestamps();

            $table->foreign('import_id')
                ->references('id')
                ->on('imports')
                ->cascadeOnDelete();

            $table->unique(['import_id', 'row_number']);

            $table->index(['import_id', 'status']);
            $table->index(['fingerprint']);
        });

        DB::statement("
            ALTER TABLE import_rows
            ADD CONSTRAINT chk_import_rows_status
            CHECK (status IN (
                'valid',
                'invalid',
                'duplicate_candidate',
                'imported'
            ))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_rows');
    }
};

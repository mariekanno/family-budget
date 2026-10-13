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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id');
            $table->foreignId('user_id');
            $table->string('original_filename', 255);
            $table->string('storage_path', 512)->nullable();
            $table->char('fingerprint', 64);
            $table->json('mapping_config')->nullable();
            $table->string('status', 20)->default('uploaded');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->unsignedInteger('duplicate_rows')->default(0);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign(['family_id', 'user_id'])
                ->references(['family_id', 'user_id'])
                ->on('family_user')
                ->restrictOnDelete();

            $table->unique(['id', 'family_id', 'user_id']);

            $table->index(['family_id', 'user_id', 'created_at']);
            $table->index(['user_id', 'fingerprint']);
            $table->index(['status', 'created_at']);
        });

        DB::statement("
            ALTER TABLE imports
            ADD CONSTRAINT chk_imports_status
            CHECK (status IN (
                'uploaded',
                'mapped',
                'validated',
                'processing',
                'completed',
                'failed'
            ))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};

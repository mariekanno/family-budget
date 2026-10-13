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
        Schema::create('family_user', function (Blueprint $table) {
            $table->foreignId('family_id')
                ->constrained('families')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('role', 20)->default('member');
            $table->timestamps();

            $table->primary(['family_id', 'user_id']);

            $table->unique('user_id');
        });

        DB::statement("
            ALTER TABLE family_user
            ADD CONSTRAINT chk_family_user_role
            CHECK (role IN ('admin', 'member'))
            ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_user');
    }
};

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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id');
            $table->foreignId('category_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedBigInteger('amount');
            $table->timestamps();

            $table->foreign(['category_id', 'family_id'])
                ->references(['id', 'family_id'])
                ->on('categories')
                ->restrictOnDelete();

            $table->unique(['family_id', 'category_id', 'year', 'month']);

            $table->index(['family_id', 'year', 'month']);
        });

        DB::statement('
            ALTER TABLE budgets
            ADD CONSTRAINT chk_budgets_year
            CHECK (year BETWEEN 2000 and 2100)
            ');

        DB::statement('
            ALTER TABLE budgets
            ADD CONSTRAINT chk_budgets_month
            CHECK (month BETWEEN 1 AND 12)
        ');

        DB::statement('
            ALTER TABLE budgets
            ADD CONSTRAINT chk_budgets_amount
            CHECK (amount BETWEEN 0 AND 999999999)
            ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};

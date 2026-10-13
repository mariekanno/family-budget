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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_id')
                ->constrained('families')
                ->restrictOnDelete();

            $table->string('name', 50);
            $table->string('normalized_name', 100);
            $table->string('type', 10);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['id', 'family_id']);

            $table->unique(
                ['family_id', 'type', 'normalized_name'],
                'categories_family_type_name_unique'
            );

            $table->index(
                ['family_id', 'type', 'is_active', 'sort_order'],
                'categories_filter_index'
            );
        });

        DB::statement("
            ALTER TABLE categories
            ADD CONSTRAINT chk_categories_type
            CHECK (type IN ('income', 'expense'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

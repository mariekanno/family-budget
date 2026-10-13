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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id');
            $table->foreignId('user_id');
            $table->foreignId('category_id');
            $table->foreignId('import_id')->nullable();
            $table->string('type', 10);
            $table->string('scope', 10);
            $table->string('visibility', 10);
            $table->unsignedBigInteger('amount');
            $table->date('transaction_date');
            $table->string('description', 100);
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->foreign(['family_id', 'user_id'])
                ->references(['family_id', 'user_id'])
                ->on('family_user')
                ->restrictOnDelete();

            $table->foreign(['category_id', 'family_id'])
                ->references(['id', 'family_id'])
                ->on('categories')
                ->restrictOnDelete();

            $table->foreign(['import_id', 'family_id', 'user_id'])
                ->references(['id', 'family_id', 'user_id'])
                ->on('imports')
                ->restrictOnDelete();

            $table->index(['family_id', 'transaction_date', 'id']);

            $table->index(['family_id', 'scope', 'visibility', 'transaction_date']);

            $table->index(['family_id', 'user_id', 'transaction_date']);

            $table->index(['family_id', 'category_id', 'transaction_date']);

            $table->index(['import_id', 'family_id', 'user_id']);
        });

        DB::statement("
            ALTER TABLE transactions
            ADD CONSTRAINT chk_transactions_type
            CHECK (type IN ('income', 'expense'))
        ");

        DB::statement("
            ALTER TABLE transactions
            ADD CONSTRAINT chk_transactions_scope
            CHECK (scope IN ('family','personal'))
        ");

        DB::statement("
            ALTER TABLE transactions
            ADD CONSTRAINT chk_transactions_visibility
            CHECK (visibility IN ('shared', 'private'))
        ");

        DB::statement('
            ALTER TABLE transactions
            ADD CONSTRAINT chk_transactions_amount
            CHECK (amount BETWEEN 1 AND 999999999)
        ');

        DB::statement("
            ALTER TABLE transactions
            ADD CONSTRAINT chk_transactions_family_visibility
            CHECK (scope <> 'family' OR visibility = 'shared')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

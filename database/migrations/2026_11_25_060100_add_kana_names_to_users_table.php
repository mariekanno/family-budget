<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name_kana', 50)->nullable();
            $table->string('first_name_kana', 50)->nullable();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name_kana', 'first_name_kana']);
        });
    }
};

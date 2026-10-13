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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id');
            $table->foreignId('invited_by');
            $table->string('email', 255);
            $table->char('token_hash', 64)->unique();
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign(['family_id', 'invited_by'])
                ->references(['family_id', 'user_id'])
                ->on('family_user')
                ->restrictOnDelete();

            $table->index(['family_id', 'email', 'status']);
            $table->index('expires_at');
        });
        DB::statement("
            ALTER TABLE invitations
            ADD CONSTRAINT chk_invitations_status
            CHECK (status IN (
                'pending', 'accepted', 'declined', 'revoked', 'expired'
            ))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};

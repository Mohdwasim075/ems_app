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
        Schema::table('events', function (Blueprint $table) {
            // 1. Drop the incorrect foreign key constraint targeting 'roles'
            // $table->dropForeign(['organizer_id']);

            // 2. Re-add the foreign key constraint targeting 'users'
            $table->foreign('organizer_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Revert back to roles constraint if rolled back
            $table->dropForeign(['organizer_id']);

            $table->foreign('organizer_id')
                  ->references('id')
                  ->on('roles')
                  ->restrictOnDelete();
        });
    }
};

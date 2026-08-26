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
        Schema::create('tickets', function (Blueprint $table) {
         $table->id();

        $table->foreignId('event_registration_id')
            ->constrained('event_registrations')
            ->restrictOnDelete();

        $table->string('ticket_number')->unique();

        $table->string('qr_code')->nullable();

        $table->string('status')->default('VALID');

        $table->dateTime('checked_in_at')->nullable();

        $table->timestamps();

        $table->unique('event_registration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

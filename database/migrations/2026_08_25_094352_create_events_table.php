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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organizer_id')
                ->nullable()
                ->constrained('roles')
                ->restrictOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('location');

            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->dateTime('registration_deadline')->nullable();

            $table->unsignedInteger('capacity');
            $table->unsignedInteger('available_seats')->nullable();
            $table->boolean('featured_at')->nullable()->default(0);

            $table->decimal('price', 10, 2)->default(0);

            $table->string('status')->default('DRAFT');

            $table->string('cover_image')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

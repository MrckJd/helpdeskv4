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
        Schema::create('signatures', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('specimen');
            $table->string('certificate')->nullable();
            $table->text('password')->nullable();
            $table->foreignUlid('user_id')->unique()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->check('`certificate` is not NULL or `password` is NULL', 'signatures_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};

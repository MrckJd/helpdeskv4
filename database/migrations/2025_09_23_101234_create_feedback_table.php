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
        Schema::create('feedback', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->json('feedbacks');
            $table->foreignUlid('category_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignUlid('request_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignUlid('organization_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};

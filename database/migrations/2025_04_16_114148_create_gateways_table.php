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
        Schema::create('gateways', function (Blueprint $table) {
            $table->id();
            $table->citext('identifier')->unique();
            $table->citext('name')->index();
            $table->citext('provider')->index();
            $table->citext('type')->index();
            $table->citext('description')->nullable();
            $table->citext('image_url')->nullable();
            $table->citext('status')->default('active');
            $table->json('config')->nullable();
            $table->timestamps();
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateways');
    }
};

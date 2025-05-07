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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('gateway_id')->nullable()->index();
            $table->citext("provider")->index();
            $table->decimal('amount');
            $table->decimal('amount_paid')->default(0);
            $table->citext("currency")->index();
            $table->citext("receipt")->nullable()->index();
            $table->citext("status")->index();
            $table->citext("result")->nullable();
            $table->text("provider_response")->nullable();
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
        Schema::dropIfExists('transactions');
    }
};

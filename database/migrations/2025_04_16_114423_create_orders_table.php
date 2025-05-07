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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedInteger('profile_id')->index();
            $table->unsignedInteger('gateway_id')->index()->nullable();
            $table->uuid('uuid')->index()->unique();
            $table->citext('identifier')->unique()->index();
            $table->citext('invoice_number')->index();
            $table->citext('currency')->index()->nullable();
            $table->decimal('amount');
            $table->citext('customer_identifier')->index()->nullable();
            $table->citext('customer_phone')->index()->nullable();
            $table->boolean('paid')->default(false);
            $table->citext('status')->default('PENDING');
            $table->citext('receipt')->nullable()->index();
            $table->citext('callback_url',1000)->nullable();
            $table->citext('redirect_url',1000)->nullable();
            $table->boolean('attempted')->default(false);
            $table->citext('provider_code')->nullable();
            $table->citext('provider_initial_response')->nullable();
            $table->citext('provider_final_response')->nullable();
            $table->json('provider_initial_response_data')->nullable();
            $table->json('provider_final_response_data')->nullable();
            $table->citext('callback_response')->nullable();
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
        Schema::dropIfExists('orders');
    }
};

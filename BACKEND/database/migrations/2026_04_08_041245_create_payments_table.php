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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('financing_application_id');
            $table->string('order_id')->unique();
            $table->string('snap_token')->nullable();
            $table->string('payment_type')->nullable();
            $table->enum('status', ['pending', 'settlement', 'expire', 'cancel', 'deny'])->default('pending');
            $table->bigInteger('amount');
            $table->json('midtrans_payload')->nullable();
            $table->timestamps();
            
            $table->foreign('financing_application_id')
                ->references('id')
                ->on('financing_applications')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

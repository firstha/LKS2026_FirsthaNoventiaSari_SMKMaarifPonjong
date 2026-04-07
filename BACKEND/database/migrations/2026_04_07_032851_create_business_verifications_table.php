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
        Schema::create('business_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');

            $table->string('nama_usaha');
            $table->string('nib');
            $table->string('npwp');
            $table->bigInteger('omzet_bulanan');
            $table->integer('jumlah_karyawan');
            $table->integer('lama_usaha_tahun');

            $table->enum('status', ['draft','submitted','verified','rejected'])->default('draft');
            $table->text('rejected_reason')->nullable();

            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_verifications');
    }
};

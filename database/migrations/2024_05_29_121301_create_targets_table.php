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
        Schema::create('targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('judul');
            $table->text('gambar');
            $table->bigInteger('target_uang');
            $table->bigInteger('uang_tersimpan')->default(0);
            $table->bigInteger('nominal_pengisian');
            $table->enum('jadwal_menabung',['hari','minggu','bulan']);
            $table->enum('status',['berlangsung','tercapai'])->default('berlangsung');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};

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
            $table->id();
            $table->foreignId('user_id');
            $table->string('judul');
            $table->text('gambar');
            $table->integer('target_uang');
            $table->integer('uang_tersimpan')->default(0);
            $table->integer('nominal_pengisian');
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

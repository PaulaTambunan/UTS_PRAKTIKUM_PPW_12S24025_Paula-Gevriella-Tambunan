<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

Schema::create('equipments', function (Blueprint $table) {
           $table->id();
           $table->string('asset_code', 20)->unique();
           $table->string('name', 150);
           $table->text('specification');
           $table->enum('condition', baik , rusak_ringan , rusak_berat)->default('baik');
           $table->foreignId('kategori_id')->constrained('kategoris')-cascadeOnDelete();
        });

 }

    public function down(): void
    {
        Schema::dropIfExists('kategoris');
    }
};

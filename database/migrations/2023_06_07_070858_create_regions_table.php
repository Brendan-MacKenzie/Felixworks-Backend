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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
        });

        Schema::create('region_models', function (Blueprint $table) {
            $table->unsignedBigInteger('region_id');
            $table->foreign('region_id')->references('id')->on('regions')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->morphs('model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('region_models', function (Blueprint $table) {
            $table->dropForeign('region_models_region_id_foreign');
        });

        Schema::dropIfExists('region_models');
        Schema::dropIfExists('regions');
    }
};

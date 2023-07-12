<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('placement_types', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('position')->nullable();
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('locations')
                ->onDelete('cascade')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('placement_types', function (Blueprint $table) {
            $table->dropForeign('placement_types_location_id_foreign');
        });

        Schema::dropIfExists('placement_types');
    }
};

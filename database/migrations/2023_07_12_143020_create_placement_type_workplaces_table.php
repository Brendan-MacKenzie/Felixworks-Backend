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
        Schema::create('placement_type_workplaces', function (Blueprint $table) {
            $table->unsignedBigInteger('placement_type_id');
            $table->unsignedBigInteger('workplace_id');
            $table->foreign('placement_type_id')->references('id')->on('placement_types')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->foreign('workplace_id')->references('id')->on('workplaces')
                ->onDelete('cascade')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('placement_type_workplaces', function (Blueprint $table) {
            $table->dropForeign('placement_type_workplaces_placement_type_id_foreign');
            $table->dropForeign('placement_type_workplaces_workplace_id_foreign');
        });

        Schema::dropIfExists('placement_type_workplaces');
    }
};

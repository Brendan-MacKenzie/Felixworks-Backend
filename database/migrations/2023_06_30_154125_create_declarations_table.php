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
        Schema::create('declarations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title');
            $table->decimal('total', 8, 2)->default(0);
            $table->unsignedBigInteger('placement_id');
            $table->foreign('placement_id')->references('id')->on('placements')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('declarations', function (Blueprint $table) {
            $table->dropForeign('declarations_placement_id_foreign');
            $table->dropForeign('declarations_created_by_foreign');
        });

        Schema::dropIfExists('declarations');
    }
};

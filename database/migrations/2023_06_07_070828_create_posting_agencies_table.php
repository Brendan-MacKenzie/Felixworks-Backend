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
        Schema::create('posting_agencies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('posting_id');
            $table->unsignedBigInteger('agency_id');
            $table->foreign('posting_id')->references('id')->on('postings')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->foreign('agency_id')->references('id')->on('agencies')
                ->onDelete('cascade')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posting_agencies', function (Blueprint $table) {
            $table->dropForeign('posting_agencies_posting_id_foreign');
            $table->dropForeign('posting_agencies_agency_id_foreign');
        });

        Schema::dropIfExists('posting_agencies');
    }
};

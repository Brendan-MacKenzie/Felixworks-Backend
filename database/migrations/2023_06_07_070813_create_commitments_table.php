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
        Schema::create('commitments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('posting_id');
            $table->unsignedBigInteger('agency_id');
            $table->integer('amount')->default(0);
            $table->foreign('posting_id')->references('id')->on('postings')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->foreign('agency_id')->references('id')->on('agencies')
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
        Schema::table('commitments', function (Blueprint $table) {
            $table->dropForeign('commitments_posting_id_foreign');
            $table->dropForeign('commitments_agency_id_foreign');
            $table->dropForeign('commitments_created_by_foreign');
        });

        Schema::dropIfExists('commitments');
    }
};

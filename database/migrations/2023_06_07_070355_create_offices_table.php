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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('agency_id');
            $table->foreign('agency_id')->references('id')->on('agencies')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('phone');
            $table->string('street_name');
            $table->string('number');
            $table->string('zip_code');
            $table->string('city');
            $table->string('country');
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
        Schema::table('offices', function (Blueprint $table) {
            $table->dropForeign('offices_agency_id_foreign');
            $table->dropForeign('offices_created_by_foreign');
        });

        Schema::dropIfExists('offices');
    }
};

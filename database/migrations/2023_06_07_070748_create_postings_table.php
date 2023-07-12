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
        Schema::create('postings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('locations')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->unsignedBigInteger('address_id');
            $table->foreign('address_id')->references('id')->on('addresses')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->dateTime('start_at');
            $table->text('dresscode')->nullable();
            $table->text('briefing')->nullable();
            $table->text('information')->nullable();
            $table->dateTime('cancelled_at')->nullable();
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
        Schema::table('postings', function (Blueprint $table) {
            $table->dropForeign('postings_location_id_foreign');
            $table->dropForeign('postings_address_id_foreign');
            $table->dropForeign('postings_created_by_foreign');
        });

        Schema::dropIfExists('postings');
    }
};

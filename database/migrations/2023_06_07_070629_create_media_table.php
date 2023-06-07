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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('path');
        });

        Schema::table('agencies', function (Blueprint $table) {
            $table->foreign('logo_id')->references('id')->on('media')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('avatar_id')->references('id')->on('media')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropForeign('agencies_logo_id_foreign');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign('employees_avatar_id_foreign');
        });

        Schema::dropIfExists('media');
    }
};

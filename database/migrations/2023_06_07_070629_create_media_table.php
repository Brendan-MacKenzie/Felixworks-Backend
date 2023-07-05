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
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->string('name');
            $table->tinyInteger('type');
        });

        Schema::table('agencies', function (Blueprint $table) {
            $table->foreign('logo_uuid')->references('id')->on('media')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('avatar_uuid')->references('id')->on('media')
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
            $table->dropForeign('agencies_logo_uuid_foreign');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign('employees_avatar_uuid_foreign');
        });

        Schema::dropIfExists('media');
    }
};

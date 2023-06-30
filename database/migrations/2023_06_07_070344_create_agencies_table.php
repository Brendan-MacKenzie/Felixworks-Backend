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
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('full_name');
            $table->string('email');
            $table->integer('base_rate')->default(0);
            $table->string('brand_color');
            $table->text('api_key')->nullable();
            $table->text('ip_address')->nullable();
            $table->text('webhook')->nullable();
            $table->text('webhook_key')->nullable();
            $table->uuid('logo_uuid')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('agency_id')->references('id')->on('agencies')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_agency_id_foreign');
        });

        Schema::table('agencies', function (Blueprint $table) {
            $table->dropForeign('agencies_created_by_foreign');
        });

        Schema::dropIfExists('agencies');
    }
};

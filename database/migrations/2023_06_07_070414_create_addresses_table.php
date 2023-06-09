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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')
                ->onDelete('set null')
                ->onUpdate('restrict');
            $table->string('name');
            $table->tinyInteger('type')->default(0);
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

        Schema::table('branches', function (Blueprint $table) {
            $table->foreign('address_id')->references('id')->on('addresses')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign('addresses_branch_id_foreign');
            $table->dropForeign('addresses_created_by_foreign');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign('branches_address_id_foreign');
        });

        Schema::dropIfExists('addresses');
    }
};

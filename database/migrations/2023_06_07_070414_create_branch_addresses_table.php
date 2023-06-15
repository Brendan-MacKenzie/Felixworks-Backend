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
        Schema::create('branch_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->unsignedBigInteger('address_id');
            $table->foreign('address_id')->references('id')->on('addresses')
                ->onDelete('cascade')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_addresses', function (Blueprint $table) {
            $table->dropForeign('branch_addresses_branch_id_foreign');
            $table->dropForeign('branch_addresses_address_id_foreign');
        });

        Schema::dropIfExists('branch_addresses');
    }
};

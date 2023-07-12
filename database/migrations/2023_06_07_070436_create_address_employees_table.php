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
        Schema::create('address_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('address_id')->references('id')->on('addresses')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->foreign('employee_id')->references('id')->on('employees')
                ->onDelete('cascade')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('address_employees', function (Blueprint $table) {
            $table->dropForeign('address_employees_employee_id_foreign');
            $table->dropForeign('address_employees_address_id_foreign');
        });

        Schema::dropIfExists('address_employees');
    }
};

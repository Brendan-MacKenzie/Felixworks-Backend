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
        Schema::create('location_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('employee_id');
            $table->boolean('nogo')->default(false);
            $table->foreign('location_id')->references('id')->on('locations')
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
        Schema::table('location_employees', function (Blueprint $table) {
            $table->dropForeign('location_employees_location_id_foreign');
            $table->dropForeign('location_employees_employee_id_foreign');
        });

        Schema::dropIfExists('location_employees');
    }
};

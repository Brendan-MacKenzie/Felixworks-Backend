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
        Schema::create('pool_employees', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('pool_id');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('pool_id')->references('id')->on('pools')
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
        Schema::table('pool_employees', function (Blueprint $table) {
            $table->dropForeign('pool_employees_pool_id_foreign');
            $table->dropForeign('pool_employees_employee_id_foreign');
        });

        Schema::dropIfExists('pool_employees');
    }
};

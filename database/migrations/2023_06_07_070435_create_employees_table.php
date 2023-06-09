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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('agency_id')->nullable();
            $table->foreign('agency_id')->references('id')->on('agencies')
                ->onDelete('set null')
                ->onUpdate('restrict');
            $table->integer('external_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->unsignedBigInteger('avatar_id')->nullable();
            $table->boolean('drivers_license')->default(false);
            $table->boolean('car')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });

        Schema::create('client_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('client_id')->references('id')->on('clients')
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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign('employees_agency_id_foreign');
            $table->dropForeign('employees_created_by_foreign');
        });

        Schema::table('client_employees', function (Blueprint $table) {
            $table->dropForeign('client_employees_client_id_foreign');
            $table->dropForeign('client_employees_employee_id_foreign');
        });

        Schema::dropIfExists('client_employees');
        Schema::dropIfExists('employees');
    }
};

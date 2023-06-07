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
        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('posting_id');
            $table->foreign('posting_id')->references('id')->on('postings')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->unsignedBigInteger('workplace_id')->nullable();
            $table->foreign('workplace_id')->references('id')->on('workplaces')
                ->onDelete('set null')
                ->onUpdate('restrict');
            $table->unsignedBigInteger('placement_type_id');
            $table->foreign('placement_type_id')->references('id')->on('placement_types')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')
                ->onDelete('set null')
                ->onUpdate('restrict');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('restrict');
            $table->dateTime('report_at');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('placements', function (Blueprint $table) {
            $table->dropForeign('placements_posting_id_foreign');
            $table->dropForeign('placements_workplace_id_foreign');
            $table->dropForeign('placements_placement_type_id_foreign');
            $table->dropForeign('placements_employee_id_foreign');
            $table->dropForeign('placements_created_by_foreign');
        });

        Schema::dropIfExists('placements');
    }
};

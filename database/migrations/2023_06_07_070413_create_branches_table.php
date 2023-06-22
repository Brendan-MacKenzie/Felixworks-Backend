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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->text('dresscode')->nullable();
            $table->text('briefing')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });

        Schema::create('user_branches', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('restrict');
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches')
                ->onDelete('cascade')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign('branches_client_id_foreign');
            $table->dropForeign('branches_created_by_foreign');
        });

        Schema::table('user_branches', function (Blueprint $table) {
            $table->dropForeign('user_branches_user_id_foreign');
            $table->dropForeign('user_branches_branch_id_foreign');
        });

        Schema::dropIfExists('user_branches');
        Schema::dropIfExists('branches');
    }
};

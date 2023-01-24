<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lessons', function(Blueprint $table)
        {
            $table->dropColumn('type');
        });
        Schema::table('lessons', function (Blueprint $table) {
            $table->enum('type', ['video', 'article'])->after('article')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->enum('type', ['video', 'article'])->default('video')->change();
        });
    }
};

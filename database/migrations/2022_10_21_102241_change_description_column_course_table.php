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
        Schema::table('courses', function (Blueprint $table) {
            $table->text("description")->default(null)->nullable()->change();
            $table->text("definition")->default(null)->nullable()->change();
            $table->decimal("price", 2, 0)->default(0.0)->change();
            $table->string("currency")->nullable(false)->default('AMD')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};

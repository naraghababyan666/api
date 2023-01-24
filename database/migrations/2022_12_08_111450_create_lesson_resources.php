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
        Schema::create('lesson_resources', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("lesson_id")->unsigned()->nullable(false);
            $table->bigInteger("resource_id")->unsigned()->nullable(false);
            $table->engine = 'InnoDB';
            $table->foreign('lesson_id')
                ->references('id')
                ->on('lessons')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('resource_id')
                ->references('id')
                ->on('resources')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
        Schema::table('resources', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lesson_resources');
    }
};

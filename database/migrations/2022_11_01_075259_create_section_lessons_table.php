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
        Schema::create('section_lessons', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("section_id")->unsigned()->nullable()->default(null);
            $table->bigInteger("lesson_id")->unsigned()->nullable()->default(null);
            $table->foreign('lesson_id')
                ->references('id')
                ->on('lessons')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('section_lessons');
    }
};

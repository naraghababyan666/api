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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            $table->string('headline1', 255)->nullable();
            $table->string('headline2', 255)->nullable();
            $table->string('headline3', 255)->nullable();
            $table->string('signature', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('course_title', 255)->nullable();
            $table->foreignId('course_id')->constrained('courses')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->timestamps();
        });

        Schema::create('generated_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained('certificates')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('user_id')->constrained('users')->onDelete('CASCADE')->onUpdate('CASCADE');
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
        //
    }
};

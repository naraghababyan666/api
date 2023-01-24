<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     *
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("user_id")->unsigned()->nullable(false);
            $table->string("cover_image")->nullable()->default(null);
            $table->string("promo_video")->nullable()->default(null);
            $table->string("title")->nullable()->default(null);
            $table->text("description");
            $table->string("sub_title")->nullable()->default(null);
            $table->bigInteger("language")->unsigned()->nullable()->default(null);
            $table->tinyInteger("type")->default(1)->comment('1=online ; 2 = oflline; 3 = online webinar; 4= consultation');
            $table->integer("status")->default(0)->comment('1 = draft; 2 = under review; 3 = approved; 4 = declined; 5 = deleted');
            $table->bigInteger("category_id")->unsigned()->default(null)->nullable();
            $table->integer("max_participants")->default(null)->nullable();
            $table->tinyInteger("level")->default(null)->nullable()->comment('1 = All Levels; 2= Beginners; 3 = Middle level; 4 = Advanced');
            $table->text("definition")->comment('here we will keep json of arrays texts');
            $table->bigInteger("trainer_id")->unsigned()->default(null)->nullable();
            $table->decimal("price", 2, 0)->nullable(false);
            $table->string("currency")->nullable(false)->comment('here we will store currency code, AMD or USD');
            $table->timestamps();
            $table->foreign('language')
                ->references('id')
                ->on('languages')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('trainer_id')
                ->references('id')
                ->on('trainers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
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
        Schema::dropIfExists('courses');
    }
};

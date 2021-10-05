<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckTaskPageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_task_pages', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->bigInteger('task_id')->index();
            $table->bigInteger('count_js_links')->default(0)->index();
            $table->bigInteger('count_phone_links')->default(0)->index();
            $table->bigInteger('count_empty_links')->default(0)->index();
            $table->bigInteger('count_link')->default(0);
            $table->bigInteger('count_error_link')->default(0)->index();
            $table->bigInteger('count_blank')->default(0)->index();
            $table->bigInteger('count_image')->default(0)->index();
            $table->bigInteger('count_error_image')->default(0)->index();
            $table->bigInteger('count_empty_image')->default(0)->index();
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
        Schema::dropIfExists('check_task_pages');
    }
}

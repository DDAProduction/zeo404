<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckTaskPageLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_task_page_links', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->bigInteger('page_id')->index();
            $table->integer('type');
            $table->integer('code');
            $table->text('info')->default('');
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
        Schema::dropIfExists('check_task_page_links');
    }
}

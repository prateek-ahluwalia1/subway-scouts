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
        Schema::create('guard_feed_backs', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id')->nullable();
            $table->integer('admin_id')->nullable();
            $table->text('feedback')->nullable();
            $table->string('on_edit')->nullable();
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
        Schema::dropIfExists('guard_feed_backs');
    }
};

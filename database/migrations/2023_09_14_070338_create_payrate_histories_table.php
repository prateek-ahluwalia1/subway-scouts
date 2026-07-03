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
        Schema::create('payrate_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('payrate_id');
            $table->longText('data');
            $table->string('effective_from');
            $table->string('effective_to');
            $table->integer('changed_by');
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
        Schema::dropIfExists('payrate_histories');
    }
};

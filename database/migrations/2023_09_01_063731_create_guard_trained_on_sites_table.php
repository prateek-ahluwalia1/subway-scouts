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
        Schema::create('guard_trained_on_sites', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id')->nullable();
            $table->integer('site_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('status')->nullable();
            $table->string('file')->nullable();
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
        Schema::dropIfExists('guard_trained_on_sites');
    }
};

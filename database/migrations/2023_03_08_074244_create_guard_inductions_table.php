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
        Schema::create('guard_inductions', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id');
            $table->string('induction_file')->nullable();
            $table->string('customer')->nullable();
            $table->string('site')->nullable();
            $table->string('customer_site_status')->nullable();
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
        Schema::dropIfExists('guard_inductions');
    }
};

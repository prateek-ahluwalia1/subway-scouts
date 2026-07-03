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
        Schema::create('green_call', function (Blueprint $table) {
            $table->id();
            $table->string('coordinates');
            $table->integer('job_id');
            $table->integer('guard_id');
            $table->enum('status', ['yes', 'no'])->nullable();
            $table->string('before_time')->nullable();
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
        Schema::dropIfExists('green_call');
    }
};

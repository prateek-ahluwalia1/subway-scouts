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
        Schema::create('staff_uniforms', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id');
            $table->integer('return_status');
            $table->text('customer_id');
            $table->text('uniform_type');
            $table->longText('note');
            $table->string('status')->nullable();
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
        Schema::dropIfExists('staff_uniforms');
    }
};

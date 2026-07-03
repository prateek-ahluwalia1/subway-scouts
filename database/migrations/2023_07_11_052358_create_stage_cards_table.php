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
        Schema::create('stage_cards', function (Blueprint $table) {
            $table->id();
            $table->longText('description')->nullable();
            $table->integer('stage_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('saleperson_id')->nullable();
            $table->string('admin_id')->nullable();
            $table->string('status')->nullable()->default('pending');
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
        Schema::dropIfExists('stage_cards');
    }
};

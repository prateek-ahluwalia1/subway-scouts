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
        Schema::create('guard_refrences', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id');
            $table->text('description');
            $table->enum('phy_dis', ['no', 'yes']);
            $table->enum('ner_dis', ['no', 'yes']);
            $table->enum('bron_dis', ['no', 'yes']);
            $table->enum('med_cond', ['no', 'yes']);
            $table->enum('work_inj', ['no', 'yes']);
            $table->enum('smoke', ['no', 'yes']);
            $table->text('work_history');
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
        Schema::dropIfExists('guard_refrences');
    }
};

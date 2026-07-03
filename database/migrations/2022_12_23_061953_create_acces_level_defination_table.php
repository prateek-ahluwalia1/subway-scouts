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
        Schema::create('acces_level_defination', function (Blueprint $table) {
            $table->id();
            $table->longText('permissions');
            $table->longText('specific_customer')->nullable();
            $table->text('sites')->nullable();
            $table->string('role');
            $table->string('user_id');
            $table->integer('created_by');
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
        Schema::dropIfExists('acces_level_defination');
    }
};

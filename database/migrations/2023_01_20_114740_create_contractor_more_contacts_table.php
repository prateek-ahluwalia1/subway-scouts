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
        Schema::create('contractor_more_contacts', function (Blueprint $table) {
            $table->id();
            $table->integer('contractor_id');
            $table->string('more_email')->nullable();
            $table->string('more_phone')->nullable();
            $table->longText('more_notes')->nullable();
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
        Schema::dropIfExists('contractor_more_contacts');
    }
};

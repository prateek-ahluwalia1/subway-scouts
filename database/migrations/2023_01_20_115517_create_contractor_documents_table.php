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
        Schema::create('contractor_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('contractor_id');
            $table->string('document')->nullable();
            $table->string('document_name')->nullable();
            $table->string('document_no')->nullable();
            $table->string('document_expire')->nullable();
            $table->string('type')->nullable();
            $table->string('side')->nullable();
            // $table->foreign('contractor_id')->references('id')->on('contractors');
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
        Schema::dropIfExists('contractor_documents');
    }
};

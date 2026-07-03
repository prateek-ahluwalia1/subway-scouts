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
        Schema::create('guards_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guard_id');
            $table->string('document_category')->default('other');
            $table->string('document_name')->nullable();
            $table->string('document_expire')->nullable();
            $table->string('document_no')->nullable();
            $table->string('side')->nullable();
            $table->string('document_type')->nullable();
            $table->longText('notes')->nullable();
            $table->string('file')->nullable();
            $table->string('c_f_roster')->nullable();
            $table->string('c_f_profile')->nullable();
            $table->foreign('guard_id')->references('id')->on('guards');
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
        Schema::dropIfExists('guards_documents');
    }
};

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
        Schema::create('ticket_management', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_type');
            $table->integer('guard_id');
            $table->string('guard_name');
            $table->string('guard_email');
            $table->text('subject');
            $table->string('priority');
            $table->text('message')->nullable();
            $table->text('file')->nullable();
            $table->integer('replied_by')->nullable();
            $table->string('status')->default('open');
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
        Schema::dropIfExists('ticket_management');
    }
};

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
        Schema::create('customer_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->integer('guards');
            $table->timestamp('request_date')->useCurrent();
            $table->string('required_date')->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->string('notes')->nullable();
            $table->string('site_name')->nullable();
            $table->string('site_address')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('time')->nullable();
            $table->string('date_start')->nullable();
            $table->string('date_end')->nullable();
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
        Schema::dropIfExists('customer_requests');
    }
};

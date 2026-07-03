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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('web_url')->nullable();
            $table->longText('auth_token')->nullable();
            $table->string('job_level')->nullable();
            $table->string('notification_token')->nullable();
            $table->enum('status', ['active', 'inactive','deleted'])->default('active');
            $table->string('image')->nullable();
            $table->integer('charged_rates_id')->nullable();
            $table->string('apply_date')->nullable();
            $table->string('timestamp_joined')->nullable();
            $table->string('timestamp_activity')->nullable();
            $table->string('otp')->nullable();
            $table->string('last_login')->nullable();
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
        Schema::dropIfExists('customers');
    }
};

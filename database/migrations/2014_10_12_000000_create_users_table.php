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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('image')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('userType')->nullable();
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('inactive');
            $table->enum('is_email_verify', ['no', 'yes'])->default('no');
            $table->integer('access_level_id')->default(0);
            $table->integer('is_super_admin')->default(0);
            $table->text('state')->nullable();
            $table->text('specific_customer')->nullable();
            $table->text('specific_sites')->nullable();
            $table->integer('hide_status')->default(0);
            $table->string('code')->nullable();
            $table->string('code_expiry')->nullable();
            $table->string('last_login')->nullable();
            $table->string('notification_token')->nullable();
            $table->string('otp')->nullable();
            $table->longText('auth_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};

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
        Schema::create('employment_pack_checklists', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id');
            $table->boolean('emp_form_filled')->default(false);
            $table->boolean('tfn_form_filled')->default(false);
            $table->boolean('super_form_filled')->default(false);
            $table->boolean('copy_of_passport_dob')->default(false);
            $table->boolean('copy_current_victoria_security_license')->default(false);
            $table->boolean('copy_security_certificate')->default(false);
            $table->boolean('copy_of_visa')->default(false);
            $table->boolean('copy_current_firstaid_rsa')->default(false);
            $table->boolean('copy_recent_cv')->default(false);
            $table->boolean('copy_driver_license')->default(false);
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
        Schema::dropIfExists('employment_pack_checklists');
    }
};

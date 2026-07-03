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
        Schema::create('crm_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('password')->nullable();
            $table->string('image')->nullable();
            $table->string('saleperson_id')->nullable();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            $table->string('title')->nullable();
            $table->string('lead_source')->nullable();
            $table->string('lead_status')->nullable();
            $table->string('industry')->nullable();
            $table->string('no_emp')->nullable();
            $table->string('annual_revenue')->nullable();
            $table->string('rating')->nullable();
            $table->string('skype_id')->nullable();
            $table->string('secondary_email')->nullable();
            $table->string('twitter')->nullable();
            $table->string('street')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('description')->nullable();
            $table->string('created_by')->nullable();
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
        Schema::dropIfExists('crm_customers');
    }
};

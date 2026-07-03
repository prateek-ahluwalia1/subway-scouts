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
        Schema::create('guard_work_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guard_id')->nullable();
            $table->string('hired_on')->nullable();
            // $table->string('induction_file')->nullable();
            //$table->string('customer')->nullable();
            //$table->string('site')->nullable();
            //$table->string('customer_site_status')->nullable();
            $table->string('job_level')->nullable();
            $table->string('payrate_state')->nullable();
            $table->string('payrate')->nullable();
            $table->string('tfn_file')->nullable();
            $table->string('tfn_file_no')->nullable();
            $table->string('superannutation_file')->nullable();
            $table->string('superannutation_no')->nullable();
            $table->string('abn_name')->nullable();
            $table->string('abn_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bsb')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('work_hours_limitation_status')->nullable();
            $table->string('weekly_work_hours_limitation')->nullable();
            $table->string('authorized_by')->nullable();
            $table->string('letter_from_educational_institute')->nullable();
            $table->string('guard_document_type')->nullable();
            $table->string('sr_name')->nullable();
            $table->string('home_phone')->nullable();
            $table->string('abn_type')->nullable();
            $table->string('day_of_commencement')->nullable();
            $table->text('other_qualification')->nullable();
            $table->string('car_reg')->nullable();
            $table->string('gst')->nullable();
            $table->enum('car', ['true', 'false'])->default('false');
            $table->text('availability_days')->nullable();
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
        Schema::dropIfExists('guard_work_details');
    }
};

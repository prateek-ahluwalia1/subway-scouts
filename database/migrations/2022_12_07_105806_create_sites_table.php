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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('contractor_id')->nullable();
            $table->text('site_guard_ids')->nullable();
            $table->string('level')->nullable();
            $table->string('state')->nullable();
            $table->string('staff_type')->nullable();
            $table->string('payrol')->nullable();
            $table->string('trained')->nullable();
            $table->string('green_call')->nullable();
            $table->string('first_green_call')->nullable();
            $table->string('second_green_call')->nullable();
            $table->string('first_green_call_time')->nullable();
            $table->string('second_green_call_time')->nullable();
            $table->string('welfare_call')->nullable();
            $table->string('welfare_call_type')->nullable();
            $table->string('welfare_timing')->nullable();
            $table->text('job_instrcutions')->nullable();
            $table->text('site_update_reason')->nullable();
            $table->string('sos_phone')->nullable();
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->longText('address')->nullable();
            $table->string('coordinates')->nullable();
            $table->enum('site_status', ['active','inactive','deleted'])->default('active');
            //$table->integer('guards_count')->default(0);
            $table->string('hourly_rate')->default(0);
            //$table->enum('guard_status', ['new','upcoming','ongoing','completed'])->default('new');
            //$table->enum('status', ['active','inactive','deleted'])->default('active');
           // $table->integer('date_added');
            //$table->longText('week_schedule');
            $table->string('site_name')->nullable();
            $table->longText('site_description')->nullable();
            $table->string('signin_radius')->nullable();
            $table->string('alert_radius')->nullable();
            $table->integer('break')->default(0);
            $table->string('break_chargeable')->nullable();
            $table->string('break_payable')->nullable();
            $table->string('break_deduction_payable')->nullable();
            $table->string('break_deduction_chargeable')->nullable();
            //$table->integer('petrol_site')->default(0);
            $table->integer('site_payrate')->nullable();
            $table->integer('site_charge_rate')->nullable();
            
            //$table->string('site_employer')->nullable();
            $table->integer('site_chargerate_level')->nullable();
            $table->integer('site_payrate_level')->nullable();
            $table->longText('site_tasks')->nullable();
            $table->integer('fatigue')->default(0);
            //$table->string('payable_and_chargeable_time')->nullable();
            //$table->string('break_deduction_chargeable')->nullable();
            //$table->string('site_hours')->nullable();
            $table->enum('site_type', ['direct','direct_or_contractor'])->default('direct');
            $table->string('unpublished_site')->nullable();
            //$table->string('charge_apply_date')->nullable();
            //$table->string('apply_date')->nullable();
            $table->string('job_instruction_file')->nullable();
            $table->string('site_hours')->nullable();
            $table->string('po_wo')->nullable();
            $table->string('site_updated_by')->nullable();
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
        Schema::dropIfExists('sites');
    }
};

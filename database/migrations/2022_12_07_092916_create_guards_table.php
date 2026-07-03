<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guards', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('email');
            $table->string('password')->default(Hash::make(123456));
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('suburb')->nullable();
            $table->string('covid_19')->default(0);
            $table->string('coordinates')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_email')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->string('registration_type')->nullable();
            $table->string('residential_status')->nullable();
            $table->longText('auth_token')->nullable();
            $table->string('notification_token')->nullable();
            $table->enum('is_email_approved', ['yes', 'no'])->default('no');
            $table->enum('is_available', ['yes', 'no'])->default('yes');
            $table->enum('guard_status', ['new','pending','active','inactive','deleted', 'document_exp'])->default('new');
            $table->enum('admin_approval_status', ['active','inactive','deleted'])->default('inactive');
            $table->string('work_limitation_status')->default(false);
            $table->string('weekly_work_hours_limitation')->nullable();
            $table->string('payroll_abn_number')->nullable();
            $table->string('payroll_bank_name')->nullable();
            $table->string('bsb')->nullable();
            $table->string('payroll_bank_account_number')->nullable();
            $table->enum('staff_type', ['part_time','full_time', 'casual'])->nullable();
            $table->enum('guard_type', ['direct','contractor'])->nullable();
            $table->string('activation_code')->nullable();
            $table->string('guard_postion')->nullable();
            $table->text('site_id')->nullable();
            $table->integer('admin_approved')->default(1);
            $table->string('payroll_superannutation_name')->default(1);
            $table->integer('payrates_id')->nullable();
            $table->string('residential_status_secondary')->nullable();
            $table->timestamp('last_login')->useCurrent();
            $table->longText('guard_availability')->nullable();
            $table->float('annual_leave_hours')->default(0);
            $table->float('sick_leave_hours')->default(0);
            $table->integer('rating')->default(0);
            $table->integer('profile_completion')->nullable();
            $table->string('authorized_by')->nullable();
            $table->string('guard_working_type')->default('guard');
            $table->string('position')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('contractor_id')->nullable();
            $table->string('email_varifay_otp')->nullable();
            $table->string('internal_id')->nullable();
            $table->string('joining_date')->nullable();
            $table->string('otp')->nullable();
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
        Schema::dropIfExists('guards');
    }
};

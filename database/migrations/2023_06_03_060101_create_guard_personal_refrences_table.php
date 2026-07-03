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
        Schema::create('guard_personal_refrences', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id');
            $table->string('name');
            $table->string('relationship');
            $table->string('contact_no');
            $table->string('applicant_signature');
            $table->string('print_full_name');
            $table->string('current_date');
            $table->string('check_and_interviewed_by');
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
        Schema::dropIfExists('guard_personal_refrences');
    }
};

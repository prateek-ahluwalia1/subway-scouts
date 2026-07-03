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
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('job_id');
            $table->integer('guard_id');
            $table->integer('roster_id');
            $table->string('site_name');
            $table->string('incident_date');
            $table->string('incident_time');
            $table->string('injury_type');
            $table->string('pdf');
            $table->text('injury_detail');
            $table->longText('people_involved');
            $table->longText('vehicle');
            $table->longText('emergency_services');
            $table->longText('wittness');
            $table->longText('photo');
            $table->longText('signature');
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
        Schema::dropIfExists('incident_reports');
    }
};

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
        Schema::create('guard_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id');
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->longText('notes')->nullable();
            $table->string('date_added')->nullable();
            $table->string('status')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('reason')->nullable();
            $table->string('days')->nullable();
            $table->string('admin_id')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('roster_id')->nullable();
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
        Schema::dropIfExists('guard_leave_requests');
    }
};

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
        Schema::create('guard_availability', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id');
            $table->boolean('monday')->default(0);
            $table->string('monday_type')->nullable();
            $table->string('monday_from')->nullable();
            $table->string('monday_to')->nullable();
            $table->boolean('tuesday')->default(0);
            $table->string('tuesday_type')->nullable();
            $table->string('tuesday_from')->nullable();
            $table->string('tuesday_to')->nullable();
            $table->boolean('wednesday')->default(0);
            $table->string('wednesday_type')->nullable();
            $table->string('wednesday_from')->nullable();
            $table->string('wednesday_to')->nullable();
            $table->boolean('thursday')->default(0);
            $table->string('thursday_type')->nullable();
            $table->string('thursday_from')->nullable();
            $table->string('thursday_to')->nullable();
            $table->boolean('friday')->default(0);
            $table->string('friday_type')->nullable();
            $table->string('friday_from')->nullable();
            $table->string('friday_to')->nullable();
            $table->boolean('saturday')->default(0);
            $table->string('saturday_type')->nullable();
            $table->string('saturday_from')->nullable();
            $table->string('saturday_to')->nullable();
            $table->boolean('sunday')->default(0);
            $table->string('sunday_type')->nullable();
            $table->string('sunday_from')->nullable();
            $table->string('sunday_to')->nullable();
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
        Schema::dropIfExists('guard_availability');
    }
};

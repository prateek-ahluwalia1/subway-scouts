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
        Schema::create('guard_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id');
            $table->string('relationship');
            $table->string('account_name');
            $table->string('account_type');
            $table->string('super_fund_name');
            $table->longText('criminal_history');
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
        Schema::dropIfExists('guard_emergency_contacts');
    }
};

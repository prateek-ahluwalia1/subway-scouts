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
        Schema::create('sms_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->longText('msg_body')->nullable();
            $table->longText('to')->nullable();
            $table->longText('to_number')->nullable();
            $table->longText('from_number')->nullable();
            $table->string('message_id')->nullable();
            $table->string('original_message_id')->nullable();
            $table->string('datetime')->nullable();
            $table->string('user_id')->nullable();
            $table->enum('direction', ['in', 'out'])->default('out')->nullable();
            $table->enum('seen_status', ['seen', 'unseen'])->default('unseen')->nullable();
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
        Schema::dropIfExists('sms_history');
    }
};

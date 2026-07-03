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
        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_owner')->nullable();
            $table->string('due_date')->nullable();
            $table->text('contact')->nullable();
            $table->enum('status', ['not_started','deferred','in_progress','completed','waiting_for_input']);
            $table->enum('priority', ['high','highest','low','lowest','normal']);
            $table->boolean('reminder')->default(false);
            $table->boolean('repeat')->default(false);
            $table->longText('description')->nullable();
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
        Schema::dropIfExists('crm_tasks');
    }
};

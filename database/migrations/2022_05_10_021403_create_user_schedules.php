<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TABLE_USER_SCHEDULES, function (Blueprint $table) {
            $table->id();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('occupation_id');
            $table->double('hourly_rate')->nullable();

            $table->text('address')->nullable();
            $table->double('long')->nullable();
            $table->double('lat')->nullable();
            $table->double('radius')->nullable();
            $table->string('company_ids')->default(',');
            $table->enum('repeat_type', [REPEAT_TYPE_NO_REPEAT, REPEAT_TYPE_DAILY, REPEAT_TYPE_WEEKLY, REPEAT_TYPE_MONTHLY])->default(REPEAT_TYPE_NO_REPEAT)->comment(REPEAT_TYPE_NO_REPEAT, REPEAT_TYPE_DAILY, REPEAT_TYPE_WEEKLY, REPEAT_TYPE_MONTHLY);
            $table->text('note')->nullable();
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
        Schema::dropIfExists(TABLE_USER_SCHEDULES);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TABLE_COMPANY_TASKS, function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('name');
            $table->enum('type', ['MODULE', 'TASK'])->default('TASK')->comment('MODULE','TASK');                        
            $table->integer('parent')->default(0);
            $table->string('url')->nullable();            
            $table->string('icon')->nullable();
            $table->integer('ordering')->default(9999);            
            $table->enum('status', [SYSTEM_STATUS_ACTIVE, SYSTEM_STATUS_INACTIVE, SYSTEM_STATUS_DELETE])->default('Active')->comment(SYSTEM_STATUS_ACTIVE, SYSTEM_STATUS_INACTIVE, SYSTEM_STATUS_DELETE);                       
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists(TABLE_COMPANY_TASKS);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyUserGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public $actions = [
        '0' => 'VIEW',
        '1' => 'ADD',
        '2' => 'EDIT',
        '3' => 'DELETE'        
    ];
    public function up()
    {
        Schema::create(TABLE_COMPANY_USER_GROUPS, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->integer('ordering')->default(9999);
            $table->enum('status', [SYSTEM_STATUS_ACTIVE, SYSTEM_STATUS_INACTIVE, SYSTEM_STATUS_DELETE])->default('Active')->comment(SYSTEM_STATUS_ACTIVE, SYSTEM_STATUS_INACTIVE, SYSTEM_STATUS_DELETE);            
            foreach ($this->actions as $key => $action) {
                $table->string('action_' . $key)->default(',')->comment($action);
            }
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
        Schema::dropIfExists(TABLE_COMPANY_USER_GROUPS);
    }
}

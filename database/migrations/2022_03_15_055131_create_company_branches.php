<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyBranches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TABLE_COMPANY_BRANCHES, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);            
            $table->integer('company_id');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->double('long')->nullable();
            $table->double('lat')->nullable();
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
        Schema::dropIfExists(TABLE_COMPANY_BRANCHES);
    }
}

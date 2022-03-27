<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TABLE_COMPANY_USERS, function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('user_id');
            $table->integer('company_user_group_id');
            $table->string('company_brach_ids')->default(',');
            $table->string('designation')->nullable();
            $table->enum('reason_status_active', [COMPANY_USER_STATUS_ACTIVE_ADMIN_ADD, COMPANY_USER_STATUS_ACTIVE_ADMIN_INVITATION, COMPANY_USER_STATUS_ACTIVE_OWNER_INVITATION,COMPANY_USER_STATUS_ACTIVE_USER_REQUEST]);            
            $table->integer('status_active_id')->default(0);
            $table->enum('reason_status_inactive', [COMPANY_USER_STATUS_INACTIVE_ADMIN_REMOVE, COMPANY_USER_STATUS_INACTIVE_OWNER_REMOVE, COMPANY_USER_STATUS_INACTIVE_USER_LEAVE]);            
            $table->integer('status_inactive_id')->default(0);
            $table->enum('status', [SYSTEM_STATUS_ACTIVE, SYSTEM_STATUS_INACTIVE, SYSTEM_STATUS_DELETE])->default('Active')->comment(SYSTEM_STATUS_ACTIVE.' means Joined', SYSTEM_STATUS_INACTIVE.' means Left', SYSTEM_STATUS_DELETE);            
            $table->timestamp('updated_at')->useCurrent();
            $table->integer('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(TABLE_COMPANY_USERS);
    }
}

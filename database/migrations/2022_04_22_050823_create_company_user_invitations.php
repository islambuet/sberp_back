<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyUserInvitations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TABLE_COMPANY_USER_INVITATIONS, function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('user_id');
            $table->integer('company_user_group_id');
            $table->string('company_branch_ids')->default(',');
            $table->string('designation')->nullable();
            $table->enum('status', [SYSTEM_STATUS_PENDING, SYSTEM_STATUS_ACCEPTED, SYSTEM_STATUS_REJECTED, SYSTEM_STATUS_CANCELLED, SYSTEM_STATUS_DELETE])->default(SYSTEM_STATUS_PENDING); //TODO cancel and delete not happen
            $table->smallInteger('revision_count')->default(1);
            $table->timestamp('invited_at')->useCurrent();
            $table->integer('invited_by');
            $table->timestamp('expires_at')->nullable();
            $table->integer('status_changed_by');
            $table->timestamp('status_changed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(TABLE_COMPANY_USER_INVITATIONS);
    }
}

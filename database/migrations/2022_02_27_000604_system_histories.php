<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SystemHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TABLE_SYSTEM_HISTORIES, function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 150);
            $table->integer('table_id')->comment('Primary key ID of regarding table');
            $table->string('controller', 150);
            $table->string('method', 150);
            $table->text('data_old')->nullable();
            $table->text('data_new')->nullable();
            $table->enum('action', [DB_ACTION_ADD, DB_ACTION_EDIT, DB_ACTION_DELETE])->comment(DB_ACTION_ADD, DB_ACTION_EDIT, DB_ACTION_DELETE);                        
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(TABLE_SYSTEM_HISTORIES);
    }
}

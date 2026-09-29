<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadFollowUpsTable extends Migration
{
    public function up()
    {
        Schema::create('lead_follow_ups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lead_id');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('account_id')->nullable();
            $table->dateTime('scheduled_at');
            $table->text('note')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lead_id')->references('id')->on('leads');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('account_id', 'lead_follow_ups_account')
                ->references('id')
                ->on('accounts');

            $table->index(['created_by', 'scheduled_at', 'dismissed_at'], 'lead_follow_ups_due_idx');
            $table->index(['lead_id', 'scheduled_at'], 'lead_follow_ups_lead_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lead_follow_ups');
    }
}

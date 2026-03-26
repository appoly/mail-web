<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailweb_email_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            if (config('MailWeb.SINGLESTORE')) {
                $table->foreignUuid('mailweb_email_id');
                $table->index('mailweb_email_id');
            } else {
                $table->foreignUuid('mailweb_email_id')->constrained('mailweb_emails')->onDelete('cascade');
            }
            $table->string('name')->nullable();
            $table->string('path')->nullable();
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
        Schema::dropIfExists('mailweb_email_attachments');
    }
};

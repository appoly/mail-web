<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // if a table already exists, drop it
        if (Schema::hasTable('mailweb_emails')) {
            Schema::rename('mailweb_emails', 'mailweb_emails_archived');
        }

        Schema::create('mailweb_emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('from')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->boolean('read')->default(false);
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
        Schema::dropIfExists('mailweb_emails');

        if (Schema::hasTable('mailweb_emails_archived')) {
            Schema::rename('mailweb_emails_archived', 'mailweb_emails');
        }
    }
};

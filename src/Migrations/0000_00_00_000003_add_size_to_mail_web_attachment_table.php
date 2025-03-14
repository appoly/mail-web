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
        Schema::table('mailweb_email_attachments', function (Blueprint $table) {
            $table->integer('size_bytes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mailweb_email_attachments', function (Blueprint $table) {
            $table->dropColumn('size_bytes');
        });
    }
};

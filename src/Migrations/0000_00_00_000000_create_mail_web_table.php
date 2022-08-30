<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailWebTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailweb_emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('read')->default(false);
            $table->timestamps();
        });

        // add long blob column to store the email after the id column
        DB::statement('ALTER TABLE mailweb_emails ADD COLUMN email LONGBLOB AFTER id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailweb_emails');
    }
}

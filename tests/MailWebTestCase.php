<?php

namespace Appoly\MailWeb;

use Illuminate\Database\Eloquent\Factory;
use Orchestra\Testbench\TestCase;

class MailWebTestCase extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();

        $this->app->make(Factory::class)->load(__DIR__ . '/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            MailWebServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('remember_token');
            $table->timestamps();
        });
    }

    /** @test */
    public function a_user_can_view_mailweb()
    {
        assert(true);
    }
}

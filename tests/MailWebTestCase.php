<?php

namespace Appoly\MailWeb\Tests;

use Illuminate\Database\Eloquent\Factory;
use Orchestra\Testbench\TestCase;

class MailWebTestCase extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // $this->setUpDatabase();

        // $this->app->make(Factory::class)->load(__DIR__ . '/factories');
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
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
}

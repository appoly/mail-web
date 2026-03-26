<?php

namespace Appoly\MailWeb\Tests\Unit;

use Illuminate\Foundation\Auth\User;
use Appoly\MailWeb\Tests\MailWebTestCase;

class RouteTest extends MailWebTestCase
{
    protected function setUp()
    {
        $this->actingAs(factory(User::class)->create());
    }

    /** @test */
    public function a_user_can_see_mail_web()
    {
        $this->get('/mailweb')
            ->assertStatus(200);
    }
}

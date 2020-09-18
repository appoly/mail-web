<?php

namespace Appoly\MailWeb\Tests\Unit;

use Appoly\MailWeb\Tests\MailWebTestCase;
use Illuminate\Foundation\Auth\User;

class RouteTest extends MailWebTestCase
{
    public function setUp()
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

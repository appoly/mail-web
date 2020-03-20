<?php

namespace Appoly\MailWeb;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\TestCase;

class RouteTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->actingAs(factory(User::class)->create());
    }

    /** @test */
    public function a_user_can_see_mail_web()
    {
        $this->get('/mailweb')
            ->assertStatus(200);
    }
}

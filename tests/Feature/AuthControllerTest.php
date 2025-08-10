<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_register()
    {

        $this->json('POST', '/api/register')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);

    }
}

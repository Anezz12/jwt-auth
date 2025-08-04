<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
  public function testRegister()
  {
    $this->json('POST', '/api/register')
         ->assertStatus(200)
         ->assertJson(['message' => 'Register']);

  }
}

<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->withoutVite();

        $this->assertSame('sqlite', config('database.default'));

        $this->get('/')->assertRedirect(route('login'));

        $this->get('/login')
            ->assertOk()
            ->assertSee('ERP Mercado')
            ->assertSee('Acessar o sistema');
    }
}

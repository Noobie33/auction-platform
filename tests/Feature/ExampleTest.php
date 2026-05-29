<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_page_loads_successfully(): void
    {
        $this->get('/')
            ->assertOk();
    }

    public function test_login_page_loads_successfully(): void
    {
        $this->get('/login')
            ->assertOk();
    }

    public function test_register_page_loads_successfully(): void
    {
        $this->get('/register')
            ->assertOk();
    }
}
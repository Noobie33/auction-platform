<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;

class ExampleTest extends TestCase
{
    public function test_user_model_can_be_instantiated(): void
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_user_model_has_expected_fillable_fields(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('status', $fillable);
    }
}
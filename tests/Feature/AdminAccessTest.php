<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_acp_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_acp_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.dashboard'));

        $response->assertForbidden();
    }
}

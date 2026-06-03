<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $bidder;
    private User $seller;
    private Category $parentCategory;
    private Category $childCategory;
    private Category $inactiveCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles only — CategorySeeder is not needed because tests
        // create their own categories with known names/slugs.
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin  = $this->createUserWithRole('admin');
        $this->bidder = $this->createUserWithRole('bidder');
        $this->seller = $this->createUserWithRole('seller');

        $this->parentCategory = Category::create([
            'name'        => 'Test Electronics',
            'slug'        => 'test-electronics',
            'description' => 'Parent category for tests.',
            'parent_id'   => null,
            'is_active'   => true,
        ]);

        $this->childCategory = Category::create([
            'name'      => 'Test Mobile Phones',
            'slug'      => 'test-mobile-phones',
            'parent_id' => $this->parentCategory->id,
            'is_active' => true,
        ]);

        $this->inactiveCategory = Category::create([
            'name'      => 'Archived Category',
            'slug'      => 'archived-category',
            'parent_id' => null,
            'is_active' => false,
        ]);
    }

    // ── Role assignment helper ─────────────────────────────────────────────────
    // NOTE: uses Spatie assignRole(). If this project uses a different role
    // system, match the pattern in AdminUserManagementTest.php instead.

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        return $user;
    }

    // ── Access control ─────────────────────────────────────────────────────────

    public function test_guest_is_redirected_from_category_index(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_access_category_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertOk();
    }

    public function test_bidder_cannot_access_category_index(): void
    {
        $this->actingAs($this->bidder)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_seller_cannot_access_category_index(): void
    {
        $this->actingAs($this->seller)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    // ── Content: parent category ───────────────────────────────────────────────

    public function test_category_index_shows_parent_category_name(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertSee('Test Electronics');
    }

    public function test_category_index_shows_parent_category_slug(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertSee('test-electronics');
    }

    // ── Content: child category ────────────────────────────────────────────────

    public function test_category_index_shows_child_category_name(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertSee('Test Mobile Phones');
    }

    public function test_category_index_shows_child_category_slug(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertSee('test-mobile-phones');
    }

    // ── Content: status badges ─────────────────────────────────────────────────

    public function test_category_index_shows_active_badge(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertSee('Active');
    }

    public function test_category_index_shows_inactive_badge(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertSee('Inactive');
    }

    public function test_category_index_shows_inactive_categories(): void
    {
        // Admin must see inactive categories — they are not filtered out.
        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertSee('Archived Category');
    }

    // ── Dashboard navigation ───────────────────────────────────────────────────

    public function test_admin_dashboard_contains_category_management_link(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertSee(route('admin.categories.index'));
    }
}
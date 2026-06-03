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

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        return $user;
    }

    // ── Index: access control ──────────────────────────────────────────────────

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

    // ── Index: content ─────────────────────────────────────────────────────────

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

    // ── Create: access control ─────────────────────────────────────────────────

    public function test_admin_can_view_create_category_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.categories.create'))
            ->assertOk();
    }

    public function test_guest_is_redirected_from_create_category_page(): void
    {
        $this->get(route('admin.categories.create'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_view_create_category_page(): void
    {
        $this->actingAs($this->bidder)
            ->get(route('admin.categories.create'))
            ->assertForbidden();
    }

    // ── Store: successful creation ─────────────────────────────────────────────

    public function test_admin_can_create_parent_category(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name'        => 'New Parent Category',
                'description' => 'A top-level test category.',
                'parent_id'   => null,
                'is_active'   => '1',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name'      => 'New Parent Category',
            'parent_id' => null,
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_create_child_category(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name'      => 'New Child Category',
                'parent_id' => $this->parentCategory->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name'      => 'New Child Category',
            'parent_id' => $this->parentCategory->id,
        ]);
    }

    // ── Store: slug generation ─────────────────────────────────────────────────

    public function test_slug_is_generated_automatically_from_name(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name'      => 'Auto Slug Category',
                'is_active' => '1',
            ]);

        $this->assertDatabaseHas('categories', [
            'slug' => 'auto-slug-category',
        ]);
    }

    public function test_duplicate_names_generate_unique_slugs(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name'      => 'Duplicate Name',
                'is_active' => '1',
            ]);

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name'      => 'Duplicate Name',
                'is_active' => '1',
            ]);

        $this->assertDatabaseHas('categories', ['slug' => 'duplicate-name']);
        $this->assertDatabaseHas('categories', ['slug' => 'duplicate-name-2']);
    }

    // ── Store: validation failures ─────────────────────────────────────────────

    public function test_name_is_required_to_create_category(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name'      => '',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_invalid_parent_id_fails_validation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name'      => 'Orphan Category',
                'parent_id' => 99999,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('parent_id');
    }
}
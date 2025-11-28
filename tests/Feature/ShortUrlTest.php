<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShortUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $roles = ['SuperAdmin', 'Admin', 'Member'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_superadmin_cannot_create_short_urls(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('SuperAdmin');

        $response = $this->actingAs($superAdmin)->post(route('admin.short-urls.store'), [
            'original_url' => 'http://ersachinkumar.in/',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'SuperAdmin cannot create short URLs.');
        $this->assertDatabaseCount('short_urls', 0);
    }

    public function test_admin_can_create_short_urls(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create(['company_id' => $company->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('admin.short-urls.store'), [
            'original_url' => 'http://ersachinkumar.in/',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('short_urls', [
            'user_id' => $admin->id,
            'company_id' => $company->id,
            'original_url' => 'http://ersachinkumar.in/',
        ]);
    }

    public function test_member_can_create_short_urls(): void
    {
        $company = Company::factory()->create();
        $member = User::factory()->create(['company_id' => $company->id]);
        $member->assignRole('Member');

        $response = $this->actingAs($member)->post(route('admin.short-urls.store'), [
            'original_url' => 'http://ersachinkumar.in/',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('short_urls', [
            'user_id' => $member->id,
            'company_id' => $company->id,
            'original_url' => 'http://ersachinkumar.in/',
        ]);
    }

    public function test_admin_can_only_see_short_urls_from_their_company(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $admin = User::factory()->create(['company_id' => $company1->id]);
        $admin->assignRole('Admin');

        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $user2 = User::factory()->create(['company_id' => $company2->id]);

        $shortUrl1 = ShortUrl::factory()->create(['user_id' => $user1->id, 'company_id' => $company1->id]);
        ShortUrl::factory()->create(['user_id' => $user2->id, 'company_id' => $company2->id]);

        $response = $this->actingAs($admin)->get(route('admin.short-urls.index'));

        $response->assertStatus(200);
        $response->assertViewHas('shortUrls');
        $shortUrls = $response->viewData('shortUrls');
        $this->assertCount(1, $shortUrls);
        $this->assertEquals($shortUrl1->id, $shortUrls->first()->id);
    }

    public function test_member_can_only_see_short_urls_created_by_themselves(): void
    {
        $company = Company::factory()->create();
        
        $member = User::factory()->create(['company_id' => $company->id]);
        $member->assignRole('Member');

        $otherUser = User::factory()->create(['company_id' => $company->id]);

        $shortUrl1 = ShortUrl::factory()->create(['user_id' => $member->id, 'company_id' => $company->id]);
        ShortUrl::factory()->create(['user_id' => $otherUser->id, 'company_id' => $company->id]);

        $response = $this->actingAs($member)->get(route('admin.short-urls.index'));

        $response->assertStatus(200);
        $response->assertViewHas('shortUrls');
        $shortUrls = $response->viewData('shortUrls');
        $this->assertCount(1, $shortUrls);
        $this->assertEquals($shortUrl1->id, $shortUrls->first()->id);
    }

    public function test_short_urls_require_authentication_and_redirect_to_original_url(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        
        $shortUrl = ShortUrl::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'original_url' => 'http://ersachinkumar.in/',
            'short_code' => 'abc123',
        ]);

        // Test that unauthenticated users are redirected to login
        $response = $this->get(route('short-url.redirect', 'abc123'));
        $response->assertRedirect(route('login'));

        // Test that authenticated users can access and redirect works
        $response = $this->actingAs($user)->get(route('short-url.redirect', 'abc123'));
        $response->assertRedirect('http://ersachinkumar.in/');
        $this->assertEquals(1, $shortUrl->fresh()->clicks);
    }

    public function test_superadmin_cannot_see_all_short_urls(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('SuperAdmin');

        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        ShortUrl::factory()->create(['user_id' => $user->id, 'company_id' => $company->id]);

        $response = $this->actingAs($superAdmin)->get(route('admin.short-urls.index'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_superadmin_can_invite_admin_for_new_company(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('SuperAdmin');

        $response = $this->actingAs($superAdmin)->post(route('admin.invitations.store'), [
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => 'Admin',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
        ]);
        $this->assertDatabaseHas('companies', [
            'email' => 'admin@example.com',
        ]);
    }

    public function test_superadmin_cannot_invite_non_admin_role_for_new_company(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('SuperAdmin');

        $response = $this->actingAs($superAdmin)->post(route('admin.invitations.store'), [
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'password' => 'password123',
            'role' => 'Member',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'SuperAdmin can only invite Admins for new companies.');
    }

    public function test_admin_cannot_invite_another_admin(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create(['company_id' => $company->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'name' => 'Another Admin',
            'email' => 'admin2@example.com',
            'password' => 'password123',
            'role' => 'Admin',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Admin cannot invite another Admin.');
    }

    public function test_admin_can_invite_member(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create(['company_id' => $company->id]);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'password' => 'password123',
            'role' => 'Member',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'member@example.com',
            'company_id' => $company->id,
        ]);
    }

    public function test_superadmin_can_invite_any_role_to_existing_company(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('SuperAdmin');
        
        $company = Company::factory()->create();

        $response = $this->actingAs($superAdmin)->post(route('admin.invitations.store'), [
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'password' => 'password123',
            'role' => 'Member',
            'company_id' => $company->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'member@example.com',
            'company_id' => $company->id,
        ]);
    }
}

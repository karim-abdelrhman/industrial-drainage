<?php

namespace Tests\Feature;

use App\Models\Admin;
use Database\Seeders\AdminSeeder;
use Filament\Auth\Pages\Login;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/2026_09_20_040401_create_admins_table.php',
            '--no-interaction' => true,
        ]);
    }

    public function test_admin_seeder_creates_the_panel_user(): void
    {
        $this->seed(AdminSeeder::class);

        $this->assertDatabaseHas('admins', [
            'email' => 'admin@gmail.com',
            'name' => 'Admin',
        ]);
    }

    public function test_admin_can_log_in_to_the_admin_panel(): void
    {
        $this->seed(AdminSeeder::class);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@gmail.com',
                'password' => 'adminpass',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertAuthenticatedAs(
            Admin::query()->where('email', 'admin@gmail.com')->firstOrFail(),
            'admin',
        );
    }

    public function test_invalid_credentials_cannot_log_in_to_the_admin_panel(): void
    {
        $this->seed(AdminSeeder::class);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@gmail.com',
                'password' => 'wrong-password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest('admin');
    }

    public function test_guest_is_redirected_from_the_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_guest_is_redirected_to_admin_login_from_protected_routes(): void
    {
        $this->get('/invoices/1/print')->assertRedirect('/admin/login');
    }
}

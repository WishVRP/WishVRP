<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UsersAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_second_account_with_hashed_password(): void
    {
        config(['ulbrich_admin.mfa_required' => false]);
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageUsers::class)
            ->callAction('create', ['name' => 'Andreas', 'email' => 'andreas@example.org', 'password' => 'ein-sehr-langes-passwort'])
            ->assertHasNoActionErrors();
        $u = User::query()->where('email', 'andreas@example.org')->firstOrFail();
        $this->assertTrue(Hash::check('ein-sehr-langes-passwort', $u->password));

        Livewire::test(ManageUsers::class)
            ->callAction('create', ['name' => 'Kurz', 'email' => 'k@example.org', 'password' => 'kurz'])
            ->assertHasActionErrors(['password']);
    }

    public function test_registration_closed_in_production_unless_setup_switch(): void
    {
        $this->get('/verwaltung/register')->assertNotFound();
    }
}

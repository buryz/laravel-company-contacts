<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // LOGIN FORM TESTS
    public function test_login_form_is_displayed()
    {
        $response = $this->get(route('auth.login'));

        $response->assertStatus(200);
        $response->assertSee('Logowanie');
        $response->assertSee('Adres email');
        $response->assertSee('Hasło');
        $response->assertSee('Zapamiętaj mnie');
    }

    public function test_authenticated_user_cannot_access_login_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('auth.login'));

        $response->assertRedirect(route('dashboard'));
    }

    // REGISTER FORM TESTS
    public function test_register_form_is_displayed()
    {
        $response = $this->get(route('auth.register'));

        $response->assertStatus(200);
        $response->assertSee('Rejestracja');
        $response->assertSee('Nazwa użytkownika');
        $response->assertSee('Adres email');
        $response->assertSee('Hasło');
        $response->assertSee('Potwierdź hasło');
    }

    public function test_authenticated_user_cannot_access_register_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('auth.register'));

        $response->assertRedirect(route('dashboard'));
    }

    // LOGIN TESTS
    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->from(route('auth.login'))
            ->post(route('auth.login'), [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHas('success', 'Zostałeś pomyślnie zalogowany.');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_remember_me()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertRedirect(route('contacts.index'));
        $this->assertAuthenticatedAs($user);
        
        // Check that remember token is set
        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    public function test_user_cannot_login_with_incorrect_email()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('auth.login'), [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'Nieprawidłowe dane logowania.']);
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'Nieprawidłowe dane logowania.']);
        $this->assertGuest();
    }

    public function test_login_validation_fails_with_empty_fields()
    {
        $response = $this->post(route('auth.login'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'email' => 'Pole email jest wymagane.',
            'password' => 'Pole hasło jest wymagane.',
        ]);
        $this->assertGuest();
    }

    public function test_login_validation_fails_with_invalid_email()
    {
        $response = $this->post(route('auth.login'), [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'Podaj prawidłowy adres email.']);
        $this->assertGuest();
    }

    public function test_login_preserves_email_on_validation_failure()
    {
        $response = $this->post(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasInput('email', 'test@example.com');
    }

    public function test_login_regenerates_session()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Start a session and get the initial session ID
        $this->get(route('auth.login'));
        $initialSessionId = Session::getId();

        $response = $this->post(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('contacts.index'));
        
        // Session ID should be different after login
        $newSessionId = Session::getId();
        $this->assertNotEquals($initialSessionId, $newSessionId);
    }

    // REGISTER TESTS
    public function test_user_can_register_with_valid_data()
    {
        $response = $this->post(route('auth.register'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHas('success', 'Konto zostało utworzone pomyślnie.');
        
        $this->assertDatabaseHas('users', [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
        ]);
        
        $this->assertAuthenticated();
        
        // Check that password is hashed
        $user = User::where('email', 'jan@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_register_validation_fails_with_empty_name()
    {
        $response = $this->post(route('auth.register'), [
            'name' => '',
            'email' => 'jan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name' => 'Pole nazwa jest wymagane.']);
        $this->assertGuest();
    }

    public function test_register_validation_fails_with_long_name()
    {
        $response = $this->post(route('auth.register'), [
            'name' => str_repeat('a', 256),
            'email' => 'jan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name' => 'Nazwa nie może być dłuższa niż 255 znaków.']);
        $this->assertGuest();
    }

    public function test_register_validation_fails_with_empty_email()
    {
        $response = $this->post(route('auth.register'), [
            'name' => 'Jan Kowalski',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'Pole email jest wymagane.']);
        $this->assertGuest();
    }

    public function test_register_validation_fails_with_invalid_email()
    {
        $response = $this->post(route('auth.register'), [
            'name' => 'Jan Kowalski',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'Podaj prawidłowy adres email.']);
        $this->assertGuest();
    }

    public function test_register_validation_fails_with_duplicate_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('auth.register'), [
            'name' => 'Jan Kowalski',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email' => 'Ten adres email jest już zajęty.']);
        $this->assertGuest();
    }

    public function test_register_validation_fails_with_short_password()
    {
        $response = $this->post(route('auth.register'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password' => 'Hasło musi mieć co najmniej 8 znaków.']);
        $this->assertGuest();
    }

    public function test_register_validation_fails_with_password_mismatch()
    {
        $response = $this->post(route('auth.register'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password' => 'Potwierdzenie hasła nie pasuje.']);
        $this->assertGuest();
    }

    public function test_register_preserves_input_on_validation_failure()
    {
        $response = $this->post(route('auth.register'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasInput('name', 'Jan Kowalski');
        $response->assertSessionHasInput('email', 'jan@example.com');
        $response->assertSessionMissingInput('password');
        $response->assertSessionMissingInput('password_confirmation');
    }

    public function test_register_automatically_logs_in_user()
    {
        $response = $this->post(route('auth.register'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('contacts.index'));
        
        $user = User::where('email', 'jan@example.com')->first();
        $this->assertAuthenticatedAs($user);
    }

    // LOGOUT TESTS
    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('auth.logout'));

        $response->assertRedirect(route('contacts.index'));
        $response->assertSessionHas('success', 'Zostałeś pomyślnie wylogowany.');
        $this->assertGuest();
    }

    public function test_logout_invalidates_session()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Get initial session ID
        $initialSessionId = Session::getId();

        $response = $this->post(route('auth.logout'));

        $response->assertRedirect(route('contacts.index'));
        $this->assertGuest();
        
        // Session should be invalidated
        $newSessionId = Session::getId();
        $this->assertNotEquals($initialSessionId, $newSessionId);
    }

    public function test_guest_cannot_logout()
    {
        $response = $this->post(route('auth.logout'));

        $response->assertRedirect(route('auth.login'));
    }

    // MIDDLEWARE TESTS
    public function test_guest_middleware_redirects_authenticated_users()
    {
        $user = User::factory()->create();

        $guestRoutes = [
            ['GET', route('auth.login')],
            ['POST', route('auth.login')],
            ['GET', route('auth.register')],
            ['POST', route('auth.register')],
        ];

        foreach ($guestRoutes as [$method, $url]) {
            $response = $this->actingAs($user)->call($method, $url, [
                'name' => 'Test',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $this->assertEquals(302, $response->getStatusCode(), "Route {$method} {$url} should redirect authenticated users");
            $this->assertStringContainsString('/dashboard', $response->headers->get('Location'));
        }
    }

    public function test_auth_middleware_redirects_guests()
    {
        $response = $this->post(route('auth.logout'));

        $response->assertRedirect(route('auth.login'));
    }

    // INTEGRATION TESTS
    public function test_complete_auth_workflow()
    {
        // Register
        $response = $this->post(route('auth.register'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('contacts.index'));
        $this->assertAuthenticated();

        // Logout
        $response = $this->post(route('auth.logout'));
        $response->assertRedirect(route('contacts.index'));
        $this->assertGuest();

        // Login again
        $response = $this->post(route('auth.login'), [
            'email' => 'jan@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('contacts.index'));
        $this->assertAuthenticated();
    }

    // SECURITY TESTS
    public function test_login_attempts_are_case_sensitive_for_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123'),
        ]);

        $response = $this->post(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'password123', // Different case
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_email_is_case_insensitive()
    {
        $user = User::factory()->create([
            'email' => 'Test@Example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('auth.login'), [
            'email' => 'test@example.com', // Different case
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('contacts.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_is_hashed_in_database()
    {
        $password = 'password123';
        
        $response = $this->post(route('auth.register'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertRedirect(route('contacts.index'));
        
        $user = User::where('email', 'jan@example.com')->first();
        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    // ERROR HANDLING TESTS
    public function test_login_validation_messages_are_in_polish()
    {
        $response = $this->post(route('auth.login'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Pole email jest wymagane.',
            'password' => 'Pole hasło jest wymagane.',
        ]);
    }

    public function test_register_validation_messages_are_in_polish()
    {
        $response = $this->post(route('auth.register'), [
            'name' => '',
            'email' => 'invalid',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertSessionHasErrors();
        
        // Check that Polish validation messages are present
        $errors = session('errors');
        $this->assertStringContainsString('wymagane', $errors->first('name'));
        $this->assertStringContainsString('email', $errors->first('email'));
        $this->assertStringContainsString('znaków', $errors->first('password'));
    }

    // REDIRECT TESTS
    public function test_login_redirects_to_intended_url()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Try to access a protected route
        $this->get(route('contacts.create'));

        // Login
        $response = $this->post(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Should redirect to the originally intended route
        $response->assertRedirect(route('contacts.create'));
    }

    public function test_login_redirects_to_contacts_index_by_default()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('auth.login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('contacts.index'));
    }
}
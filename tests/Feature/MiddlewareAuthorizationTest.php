<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all protected routes redirect guests to login page.
     */
    public function test_protected_routes_redirect_guests_to_login()
    {
        // Create test data
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create();

        // Define all protected routes that should redirect to login
        $protectedRoutes = [
            // Contact management routes
            ['GET', route('contacts.create')],
            ['POST', route('contacts.store')],
            ['GET', route('contacts.edit', $contact)],
            ['PUT', route('contacts.update', $contact)],
            ['DELETE', route('contacts.destroy', $contact)],
            ['GET', route('contacts.export')],
            
            // Tag management routes
            ['GET', route('tags.index')],
            ['GET', route('tags.create')],
            ['POST', route('tags.store')],
            ['GET', route('tags.show', $tag)],
            ['GET', route('tags.edit', $tag)],
            ['PUT', route('tags.update', $tag)],
            ['DELETE', route('tags.destroy', $tag)],
            
            // API routes
            ['GET', route('tags.api')],
            
            // Auth routes that require authentication
            ['POST', route('auth.logout')],
        ];

        // Test each protected route
        foreach ($protectedRoutes as [$method, $url]) {
            $response = $this->call($method, $url);
            
            // For GET requests, expect redirect to login
            if ($method === 'GET') {
                $response->assertRedirect(route('login'));
                $this->assertEquals(302, $response->getStatusCode(), "Route {$method} {$url} should redirect guests to login");
            } 
            // For API requests, expect 401 Unauthorized
            elseif (str_contains($url, '/api/')) {
                $response->assertStatus(401);
            } 
            // For other methods (POST, PUT, DELETE), expect 419 (CSRF token mismatch) or redirect to login
            else {
                $this->assertTrue(
                    $response->getStatusCode() === 419 || $response->getStatusCode() === 302,
                    "Route {$method} {$url} should return 419 (CSRF) or redirect guests to login"
                );
            }
        }
    }

    /**
     * Test that public routes are accessible to guests.
     */
    public function test_public_routes_are_accessible_to_guests()
    {
        // Create test data
        $contact = Contact::factory()->create();

        // Define all public routes that should be accessible to guests
        $publicRoutes = [
            // Contact viewing routes
            ['GET', route('contacts.index')],
            ['GET', route('contacts.show', $contact)],
            ['GET', route('contacts.qr', $contact)],
            
            // Search routes
            ['GET', route('search')],
            ['GET', route('search.suggestions')],
            ['GET', route('search.group-by-company')],
            ['GET', route('search.group-by-position')],
            ['GET', route('search.by-tags')],
            ['GET', route('search.available-tags')],
            
            // Auth routes for guests
            ['GET', route('auth.login')],
            ['GET', route('auth.register')],
        ];

        // Test each public route
        foreach ($publicRoutes as [$method, $url]) {
            $response = $this->call($method, $url);
            
            // Expect 200 OK for all public routes
            $this->assertTrue(
                $response->isSuccessful() || $response->isRedirect(),
                "Route {$method} {$url} should be accessible to guests"
            );
        }
    }

    /**
     * Test that protected routes are accessible to authenticated users.
     */
    public function test_protected_routes_are_accessible_to_authenticated_users()
    {
        // Create test data
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create();

        // Define all protected routes that should be accessible to authenticated users
        $protectedRoutes = [
            // Contact management routes
            ['GET', route('contacts.create')],
            ['GET', route('contacts.edit', $contact)],
            ['GET', route('contacts.export')],
            
            // Tag management routes
            ['GET', route('tags.index')],
            ['GET', route('tags.create')],
            ['GET', route('tags.show', $tag)],
            ['GET', route('tags.edit', $tag)],
            
            // API routes
            ['GET', route('tags.api')],
        ];

        // Test each protected route
        foreach ($protectedRoutes as [$method, $url]) {
            $response = $this->actingAs($user)->call($method, $url);
            
            // Expect 200 OK for all protected routes when authenticated
            $this->assertTrue(
                $response->isSuccessful() || $response->isRedirect(),
                "Route {$method} {$url} should be accessible to authenticated users"
            );
        }
    }

    /**
     * Test that the ContactAuth middleware redirects guests to login.
     */
    public function test_contact_auth_middleware_redirects_guests_to_login()
    {
        // Test routes that use the ContactAuth middleware
        $routes = [
            ['GET', route('contacts.create')],
            ['GET', route('contacts.export')],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->call($method, $url);
            
            $response->assertRedirect(route('login'));
        }
    }

    /**
     * Test that the ContactAuth middleware allows authenticated users.
     */
    public function test_contact_auth_middleware_allows_authenticated_users()
    {
        $user = User::factory()->create();

        // Test routes that use the ContactAuth middleware
        $routes = [
            ['GET', route('contacts.create')],
            ['GET', route('contacts.export')],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->actingAs($user)->call($method, $url);
            
            $this->assertTrue(
                $response->isSuccessful() || $response->isRedirect(),
                "Route {$method} {$url} should be accessible to authenticated users"
            );
        }
    }

    /**
     * Test that the ContactAuth middleware returns JSON response for API requests.
     */
    public function test_contact_auth_middleware_returns_json_for_api_requests()
    {
        // Test API routes that use the ContactAuth middleware
        $response = $this->getJson(route('tags.api'));
        
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }
}
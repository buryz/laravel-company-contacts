<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use League\Csv\Writer;

class ProjectSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_connection_works()
    {
        // Test database connection by creating a user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_sanctum_authentication_works()
    {
        $user = User::factory()->create();
        
        // Test Sanctum token creation
        $token = $user->createToken('test-token');
        
        $this->assertNotNull($token->plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test-token',
        ]);
    }

    public function test_qr_code_package_works()
    {
        // Test QR code generation with SVG format (doesn't require imagick)
        $qrCode = QrCode::format('svg')->size(200)->generate('Test QR Code');
        
        $this->assertNotEmpty($qrCode);
        $this->assertStringContainsString('svg', $qrCode);
    }

    public function test_csv_package_works()
    {
        // Test CSV generation
        $csv = Writer::createFromString('');
        $csv->insertOne(['Name', 'Email', 'Company']);
        $csv->insertOne(['John Doe', 'john@example.com', 'Test Company']);
        
        $content = $csv->toString();
        
        $this->assertStringContainsString('Name,Email,Company', $content);
        $this->assertStringContainsString('"John Doe",john@example.com,"Test Company"', $content);
    }

    public function test_authentication_middleware_works()
    {
        // Test that protected routes require authentication
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
    }
}
<?php

namespace Modules\UserManagement\tests\Unit\Auth;

use App\Services\SendPulseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User;
use Mockery;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

class ForgotPasswordControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware, WithFaker;

    protected $sendPulseServiceMock;
    protected $requestUrl = 'api/v1/auth/password/forgot';

    protected function setUp(): void
    {
        parent::setUp();

        $this->sendPulseServiceMock = Mockery::mock(SendPulseService::class);
        $this->app->instance(SendPulseService::class, $this->sendPulseServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test sending password reset link with a valid email and generating a new token.
     */
    public function test_send_password_reset_link_with_valid_email_and_new_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password_token' => null,
            'password_token_expires_at' => null,
        ]);

        $this->sendPulseServiceMock->shouldReceive('sendEmail')->once()->andReturn(true);

        $response = $this->postJson($this->requestUrl, [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Password reset token sent successfully.',
            ]);

        $user->refresh();

        $this->assertNotNull($user->password_token);
        $this->assertNotNull($user->password_token_expires_at);
        $this->assertTrue($user->password_token_expires_at->isFuture());
    }

    /**
     * Test sending password reset link with a valid email and using an existing valid token.
     */
    public function test_send_password_reset_link_with_valid_email_and_existing_token()
    {
        $token = Str::random(40);
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password_token' => $token,
            'password_token_expires_at' => Carbon::now()->addMinutes(30),
        ]);

        $this->sendPulseServiceMock->shouldReceive('sendEmail')->once()->andReturn(true);

        $response = $this->postJson($this->requestUrl, [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Password reset token sent successfully.',
            ]);

        $user->refresh();

        $this->assertEquals($token, $user->password_token);
        $this->assertTrue($user->password_token_expires_at->isFuture());
    }

    /**
     * Test sending password reset link with a non-existing email.
     */
    public function test_send_password_reset_link_with_non_existing_email()
    {
        $response = $this->postJson($this->requestUrl, [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test sending password reset link with an expired token.
     */
    public function test_send_password_reset_link_with_expired_token()
    {
        $token = Str::random(40);
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password_token' => $token,
            'password_token_expires_at' => Carbon::now()->subMinutes(1),
        ]);

        $this->sendPulseServiceMock->shouldReceive('sendEmail')->once()->andReturn(true);

        $response = $this->postJson($this->requestUrl, [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Password reset token sent successfully.',
            ]);

        $user->refresh();

        $this->assertNotEquals($token, $user->password_token);
        $this->assertTrue($user->password_token_expires_at->isFuture());
    }
}

<?php

namespace Modules\UserManagement\tests\Unit\Auth;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Services\SendPulseService;
use Mockery;
use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Exception;

class ResetPasswordControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware, WithFaker;

    protected $requestUrl = 'api/v1/auth/password/reset';
    protected $sendPulseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sendPulseService = Mockery::mock(SendPulseService::class);
        $this->app->instance(SendPulseService::class, $this->sendPulseService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test validation for the reset password request.
     *
     * @return void
     */
    public function test_it_validates_the_request()
    {
        $response = $this->postJson($this->requestUrl, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token', 'password']);
    }

    /**
     * Test failure scenario when the reset token is invalid.
     *
     * @return void
     */
    public function test_it_fails_if_token_is_invalid()
    {
        $response = $this->postJson($this->requestUrl, [
            'token' => 'invalidtoken',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    /**
     * Test failure scenario when the reset token does not exist.
     *
     * @return void
     */
    public function test_it_fails_if_token_does_not_exist()
    {
        $response = $this->postJson($this->requestUrl, [
            'token' => str_repeat('a', 40),
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    /**
     * Test failure scenario when a duplicate reset token is found.
     *
     * @return void
     */
    public function test_it_fails_if_token_is_duplicated()
    {
        $token = str_repeat('a', 40);
        User::factory()->create(['password_token' => $token]);
        User::factory()->create(['password_token' => $token]);

        $response = $this->postJson($this->requestUrl, [
            'token' => $token,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Duplicate token found. Please request another.'
            ]);
    }

    /**
     * Test failure scenario when the reset token is expired.
     *
     * @return void
     */
    public function test_it_fails_if_token_is_expired()
    {
        $user = User::factory()->create([
            'password_token' => str_repeat('a', 40),
            'password_token_expires_at' => now()->subMinutes(30)
        ]);

        $response = $this->postJson($this->requestUrl, [
            'token' => $user->password_token,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Token expired or invalid.',
            ]);
    }

    /**
     * Test successful password reset scenario.
     *
     * @return void
     */
    public function test_it_resets_password_successfully()
    {
        $user = User::factory()->create([
            'password_token' => str_repeat('a', 40),
            'password_token_expires_at' => now()->addMinutes(30)
        ]);

        $this->sendPulseService->shouldReceive('sendEmail')
            ->once()
            ->with($user->email, 'Password Reset Successful', Mockery::any());

        $response = $this->postJson($this->requestUrl, [
            'token' => $user->password_token,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Password reset successful.'
            ]);

        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
        $this->assertNull($user->fresh()->password_token);
        $this->assertNull($user->fresh()->password_token_expires_at);
    }

    /**
     * Test failure scenario when password reset fails to save.
     *
     * @return void
     */
    public function test_it_fails_if_password_reset_fails()
    {
        $user = User::factory()->create([
            'password_token' => str_repeat('a', 40),
            'password_token_expires_at' => now()->addMinutes(30)
        ]);

        // Simulate a failure in saving the user
        $this->partialMock(User::class, function ($mock) use ($user) {
            $mock->shouldReceive('save')->andReturn(false);
        });

        $this->sendPulseService->shouldReceive('sendEmail')
            ->once()
            ->with($user->email, 'Password Reset Successful', Mockery::any());

        $response = $this->postJson($this->requestUrl, [
            'token' => $user->password_token,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'failed',
                'message' => 'Password reset failed.'
            ]);
    }
}

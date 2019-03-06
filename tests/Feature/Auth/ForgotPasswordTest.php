<?php

namespace Tests\Feature\Auth;

use App\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_an_email_password_form()
    {
        $response = $this->get('/password/reset');

        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.email');
    }

    public function test_user_cannot_view_an_email_password_form_when_authenticated()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/password/reset');

        $response->assertRedirect('/tasks');
    }

    public function test_user_receives_an_email_with_a_password_reset_link()
    {
        Notification::fake();
        $user = factory(User::class)->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->post('/password/email', [
            'email' => 'john@example.com',
        ]);

        $this->assertNotNull($token = DB::table('password_resets')->first());
        Notification::assertSentTo($user, ResetPassword::class, function ($notification, $channels) use ($token) {
            return Hash::check($notification->token, $token->token) === true;
        });
    }

    public function test_user_does_not_receive_email_when_registered()
    {
        Notification::fake();

        $response = $this->from('/password/email')->post('/password/email', [
            'email' => 'nobody@example.com',
        ]);

        $response->assertRedirect('/password/email');
        $response->assertSessionHasErrors('email');
        Notification::assertNotSentTo(factory(User::class)->make(['email' => 'nobody@example.com']), ResetPassword::class);
    }

    public function test_email_is_required()
    {
        $response = $this->from('/password/email')->post('/password/email', []);

        $response->assertRedirect('/password/email');
        $response->assertSessionHasErrors('email');
    }

    public function test_email_is_valid_email()
    {
        $response = $this->from('/password/email')->post('/password/email', [
            'email' => 'invalid-email',
        ]);

        $response->assertRedirect('/password/email');
        $response->assertSessionHasErrors('email');
    }
}

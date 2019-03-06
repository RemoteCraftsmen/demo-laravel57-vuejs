<?php

namespace Tests\Feature\Auth;

use App\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function getValidToken($user)
    {
        return Password::broker()->createToken($user);
    }

    public function test_user_can_view_a_password_reset_form()
    {
        $user = factory(User::class)->create();
        $token = $this->getValidToken($user);

        $response = $this->get('password/reset/' . $token);
        
        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.reset');
        $response->assertViewHas('token', $token);
    }

    public function test_user_cannot_view_a_password_reset_form_when_authenticated()
    {
        $user = factory(User::class)->create();
        $token = $this->getValidToken($user);

        $response = $this->actingAs($user)->get('password/reset/' . $token);
        
        $response->assertRedirect('/tasks');
    }

    public function test_user_can_reset_password_with_valid_token()
    {
        Event::fake();
        $user = factory(User::class)->create();
        $token = $this->getValidToken($user);

        $response = $this->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        
        $response->assertRedirect('/tasks');
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(PasswordReset::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }

    public function test_user_cannot_reset_password_with_invalid_token()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('old-password'),
        ]);
        $invalidToken = "invalidToken";
        
        $response = $this->from('/password/reset/' . $invalidToken)->post('/password/reset/', [
            'token' => $invalidToken,
            'email' => $user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);
        
        $response->assertRedirect('/password/reset/' . $invalidToken);
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_user_cannot_reset_password_without_providing_a_new_password()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('old-password'),
        ]);
        $token = $this->getValidToken($user);

        $response = $this->from('/password/reset/' . $token)->post('/password/reset/', [
            'token' => $token,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ]);
        
        $response->assertRedirect('/password/reset/' . $token);
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_user_cannot_reset_password_without_providing_an_email()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt('old-password'),
        ]);
        $token = $this->getValidToken($user);

        $response = $this->from('/password/reset/' . $token)->post('/password/reset/', [
            'token' => $token,
            'email' => '',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/password/reset/' . $token);
        $response->assertSessionHasErrors('email');
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }
}

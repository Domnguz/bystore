<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CodigoVerificacion;
use App\Services\CorreoVerificacion;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class AccountRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Notification::fake();
    }

    private function registration(array $overrides = []): array
    {
        return [...[
            'name' => 'Cliente de prueba', 'apellidos' => 'Ludeñas García', 'dni' => '01234567', 'email' => 'cliente@example.test',
            'password' => 'Contrasena1234', 'password_confirmation' => 'Contrasena1234',
            'privacy_accepted' => '1', 'captcha_id' => str_repeat('a', 40), 'captcha' => '123456',
        ], ...$overrides];
    }

    private function challenge(int $minutes = 10): static
    {
        return $this->withSession(['registration_captchas' => [str_repeat('a', 40) => [
            'hash' => hash('sha256', '123456'), 'expires_at' => now()->addMinutes($minutes)->timestamp,
        ]]]);
    }

    private function profile(User $user, array $overrides = []): array
    {
        return [...['name' => $user->name, 'email' => $user->email, 'dni' => $user->dni,
            'privacy_accepted' => '1'], ...$overrides];
    }

    private function verificationCode(User $user): string
    {
        $user->sendEmailVerificationNotification();

        return Notification::sent($user, CodigoVerificacion::class)->last()->codigo;
    }

    public function test_registration_has_a_real_png_and_does_not_expose_the_answer(): void
    {
        $response = $this->get('/registro')->assertOk()->assertSee('name="dni"', false)
            ->assertSee('name="apellidos"', false)->assertSee('autocomplete="family-name"', false)
            ->assertSee('name="privacy_accepted"', false)->assertSee('name="captcha"', false);
        $captcha = $response->viewData('captcha');
        $png = base64_decode(substr($captcha['image'], strlen('data:image/png;base64,')), true);
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8));
        $size = getimagesizefromstring($png);
        $this->assertSame([235, 72], array_slice($size, 0, 2));
        $response->assertDontSee(session('registration_captchas')[$captcha['id']]['hash']);
        $this->getJson('/registro')->assertOk()->assertJsonStructure(['id', 'image']);
        $this->assertCount(2, session('registration_captchas'));
    }

    public function test_successful_registration_requires_verification_and_preserves_leading_zeroes(): void
    {
        $this->challenge()->post('/registro', $this->registration([
            'email' => 'CLIENTE@example.test', 'rol' => 'admin', 'email_verified_at' => now(),
        ]))->assertRedirect('/verificar-correo')->assertSessionHasNoErrors();
        $user = User::sole();
        $this->assertSame('cliente', $user->rol);
        $this->assertSame('Cliente de prueba', $user->name);
        $this->assertSame('Ludeñas García', $user->apellidos);
        $this->assertSame('01234567', $user->dni);
        $this->assertSame('cliente@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->dni_consent_at);
        $this->assertAuthenticatedAs($user);
        $this->assertArrayNotHasKey('dni', $user->toArray());
        Notification::assertSentTo($user, CodigoVerificacion::class);
    }

    public function test_duplicate_dni_with_another_email_cannot_create_another_account(): void
    {
        User::factory()->create(['dni' => '01234567']);
        $this->challenge()->post('/registro', $this->registration())
            ->assertSessionHasErrors('dni');
        $this->assertDatabaseCount('users', 1);
        Notification::assertNothingSent();
    }

    public function test_database_also_rejects_duplicate_dni_when_validation_is_bypassed(): void
    {
        User::factory()->create(['dni' => '01234567']);
        $this->expectException(UniqueConstraintViolationException::class);
        User::factory()->create(['dni' => '01234567']);
    }

    public function test_duplicate_email_differing_only_in_case_is_rejected(): void
    {
        User::factory()->create(['email' => 'cliente@example.test']);
        $this->challenge()->post('/registro', $this->registration(['email' => 'CLIENTE@example.test']))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_missing_names_surnames_dni_and_privacy_acceptance_are_rejected(): void
    {
        $data = $this->registration();
        unset($data['name'], $data['apellidos'], $data['dni'], $data['privacy_accepted']);
        $this->challenge()->post('/registro', $data)->assertSessionHasErrors(['name', 'apellidos', 'dni', 'privacy_accepted']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_dni_formats_are_rejected(): void
    {
        foreach (['1234567', '123456789', '1234abcd', '１２３４５６７８', ['01234567']] as $dni) {
            $this->challenge()->post('/registro', $this->registration(['dni' => $dni]))->assertSessionHasErrors('dni');
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_captcha_missing_wrong_expired_and_from_another_session_are_rejected(): void
    {
        $this->post('/registro', $this->registration())->assertSessionHasErrors('captcha');
        $this->challenge()->post('/registro', $this->registration(['captcha' => '654321']))->assertSessionHasErrors('captcha');
        $this->challenge(-1)->post('/registro', $this->registration())->assertSessionHasErrors('captcha');
        $this->challenge()->post('/registro', $this->registration(['captcha_id' => str_repeat('b', 40)]))->assertSessionHasErrors('captcha');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_captcha_is_consumed_even_when_registration_fails(): void
    {
        $this->challenge()->post('/registro', $this->registration(['dni' => 'invalido']))->assertSessionHasErrors('dni');
        $this->post('/registro', $this->registration())->assertSessionHasErrors('captcha');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_honeypot_and_registration_rate_limit_block_automation(): void
    {
        $this->challenge()->post('/registro', $this->registration(['website' => 'https://bot.test']))->assertSessionHasErrors('captcha');
        for ($i = 0; $i < 5; $i++) {
            $this->post('/registro', $this->registration())->assertSessionHasErrors('captcha');
        }
        $this->post('/registro', $this->registration())->assertStatus(429);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_form_limits_challenge_storage(): void
    {
        for ($i = 0; $i < 8; $i++) {
            $this->getJson('/registro')->assertOk();
        }
        $this->assertCount(5, session('registration_captchas'));
    }

    public function test_unverified_customer_cannot_get_or_submit_checkout_but_can_access_history(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get('/checkout')->assertRedirect('/verificar-correo');
        $this->post('/checkout', [])->assertRedirect('/verificar-correo');
        $this->postJson('/checkout', [])->assertForbidden();
        $this->get('/mis-pedidos')->assertOk();
        $this->get('/profile')->assertOk();
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_legacy_account_without_dni_is_redirected_to_complete_profile(): void
    {
        $user = User::factory()->create(['dni' => null]);
        $this->actingAs($user)->get('/checkout')->assertRedirect('/profile');
        $this->post('/checkout', [])->assertRedirect('/profile');
        $this->postJson('/checkout', [])->assertForbidden();
        $this->get('/mis-pedidos')->assertOk();
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_legacy_account_completes_dni_with_consent_and_returns_to_checkout(): void
    {
        $user = User::factory()->create(['dni' => null]);
        $this->actingAs($user)->get('/checkout')->assertRedirect('/profile');
        $this->put('/profile', $this->profile($user, ['dni' => '01234567', 'privacy_accepted' => '0']))
            ->assertSessionHasErrors('privacy_accepted');
        $this->put('/profile', $this->profile($user, ['dni' => '01234567']))
            ->assertSessionHasNoErrors()->assertRedirect('/checkout');
        $this->assertSame('01234567', $user->fresh()->dni);
        $this->assertNotNull($user->fresh()->dni_consent_at);
        $this->get('/checkout')->assertRedirect('/carrito');
    }

    public function test_profile_cannot_change_dni_or_claim_another_accounts_dni(): void
    {
        $user = User::factory()->create(['dni' => '01234567']);
        $this->actingAs($user)->put('/profile', $this->profile($user, ['dni' => '12345678']))
            ->assertSessionHasErrors('dni');
        $this->assertSame('01234567', $user->fresh()->dni);
        $legacy = User::factory()->create(['dni' => null]);
        $this->actingAs($legacy)->put('/profile', $this->profile($legacy, ['dni' => '01234567']))
            ->assertSessionHasErrors('dni');
        $this->assertNull($legacy->fresh()->dni);
    }

    public function test_verification_requires_authentication_and_a_code_for_the_current_user(): void
    {
        $user = User::factory()->unverified()->create();
        $codigo = $this->verificationCode($user);
        $this->post('/verificar-correo', ['codigo' => $codigo])->assertRedirect('/login');
        $other = User::factory()->unverified()->create();
        $this->actingAs($other)->post('/verificar-correo', ['codigo' => $codigo, 'id' => $user->id])
            ->assertSessionHasErrors('codigo');
        $this->actingAs($user)->get('/verificar-correo/'.$user->id.'/'.sha1($user->email))->assertNotFound();
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertNull($other->fresh()->email_verified_at);
    }

    public function test_valid_code_activates_account_once_and_returns_to_checkout(): void
    {
        Event::fake([Verified::class]);
        $user = User::factory()->unverified()->create();
        $codigo = $this->verificationCode($user);
        $this->actingAs($user)->get('/checkout')->assertRedirect('/verificar-correo');
        $this->post('/verificar-correo', ['codigo' => $codigo])->assertRedirect('/checkout')->assertSessionHasNoErrors();
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNull(Cache::get('email-verification:'.$user->id));
        Event::assertDispatchedTimes(Verified::class, 1);
        $this->get('/checkout')->assertRedirect('/carrito');
        $this->post('/verificar-correo', ['codigo' => $codigo])->assertRedirect('/profile');
        Event::assertDispatchedTimes(Verified::class, 1);
    }

    public function test_missing_malformed_and_expired_codes_cannot_verify_an_account(): void
    {
        $user = User::factory()->unverified()->create();
        $codigo = $this->verificationCode($user);
        $this->actingAs($user)->post('/verificar-correo', [])->assertSessionHasErrors('codigo');
        foreach (['12345', '1234567', 'abcdef', '１２３４５６', ['123456']] as $invalid) {
            $this->post('/verificar-correo', ['codigo' => $invalid])->assertSessionHasErrors('codigo');
        }
        $this->travel(CorreoVerificacion::MINUTOS_VALIDEZ)->minutes();
        $this->post('/verificar-correo', ['codigo' => $codigo])->assertSessionHasErrors('codigo');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_five_wrong_codes_invalidate_the_challenge_even_with_a_new_session(): void
    {
        $user = User::factory()->unverified()->create();
        $codigo = $this->verificationCode($user);
        $wrong = $codigo === '000000' ? '000001' : '000000';
        $this->actingAs($user);
        for ($i = 0; $i < CorreoVerificacion::MAX_INTENTOS; $i++) {
            $this->post('/verificar-correo', ['codigo' => $wrong])->assertSessionHasErrors('codigo');
            session()->invalidate();
        }
        $this->post('/verificar-correo', ['codigo' => $codigo])->assertSessionHasErrors('codigo');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertNull(Cache::get('email-verification:'.$user->id));
    }

    public function test_resend_invalidates_the_previous_code_and_stores_only_a_keyed_hash(): void
    {
        $user = User::factory()->unverified()->create();
        $old = $this->verificationCode($user);
        $this->actingAs($user)->post('/verificar-correo/reenviar')->assertRedirect();
        $new = Notification::sent($user, CodigoVerificacion::class)->last()->codigo;
        $this->assertNotSame($old, $new);
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/D', $new);
        $desafio = Cache::get('email-verification:'.$user->id);
        $this->assertSame(hash_hmac('sha256', $new, config('app.key')), $desafio['hash']);
        $this->assertArrayNotHasKey('codigo', $desafio);
        $this->post('/verificar-correo', ['codigo' => $old])->assertSessionHasErrors('codigo')
            ->assertSessionMissing('_old_input.codigo');
        $this->post('/verificar-correo', ['codigo' => ' '.$new.' '])->assertRedirect('/profile')->assertSessionHasNoErrors();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_codes_with_leading_zeroes_are_accepted_and_verification_requests_are_limited(): void
    {
        $user = User::factory()->unverified()->create();
        Cache::put('email-verification:'.$user->id, [
            'hash' => hash_hmac('sha256', '012345', config('app.key')),
            'email' => $user->email, 'expires_at' => now()->addMinutes(10)->timestamp, 'attempts' => 0,
        ], now()->addMinutes(10));
        $this->actingAs($user)->post('/verificar-correo', ['codigo' => '012345'])->assertRedirect('/profile');
        $this->assertNotNull($user->fresh()->email_verified_at);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/verificar-correo', ['codigo' => '012345'])->assertRedirect('/profile');
        }
        $this->post('/verificar-correo', ['codigo' => '012345'])->assertStatus(429);
    }

    public function test_resend_is_limited_and_does_not_send_to_already_verified_users(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->from('/verificar-correo')->post('/verificar-correo/reenviar')->assertRedirect('/verificar-correo');
        Notification::assertSentToTimes($user, CodigoVerificacion::class, 1);
        $this->post('/verificar-correo/reenviar')->assertStatus(429);
        $this->travel(61)->seconds();
        $user->markEmailAsVerified();
        $this->post('/verificar-correo/reenviar')->assertRedirect('/profile');
        Notification::assertSentToTimes($user, CodigoVerificacion::class, 1);
    }

    public function test_email_change_revokes_verification_and_invalidates_previous_code(): void
    {
        $user = User::factory()->unverified()->create();
        $old = $this->verificationCode($user);
        $user->markEmailAsVerified();
        $this->actingAs($user)->put('/profile', $this->profile($user, ['email' => 'NUEVO@example.test']))
            ->assertSessionHasNoErrors()->assertRedirect('/verificar-correo');
        $this->assertSame('nuevo@example.test', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
        Notification::assertSentTo($user, CodigoVerificacion::class);
        $this->get('/verificar-correo')->assertOk();
        $this->post('/verificar-correo', ['codigo' => $old])->assertSessionHasErrors('codigo');
        $this->post('/checkout', [])->assertRedirect('/verificar-correo');
        $new = Notification::sent($user, CodigoVerificacion::class)->last()->codigo;
        $this->post('/verificar-correo', ['codigo' => $new])->assertRedirect('/profile');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_unchanged_email_keeps_verification_and_does_not_send_mail(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/profile', $this->profile($user))->assertSessionHasNoErrors();
        $this->assertNotNull($user->fresh()->email_verified_at);
        Notification::assertNothingSent();
    }

    public function test_verification_email_has_a_code_without_private_data_or_a_verification_link(): void
    {
        $user = User::factory()->unverified()->create(['dni' => '01234567']);
        $message = (new CodigoVerificacion('012345'))->toMail($user);
        $this->assertSame('Tu código de verificación de ByteStore', $message->subject);
        $this->assertNull($message->actionUrl);
        $this->assertSame('012345', $message->viewData['codigo']);
        $this->assertArrayNotHasKey('dni', $message->viewData);
        $this->assertArrayNotHasKey('email', $message->viewData);
    }

    public function test_verification_email_embeds_logo_and_has_plain_text_without_exposing_private_data(): void
    {
        config(['mail.default' => 'array']);
        $user = User::factory()->unverified()->create(['name' => '<script>Cliente</script>', 'dni' => '01234567']);
        $codigo = $this->verificationCode($user);
        app(MailChannel::class)->send($user, Notification::sent($user, CodigoVerificacion::class)->last());
        $transport = Mail::mailer('array')->getSymfonyTransport();
        $this->assertCount(1, $transport->messages());
        $email = $transport->messages()->last()->getOriginalMessage();
        $html = $email->getHtmlBody();
        $this->assertSame('Tu código de verificación de ByteStore', $email->getSubject());
        $this->assertStringContainsString('Confirma tu correo', $html);
        $this->assertStringContainsString('src="cid:', $html);
        $this->assertStringContainsString('alt="ByteStore"', $html);
        $this->assertStringContainsString('&lt;script&gt;Cliente&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>Cliente</script>', $html);
        $this->assertStringNotContainsString($user->dni, $html);
        $this->assertStringContainsString('Tu código de verificación: '.$codigo, $email->getTextBody());
        $this->assertStringContainsString($codigo, $html);
        $this->assertStringNotContainsString('signature=', $email->getTextBody());
        $this->assertStringNotContainsString('cid:', $email->getTextBody());
        $this->assertCount(1, $email->getAttachments());
        $this->assertSame('inline', $email->getAttachments()[0]->getDisposition());
        $this->assertSame('image/png', $email->getAttachments()[0]->getMediaType().'/'.$email->getAttachments()[0]->getMediaSubtype());
        $this->assertStringContainsString('10 minutos', $html);
        $this->actingAs($user)->post('/verificar-correo', ['codigo' => $codigo])->assertRedirect('/profile');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_mail_failure_preserves_account_and_allows_retry(): void
    {
        Notification::shouldReceive('send')->once()->andThrow(new TransportException('Fallo SMTP simulado'));
        $this->challenge()->post('/registro', $this->registration())
            ->assertRedirect('/verificar-correo')->assertSessionHas('error');
        $this->assertDatabaseCount('users', 1);
        $this->assertAuthenticatedAs(User::sole());
        $this->assertNull(User::sole()->email_verified_at);
        $this->assertNull(Cache::get('email-verification:'.User::sole()->id));
    }

    public function test_failed_resend_keeps_the_previous_code_valid(): void
    {
        $user = User::factory()->unverified()->create();
        $codigo = $this->verificationCode($user);
        Notification::shouldReceive('send')->once()->andThrow(new TransportException('Fallo SMTP simulado'));
        $this->actingAs($user)->from('/verificar-correo')->post('/verificar-correo/reenviar')
            ->assertRedirect('/verificar-correo')->assertSessionHas('error');
        $this->post('/verificar-correo', ['codigo' => $codigo])->assertRedirect('/profile');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_wrong_code_attempts_persist_with_the_database_cache_driver(): void
    {
        config(['cache.default' => 'database']);
        $user = User::factory()->unverified()->create();
        $codigo = $this->verificationCode($user);
        $wrong = $codigo === '000000' ? '000001' : '000000';
        $this->actingAs($user)->post('/verificar-correo', ['codigo' => $wrong])->assertSessionHasErrors('codigo');
        $this->assertSame(1, Cache::get('email-verification:'.$user->id)['attempts']);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_admin_email_edit_invalidates_a_code_sent_to_the_previous_address(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $user = User::factory()->unverified()->create(['rol' => 'cliente']);
        $codigo = $this->verificationCode($user);
        $this->actingAs($admin)->put('/admin/usuarios/'.$user->id,
            [...$this->profile($user), 'email' => 'corregido@example.test', 'rol' => 'cliente'])
            ->assertSessionHasNoErrors();
        $this->actingAs($user->fresh())->post('/verificar-correo', ['codigo' => $codigo])->assertSessionHasErrors('codigo');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_log_mailer_does_not_claim_verification_email_was_delivered(): void
    {
        config(['mail.default' => 'log']);
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/verificar-correo')->assertOk()
            ->assertSee('El envío de correos aún no está disponible.')
            ->assertDontSee('Escribe el código de 6 dígitos que enviamos')
            ->assertDontSee('Reenviar código')
            ->assertSee('name="codigo"', false);
        $this->from('/verificar-correo')->post('/verificar-correo/reenviar')
            ->assertRedirect('/verificar-correo')->assertSessionHas('error')->assertSessionMissing('success');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_registration_with_log_mailer_preserves_account_and_reports_unavailable_delivery(): void
    {
        config(['mail.default' => 'log']);

        $this->challenge()->post('/registro', $this->registration())
            ->assertRedirect('/verificar-correo')->assertSessionHas('error')->assertSessionMissing('success');
        $this->assertAuthenticatedAs(User::sole());
        $this->assertNull(User::sole()->email_verified_at);
    }

    public function test_email_change_with_log_mailer_still_requires_verifying_the_new_address(): void
    {
        config(['mail.default' => 'log']);
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', $this->profile($user, ['email' => 'nuevo@example.test']))
            ->assertRedirect('/verificar-correo')->assertSessionHas('error')->assertSessionMissing('success');
        $this->assertSame('nuevo@example.test', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_smtp_mailer_keeps_the_normal_verification_and_resend_flow(): void
    {
        config(['mail.default' => 'smtp']);
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/verificar-correo')->assertOk()
            ->assertSee('Reenviar código')
            ->assertSee('name="codigo"', false)
            ->assertSee('autocomplete="one-time-code"', false)
            ->assertDontSee('El envío de correos aún no está disponible.');
        $this->from('/verificar-correo')->post('/verificar-correo/reenviar')
            ->assertRedirect('/verificar-correo')->assertSessionHas('success')->assertSessionMissing('error');
        Notification::assertSentToTimes($user, CodigoVerificacion::class, 1);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_admin_email_edit_revokes_customer_verification_without_locking_admin_out(): void
    {
        $admin = User::factory()->unverified()->create(['rol' => 'admin']);
        $customer = User::factory()->create(['rol' => 'cliente']);
        $this->actingAs($admin)->put('/admin/usuarios/'.$customer->id,
            [...$this->profile($customer), 'email' => 'cambiado@example.test', 'rol' => 'cliente'])
            ->assertSessionHasNoErrors();
        $this->assertNull($customer->fresh()->email_verified_at);
        $this->get('/admin')->assertOk();
    }
}

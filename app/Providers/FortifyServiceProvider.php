<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewClient; // Tu acción B2B
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Vista personalizada de registro
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // Usar la acción personalizada para crear clientes B2B
        Fortify::createUsersUsing(CreateNewUser::class);

        // Redirigir después del registro a la vista de pendiente
        Fortify::redirects('register', '/registration-pending'); 

        // IMPORTANTE: Validar estado del usuario en el login
        Fortify::authenticateUsing(function (Request $request) {
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                // Verificar si el usuario está inactivo (pendiente de aprobación)
                if (!$user->is_active) {
                    throw ValidationException::withMessages([
                        'email' => ['Tu cuenta está pendiente de aprobación. Recibirás un correo cuando sea activada.'],
                    ]);
                }

                return $user;
            }

            return null;
        });

        // Otras configuraciones de Fortify
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Rate limiting para login
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        // Rate limiting para two-factor
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
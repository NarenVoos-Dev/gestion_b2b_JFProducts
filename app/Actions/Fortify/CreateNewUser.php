<?php

namespace App\Actions\Fortify;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a new client registration (pending approval).
     * The User account will be created by admin upon approval.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'type_document' => ['required', 'string', 'in:NIT,CC,CE'],
            'document' => ['required', 'string', 'max:50', 'unique:clients,document'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                'unique:users,email',
                'unique:clients,email'
            ],
            'password' => $this->passwordRules(),
            'phone1' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            // Mensajes personalizados
            'document.unique' => 'Este número de documento ya está registrado. Si ya tienes una cuenta, inicia sesión.',
            'email.unique' => 'Este correo electrónico ya está registrado. Si ya tienes una cuenta, inicia sesión.',
            'name.required' => 'El nombre de la empresa es obligatorio.',
            'type_document.required' => 'Debes seleccionar el tipo de documento.',
            'document.required' => 'El número de documento es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debes ingresar un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'terms.accepted' => 'Debes aceptar los términos y condiciones.',
        ])->validate();

        // Crear el cliente en estado pendiente (is_active = false)
        $client = Client::create([
            'business_id' => 1, // ID del negocio principal (ajustar según tu lógica)
            'name' => $input['name'],
            'type_document' => $input['type_document'],
            'document' => $input['document'],
            'email' => $input['email'],
            'phone1' => $input['phone1'] ?? null,
            'address' => $input['address'] ?? null,
            'is_active' => false, // Pendiente de aprobación
            'credit_limit' => 0,
            'price_list_id' => null, // El admin asignará la lista de precios
        ]);

        // Crear el usuario pero inactivo hasta aprobación
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'client_id' => $client->id,
            'is_active' => false, // Usuario inactivo hasta aprobación
        ]);

        // Asignar rol de cliente automáticamente
        $user->assignRole('cliente');

        return $user;
    }
}

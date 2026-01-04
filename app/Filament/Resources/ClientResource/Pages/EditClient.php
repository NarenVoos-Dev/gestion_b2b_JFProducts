<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Hook después de guardar el registro
     */
    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->data;

        // Solo ejecutamos si el cliente fue marcado como activo y NO tiene usuario
        if ($record->is_active && $record->user === null) {
            
            // Verificamos que la contraseña se haya ingresado en el campo temporal
            $password = $data['user_password'] ?? null;

            if (empty($password)) {
                Notification::make()
                    ->title('Error de Activación')
                    ->body('El campo de contraseña temporal es obligatorio para crear el usuario de acceso.')
                    ->danger()
                    ->send();
                
                // Si la contraseña falta, deshacer el cambio de is_active
                $record->is_active = false;
                $record->saveQuietly();

                $this->halt();
                return;
            }

            // Verificar que el cliente tenga email
            if (empty($record->email)) {
                Notification::make()
                    ->title('Error de Activación')
                    ->body('El cliente debe tener un correo electrónico registrado para crear el acceso al portal.')
                    ->danger()
                    ->send();
                
                // Deshacer el cambio de is_active
                $record->is_active = false;
                $record->saveQuietly();

                $this->halt();
                return;
            }

            // Verificar que el email no esté ya en uso por otro usuario
            $emailExists = \App\Models\User::where('email', $record->email)
                ->where('id', '!=', $record->user_id ?? 0)
                ->exists();

            if ($emailExists) {
                Notification::make()
                    ->title('Error de Activación')
                    ->body('El correo electrónico ya está registrado por otro usuario.')
                    ->danger()
                    ->send();
                
                $record->is_active = false;
                $record->saveQuietly();

                $this->halt();
                return;
            }

            // Crear el nuevo usuario
            $user = \App\Models\User::create([
                'name' => $record->name, 
                'email' => $record->email,
                'client_id' => $record->id,
                'password' => Hash::make($password),
                'is_active' => true, // IMPORTANTE: Activar el usuario
                'estado' => 'activo', 
            ]);

            // Asignar el rol 'cliente' si usas Spatie Permissions
            $user->assignRole('cliente'); 

            Notification::make()
                ->title('Cliente Activado y Usuario Creado')
                ->body("El acceso para {$record->name} ha sido creado con éxito.")
                ->success()
                ->send();
        }
        
        // Si el cliente ya tiene usuario y se está activando, activar también el usuario
        if ($record->is_active && $record->user) {
            $record->user->update([
                'is_active' => true,
                'estado' => 'activo'
            ]);
        }
        
        // Si se desactiva el cliente, desactivar también el usuario
        if (!$record->is_active && $record->user) {
            $record->user->update([
                'is_active' => false,
                'estado' => 'inactivo'
            ]);
            
            Notification::make()
                ->title('Acceso Desactivado')
                ->body('El usuario de acceso ha sido desactivado.')
                ->warning()
                ->send();
        }
    }
}
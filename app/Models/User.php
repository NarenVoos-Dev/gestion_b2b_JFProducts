<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; 
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Models\Business;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


// CAMBIO: Se añade 'implements FilamentUser' para que el modelo sea compatible con Filament.
class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'business_id',
        'client_id',   
        'is_active',
        'estado',
        'profile_photo_path'  
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
    
    /**
     * Un usuario pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * CAMBIO: Se añade el método canAccessPanel para controlar el acceso a los diferentes paneles.
     * Esta función es crucial para la seguridad y la lógica de roles.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Esta lógica previene que un super-admin entre al panel de negocio y viceversa
        if ($panel->getId() === 'super-admin') {
            return $this->hasRole('super-admin');
        }
        
        if ($panel->getId() === 'admin') {
            return !$this->hasRole('super-admin');
        }
        
        if ($panel->getId() === 'pos') {
            return $this->hasAnyRole(['admin']);
        }

        return true;
    }
     public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
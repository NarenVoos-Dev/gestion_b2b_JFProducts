<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CartItem;
use App\Models\User;

class CleanExpiredCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar carritos expirados y rechazados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Solo limpiar items que:
        // 1. Han sido rechazados (extension_status = 'rejected')
        // 2. Han alcanzado el límite de 3 prórrogas y están expirados
        
        $itemsToDelete = CartItem::where(function($query) {
            // Caso 1: Prórroga rechazada
            $query->where('extension_status', 'rejected')
                  ->where('expiration_date', '<=', now());
        })->orWhere(function($query) {
            // Caso 2: 3 prórrogas usadas y expirado
            $query->where('extension_count', '>=', 3)
                  ->where('expiration_date', '<=', now())
                  ->where('extension_status', '!=', 'pending');
        })->get();
        
        if ($itemsToDelete->isEmpty()) {
            $this->info('No hay carritos para limpiar.');
            return 0;
        }
        
        $usersAffected = $itemsToDelete->pluck('user_id')->unique();
        
        foreach ($usersAffected as $userId) {
            $user = User::find($userId);
            $userItems = $itemsToDelete->where('user_id', $userId);
            
            $reason = $userItems->first()->extension_status === 'rejected' 
                ? 'prórroga rechazada' 
                : 'límite de 3 prórrogas alcanzado';
            
            $this->info("Limpiando {$userItems->count()} items del carrito del usuario {$user->email} ({$reason})");
            
            // Aquí se puede agregar notificación al usuario
            // Notification::send($user, new CartCleanedNotification($userItems, $reason));
        }
        
        $count = $itemsToDelete->count();
        $itemsToDelete->each->delete();
        
        $this->info("✓ Se eliminaron {$count} items del carrito.");
        
        return 0;
    }
}

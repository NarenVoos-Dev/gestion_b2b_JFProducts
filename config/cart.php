<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cart Expiration Time
    |--------------------------------------------------------------------------
    |
    | Tiempo en minutos que un item permanece en el carrito antes de expirar.
    | Por defecto: 2 minutos para pruebas, cambiar a 1440 (24 horas) en producción
    |
    */
    'expiration_minutes' => env('CART_EXPIRATION_MINUTES', 2),
    
    /*
    |--------------------------------------------------------------------------
    | Maximum Extensions
    |--------------------------------------------------------------------------
    |
    | Número máximo de prórrogas que un cliente puede solicitar
    |
    */
    'max_extensions' => 3,
    
    /*
    |--------------------------------------------------------------------------
    | Warning Time
    |--------------------------------------------------------------------------
    |
    | Minutos antes de la expiración para mostrar advertencia al cliente
    |
    */
    'warning_minutes' => 30,
];

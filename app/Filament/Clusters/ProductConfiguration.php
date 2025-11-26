<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class ProductConfiguration extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configuracion';
    protected static ?string $navigationLabel = 'Configuración de Productos';
    protected static ?int $navigationSort = 56;
    
    // Sin grupo para que aparezca en el nivel superior
    // protected static ?string $navigationGroup = 'Configuración';
    
    // Título del cluster
    protected static ?string $title = 'Configuración de Productos';
    
    // Descripción opcional
    public static function getNavigationBadge(): ?string
    {
        return null; // Puedes agregar un badge si lo necesitas
    }
}

<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;
    protected static string $view = 'filament.resources.sale-resource.pages.view-sale';

    protected function getHeaderActions(): array
    {
        return [
            // Botón para editar la venta (solo Pendiente/Separación)
            Actions\EditAction::make()
                ->visible(fn () => in_array($this->record->status, ['Pendiente', 'Separación'])),
            // Acción para descargar la factura en PDF
            Actions\Action::make('downloadInvoice')
                ->label('Descargar Factura PDF')
                ->color('info')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->downloadInvoicePdf()),
        ];
    }

    // Método que genera y descarga el PDF
    public function downloadInvoicePdf()
    {
        $sale = $this->record->load(['client', 'items.product.unitOfMeasure', 'items.unitOfMeasure', 'items.lots']);
        
        $pdf = Pdf::loadView('pdf.invoice', ['sale' => $sale]);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, "factura-{$sale->id}.pdf");
    }
}
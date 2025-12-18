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
            // Botón para facturar pedido B2B con PDF
            Actions\Action::make('invoice')
                ->label('Facturar Pedido')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->visible(fn () => $this->record->source === 'b2b' && in_array($this->record->status, ['Pendiente', 'Separación']))
                ->form([
                    \Filament\Forms\Components\TextInput::make('invoice_number')
                        ->label('Número de Factura')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('Ej: FAC-2025-001'),
                    
                    \Filament\Forms\Components\FileUpload::make('invoice_pdf')
                        ->label('PDF de Factura')
                        ->disk('public')
                        ->directory('invoices')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(5120) // 5MB
                        ->required()
                        ->helperText('Sube el PDF de la factura (máx. 5MB)'),
                    
                    \Filament\Forms\Components\DateTimePicker::make('invoiced_at')
                        ->label('Fecha de Facturación')
                        ->default(now())
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'Facturado',
                        'invoice_number' => $data['invoice_number'],
                        'invoice_pdf_path' => $data['invoice_pdf'],
                        'invoiced_at' => $data['invoiced_at'],
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Pedido Facturado')
                        ->body('El pedido ha sido marcado como facturado y el PDF ha sido guardado.')
                        ->send();
                }),
            
            // Botón para editar pedidos B2B
            Actions\Action::make('edit-b2b')
                ->label('Editar Pedido B2B')
                ->icon('heroicon-o-pencil')
                ->url(fn () => route('filament.admin.resources.sales.edit-b2b', ['record' => $this->record->id]))
                ->visible(fn () => $this->record->source === 'b2b' && in_array($this->record->status, ['Pendiente', 'Separación']))
                ->color('warning'),
            
            // Botón para editar la venta (solo Pendiente/Separación y NO B2B)
            Actions\EditAction::make()
                ->visible(fn () => $this->record->source !== 'b2b' && in_array($this->record->status, ['Pendiente', 'Separación'])),
            
            // Botón para ver/abrir la factura PDF subida
            Actions\Action::make('viewInvoicePdf')
                ->label('Ver Factura')
                ->color('success')
                ->icon('heroicon-o-eye')
                ->url(fn () => $this->record->getInvoicePdfUrl())
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->hasInvoicePdf()),
            
            // Botón para descargar la factura PDF subida
            Actions\Action::make('downloadUploadedInvoice')
                ->label('Descargar Factura')
                ->color('info')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    if ($this->record->hasInvoicePdf()) {
                        return \Storage::disk('public')->download(
                            $this->record->invoice_pdf_path,
                            "factura-{$this->record->invoice_number}.pdf"
                        );
                    }
                })
                ->visible(fn () => $this->record->hasInvoicePdf()),
            
            // Acción para descargar pedido generado en PDF
            Actions\Action::make('downloadInvoice')
                ->label('Descargar Pedido PDF')
                ->color('warning')
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
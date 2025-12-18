<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedido #{{ $sale->id }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .info-section { 
            margin-bottom: 20px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .info-section p {
            margin: 5px 0;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #4CAF50; 
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .totals { 
            margin-top: 30px; 
            text-align: right; 
        }
        .totals table { 
            width: 350px; 
            margin-left: auto;
            border: 2px solid #333;
        }
        .totals th {
            background-color: #333;
        }
        .totals .total-row {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .lot-info {
            font-size: 10px;
            color: #666;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEDIDO DE VENTA B2B</h1>
        <p style="margin: 5px 0;">Pedido #{{ $sale->id }}</p>
        <p style="margin: 5px 0;">Fecha: {{ $sale->date->format('d/m/Y H:i') }}</p>
    </div>
    
    <div class="info-section">
        <p><strong>Cliente:</strong> {{ $sale->client->name }}</p>
        <p><strong>Estado:</strong> <span style="color: {{ $sale->status === 'Facturado' ? 'green' : 'orange' }}">{{ $sale->status }}</span></p>
        @if($sale->invoice_number)
            <p><strong>Número de Factura:</strong> {{ $sale->invoice_number }}</p>
            <p><strong>Fecha de Facturación:</strong> {{ $sale->invoiced_at ? $sale->invoiced_at->format('d/m/Y H:i') : 'N/A' }}</p>
        @endif
        @if($sale->notes)
            <p><strong>Notas:</strong> {{ $sale->notes }}</p>
        @endif
    </div>
    
    <h3 style="margin-top: 20px;">Detalle de Productos</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Producto</th>
                <th style="width: 10%; text-align: center;">Cantidad</th>
                <th style="width: 15%; text-align: right;">Precio Unit.</th>
                <th style="width: 20%;">Lotes</th>
                <th style="width: 15%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">${{ number_format($item->price, 2) }}</td>
                    <td>
                        @if($item->lots->count() > 0)
                            @foreach($item->lots as $lot)
                                <div class="lot-info">
                                    <strong>{{ $lot->lot_number }}</strong> ({{ $lot->quantity }} und)
                                    <br>Venc: {{ \Carbon\Carbon::parse($lot->expiration_date)->format('d/m/Y') }}
                                </div>
                            @endforeach
                        @else
                            <span class="lot-info">Sin lotes asignados</span>
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: bold;">${{ number_format($item->quantity * $item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="totals">
        <table>
            <tr>
                <th>Subtotal:</th>
                <td style="text-align: right;">${{ number_format($sale->subtotal, 2) }}</td>
            </tr>
            <tr>
                <th>IVA:</th>
                <td style="text-align: right;">${{ number_format($sale->tax, 2) }}</td>
            </tr>
            <tr class="total-row">
                <th>TOTAL:</th>
                <td style="text-align: right;">${{ number_format($sale->total, 2) }}</td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Este es un documento digital válido</p>
    </div>
</body>
</html>

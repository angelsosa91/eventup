<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remisión {{ $remission->remission_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .company-info {
            width: 60%;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }
        .document-info {
            width: 40%;
            text-align: right;
        }
        .document-type {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        .document-number {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .document-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-draft { background-color: #6c757d; color: white; }
        .status-confirmed { background-color: #0d6efd; color: white; }
        .status-delivered { background-color: #0dcaf0; color: white; }
        .status-invoiced { background-color: #198754; color: white; }
        .status-cancelled { background-color: #dc3545; color: white; }

        .info-section {
            margin-bottom: 20px;
        }
        .info-section h3 {
            font-size: 13px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e9ecef;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 150px;
            font-weight: bold;
            padding: 4px 8px 4px 0;
            color: #495057;
        }
        .info-value {
            display: table-cell;
            padding: 4px 0;
            color: #333;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.items thead {
            background-color: #0d6efd;
            color: white;
        }
        table.items th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        table.items td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        table.items tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        .notes-section {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 12px;
            margin-top: 20px;
        }
        .notes-section h4 {
            font-size: 12px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 6px;
        }
        .notes-content {
            font-size: 11px;
            color: #495057;
            line-height: 1.5;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e9ecef;
            font-size: 10px;
            color: #6c757d;
            text-align: center;
        }

        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 48%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin: 60px 20px 5px 20px;
        }
        .signature-label {
            font-size: 11px;
            font-weight: bold;
            color: #495057;
        }

        @media print {
            .container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <table>
                <tr>
                    <td class="company-info">
                        <div class="company-name">{{ $tenant->name }}</div>
                        <div class="company-details">
                            <strong>RUC:</strong> {{ $tenant->tax_id }}<br>
                            <strong>Dirección:</strong> {{ $tenant->address }}<br>
                            <strong>Teléfono:</strong> {{ $tenant->phone }}<br>
                            <strong>Email:</strong> {{ $tenant->email }}
                        </div>
                    </td>
                    <td class="document-info">
                        <div class="document-type">REMISIÓN</div>
                        <div class="document-number">{{ $remission->remission_number }}</div>
                        <div class="document-status status-{{ $remission->status }}">
                            {{ $remission->status_text }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Información de la Remisión -->
        <div class="info-section">
            <h3>Información General</h3>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Fecha:</div>
                    <div class="info-value">{{ $remission->date->format('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Motivo:</div>
                    <div class="info-value">{{ $remission->reason_text }}</div>
                </div>
                @if($remission->delivery_address)
                <div class="info-row">
                    <div class="info-label">Dirección de Entrega:</div>
                    <div class="info-value">{{ $remission->delivery_address }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="info-section">
            <h3>Datos del Cliente</h3>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Cliente:</div>
                    <div class="info-value">{{ $remission->customer->name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">RUC/CI:</div>
                    <div class="info-value">{{ $remission->customer->tax_id }}</div>
                </div>
                @if($remission->customer->address)
                <div class="info-row">
                    <div class="info-label">Dirección:</div>
                    <div class="info-value">{{ $remission->customer->address }}</div>
                </div>
                @endif
                @if($remission->customer->phone)
                <div class="info-row">
                    <div class="info-label">Teléfono:</div>
                    <div class="info-value">{{ $remission->customer->phone }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Items -->
        <div class="info-section">
            <h3>Productos</h3>
            <table class="items">
                <thead>
                    <tr>
                        <th width="60" class="text-center">#</th>
                        <th>Producto</th>
                        <th width="100" class="text-center">Código</th>
                        <th width="100" class="text-right">Cantidad</th>
                        @if($remission->status === 'confirmed')
                        <th width="100" class="text-right">Reservado</th>
                        @endif
                        <th width="150">Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($remission->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-center">{{ $item->product->code }}</td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        @if($remission->status === 'confirmed')
                        <td class="text-right">{{ number_format($item->reserved_quantity, 2) }}</td>
                        @endif
                        <td>{{ $item->notes ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Notas -->
        @if($remission->notes)
        <div class="notes-section">
            <h4>Notas / Observaciones:</h4>
            <div class="notes-content">{{ $remission->notes }}</div>
        </div>
        @endif

        @if($remission->sale_id)
        <div class="notes-section">
            <h4>Información Adicional:</h4>
            <div class="notes-content">
                Esta remisión fue convertida a factura.
            </div>
        </div>
        @endif

        <!-- Firmas -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Entregado por</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Recibido por</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Este documento es una remisión y no tiene valor fiscal.<br>
                Generado el {{ now()->format('d/m/Y H:i:s') }} por {{ auth()->user()->name }}
            </p>
        </div>
    </div>
</body>
</html>

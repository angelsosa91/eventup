<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota de Crédito {{ $creditNote->credit_note_number }}</title>
    <style>
        @page {
            margin: 20px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #dc2626;
        }
        .company-info {
            float: left;
            width: 60%;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 11px;
            line-height: 1.5;
        }
        .document-info {
            float: right;
            width: 35%;
            text-align: right;
        }
        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 5px;
        }
        .document-number {
            font-size: 14px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 3px;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .alert-box {
            margin: 20px 0;
            padding: 10px;
            background-color: #fee2e2;
            border: 1px solid #dc2626;
        }
        .info-section {
            margin: 20px 0;
            padding: 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #dc2626;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table tbody tr:hover {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            float: right;
            width: 300px;
            margin-top: 10px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px;
        }
        .totals-table .label {
            text-align: right;
            font-weight: bold;
        }
        .totals-table .amount {
            text-align: right;
            width: 120px;
        }
        .total-row {
            border-top: 2px solid #dc2626;
            font-size: 14px;
            font-weight: bold;
            background-color: #fee2e2;
        }
        .footer {
            margin-top: 80px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="company-info">
            @php
                $companySettings = \App\Models\CompanySetting::where('tenant_id', $creditNote->tenant_id)->first();
            @endphp
            @if($companySettings && $companySettings->logo_path)
                <img src="{{ public_path('storage/' . $companySettings->logo_path) }}" alt="Logo" style="max-height: 60px; margin-bottom: 10px;">
            @endif
            <div class="company-name">{{ $companySettings->company_name ?? 'MI EMPRESA' }}</div>
            <div class="company-details">
                @if($companySettings && $companySettings->ruc)
                    <strong>RUC:</strong> {{ $companySettings->ruc }}<br>
                @endif
                @if($companySettings && $companySettings->address)
                    <strong>Dirección:</strong> {{ $companySettings->address }}<br>
                @endif
                @if($companySettings && $companySettings->phone)
                    <strong>Teléfono:</strong> {{ $companySettings->phone }}<br>
                @endif
                @if($companySettings && $companySettings->email)
                    <strong>Email:</strong> {{ $companySettings->email }}
                @endif
            </div>
        </div>
        <div class="document-info">
            <div class="document-title">NOTA DE CRÉDITO</div>
            <div class="document-number">{{ $creditNote->credit_note_number }}</div>
            <div><strong>Fecha:</strong> {{ $creditNote->date->format('d/m/Y') }}</div>
            <div><strong>Estado:</strong> {{ strtoupper($creditNote->status_text) }}</div>
        </div>
    </div>

    <div class="alert-box">
        <strong>⚠️ DOCUMENTO DE DEVOLUCIÓN/ANULACIÓN</strong><br>
        Este documento anula o modifica parcial/totalmente la factura de venta de referencia.
    </div>

    <div class="info-section">
        <div><span class="info-label">Venta Referencia:</span> {{ $creditNote->sale->sale_number }}</div>
        <div><span class="info-label">Cliente:</span> {{ $creditNote->customer->name }}</div>
        @if($creditNote->customer->tax_id)
            <div><span class="info-label">RUC/CI:</span> {{ $creditNote->customer->tax_id }}</div>
        @endif
        @if($creditNote->customer->address)
            <div><span class="info-label">Dirección:</span> {{ $creditNote->customer->address }}</div>
        @endif
        @if($creditNote->customer->phone)
            <div><span class="info-label">Teléfono:</span> {{ $creditNote->customer->phone }}</div>
        @endif
    </div>

    <div class="info-section">
        <div><span class="info-label">Motivo:</span> {{ $creditNote->reason_text }}</div>
        <div><span class="info-label">Tipo:</span> {{ $creditNote->type_text }}</div>
        <div><span class="info-label">Creado por:</span> {{ $creditNote->createdBy->name }}</div>
    </div>

    @if($creditNote->notes)
    <div class="info-section">
        <strong>Observaciones:</strong><br>
        {{ $creditNote->notes }}
    </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Producto</th>
                <th style="width: 80px;" class="text-center">Cantidad</th>
                <th style="width: 100px;" class="text-right">Precio Unit.</th>
                <th style="width: 60px;" class="text-center">IVA</th>
                <th style="width: 100px;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($creditNote->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->iva_type }}%</td>
                    <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals-section">
            <table class="totals-table">
                @if($creditNote->subtotal_0 > 0)
                    <tr>
                        <td class="label">Total Exento (0%):</td>
                        <td class="amount">{{ number_format($creditNote->subtotal_0, 0, ',', '.') }} Gs.</td>
                    </tr>
                @endif
                @if($creditNote->subtotal_5 > 0)
                    <tr>
                        <td class="label">Gravado 5%:</td>
                        <td class="amount">{{ number_format($creditNote->subtotal_5, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr>
                        <td class="label">IVA 5%:</td>
                        <td class="amount">{{ number_format($creditNote->iva_5, 0, ',', '.') }} Gs.</td>
                    </tr>
                @endif
                @if($creditNote->subtotal_10 > 0)
                    <tr>
                        <td class="label">Gravado 10%:</td>
                        <td class="amount">{{ number_format($creditNote->subtotal_10, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr>
                        <td class="label">IVA 10%:</td>
                        <td class="amount">{{ number_format($creditNote->iva_10, 0, ',', '.') }} Gs.</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td class="label">TOTAL A DEVOLVER:</td>
                    <td class="amount">{{ number_format($creditNote->total, 0, ',', '.') }} Gs.</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>{{ $companySettings->slogan ?? '' }}</p>
        <p>Nota de Crédito generada electrónicamente - {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        <p style="margin-top: 10px; font-size: 9px;">
            Este documento se emite para anular o corregir la venta {{ $creditNote->sale->sale_number }}.
            Los productos indicados han sido devueltos al inventario y se ha realizado el ajuste contable correspondiente.
        </p>
    </div>
</body>
</html>

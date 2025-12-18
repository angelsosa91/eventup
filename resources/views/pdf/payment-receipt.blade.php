<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo de Pago {{ $payment->payment_number }}</title>
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
            font-size: 13px;
            color: #333;
        }
        .header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
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
        .receipt-info {
            float: right;
            width: 35%;
            text-align: right;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #10b981;
        }
        .receipt-number {
            font-size: 14px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 3px;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .payment-details {
            margin: 20px 0;
            padding: 15px;
            background-color: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 5px;
        }
        .payment-row {
            padding: 8px 0;
            border-bottom: 1px solid #d1fae5;
        }
        .payment-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .amount-box {
            margin: 30px 0;
            padding: 20px;
            background-color: #dbeafe;
            border: 3px solid #2563eb;
            border-radius: 5px;
            text-align: center;
        }
        .amount-label {
            font-size: 14px;
            margin-bottom: 10px;
            color: #1e40af;
        }
        .amount-value {
            font-size: 28px;
            font-weight: bold;
            color: #1e3a8a;
        }
        .info-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .info-table th {
            background-color: #e5e7eb;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        .info-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .signature-section {
            margin-top: 80px;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            padding-top: 50px;
            border-top: 2px solid #333;
        }
        .signature-left {
            float: left;
        }
        .signature-right {
            float: right;
        }
        .footer {
            margin-top: 100px;
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
                    <strong>Teléfono:</strong> {{ $companySettings->phone }}
                @endif
            </div>
        </div>
        <div class="receipt-info">
            <div class="receipt-title">RECIBO DE PAGO</div>
            <div class="receipt-number">{{ $payment->payment_number }}</div>
            <div><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="payment-details">
        <div class="payment-row">
            <span class="label">Recibimos de:</span>
            {{ $payment->accountReceivable->sale->customer->name }}
        </div>
        <div class="payment-row">
            <span class="label">Documento:</span>
            {{ $payment->accountReceivable->sale->customer->tax_id ?? 'No especificado' }}
        </div>
        <div class="payment-row">
            <span class="label">Concepto:</span>
            Pago de Factura {{ $payment->accountReceivable->sale->sale_number }}
        </div>
        <div class="payment-row">
            <span class="label">Método de Pago:</span>
            {{ strtoupper($payment->payment_method) }}
            @if($payment->reference)
                - Ref: {{ $payment->reference }}
            @endif
        </div>
    </div>

    <div class="amount-box">
        <div class="amount-label">MONTO RECIBIDO</div>
        <div class="amount-value">{{ number_format($payment->amount, 0, ',', '.') }} Gs.</div>
    </div>

    <table class="info-table">
        <thead>
            <tr>
                <th>Detalle de la Cuenta por Cobrar</th>
                <th style="width: 120px; text-align: right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Factura N°: {{ $payment->accountReceivable->sale->sale_number }}</td>
                <td style="text-align: right;">{{ number_format($payment->accountReceivable->total_amount, 0, ',', '.') }} Gs.</td>
            </tr>
            <tr>
                <td>Total Pagado:</td>
                <td style="text-align: right;">{{ number_format($payment->accountReceivable->paid_amount, 0, ',', '.') }} Gs.</td>
            </tr>
            <tr>
                <td><strong>Saldo Restante:</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($payment->accountReceivable->balance_amount, 0, ',', '.') }} Gs.</strong></td>
            </tr>
        </tbody>
    </table>

    @if($payment->notes)
    <div style="margin: 20px 0; padding: 10px; background-color: #fef3c7; border-left: 4px solid #fbbf24;">
        <strong>Observaciones:</strong> {{ $payment->notes }}
    </div>
    @endif

    <div class="signature-section clearfix">
        <div class="signature-box signature-left">
            Recibí Conforme<br>
            {{ $payment->accountReceivable->sale->customer->name }}
        </div>
        <div class="signature-box signature-right">
            Entregado Por<br>
            {{ $companySettings->company_name ?? 'MI EMPRESA' }}
        </div>
    </div>

    <div class="footer">
        <p>{{ $companySettings->slogan ?? '' }}</p>
        <p>Recibo generado electrónicamente - {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>

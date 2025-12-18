<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante de Pago {{ $payment->payment_number }}</title>
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
        .voucher-info {
            float: right;
            width: 35%;
            text-align: right;
        }
        .voucher-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #dc2626;
        }
        .voucher-number {
            font-size: 14px;
            font-weight: bold;
            color: #b91c1c;
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
            background-color: #fef2f2;
            border: 2px solid #dc2626;
            border-radius: 5px;
        }
        .payment-row {
            padding: 8px 0;
            border-bottom: 1px solid #fecaca;
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
            background-color: #fee2e2;
            border: 3px solid #dc2626;
            border-radius: 5px;
            text-align: center;
        }
        .amount-label {
            font-size: 14px;
            margin-bottom: 10px;
            color: #991b1b;
        }
        .amount-value {
            font-size: 28px;
            font-weight: bold;
            color: #7f1d1d;
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
        <div class="voucher-info">
            <div class="voucher-title">COMPROBANTE DE PAGO</div>
            <div class="voucher-number">{{ $payment->payment_number }}</div>
            <div><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="payment-details">
        <div class="payment-row">
            <span class="label">Pagado a:</span>
            {{ $payment->accountPayable->purchase->supplier->name }}
        </div>
        <div class="payment-row">
            <span class="label">RUC:</span>
            {{ $payment->accountPayable->purchase->supplier->tax_id ?? 'No especificado' }}
        </div>
        <div class="payment-row">
            <span class="label">Concepto:</span>
            Pago de Compra {{ $payment->accountPayable->purchase->purchase_number }}
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
        <div class="amount-label">MONTO PAGADO</div>
        <div class="amount-value">{{ number_format($payment->amount, 0, ',', '.') }} Gs.</div>
    </div>

    <table class="info-table">
        <thead>
            <tr>
                <th>Detalle de la Cuenta por Pagar</th>
                <th style="width: 120px; text-align: right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Compra N°: {{ $payment->accountPayable->purchase->purchase_number }}</td>
                <td style="text-align: right;">{{ number_format($payment->accountPayable->total_amount, 0, ',', '.') }} Gs.</td>
            </tr>
            <tr>
                <td>Total Pagado:</td>
                <td style="text-align: right;">{{ number_format($payment->accountPayable->paid_amount, 0, ',', '.') }} Gs.</td>
            </tr>
            <tr>
                <td><strong>Saldo Restante:</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($payment->accountPayable->balance_amount, 0, ',', '.') }} Gs.</strong></td>
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
            Autorizado Por<br>
            {{ $companySettings->company_name ?? 'MI EMPRESA' }}
        </div>
        <div class="signature-box signature-right">
            Recibí Conforme<br>
            {{ $payment->accountPayable->purchase->supplier->name }}
        </div>
    </div>

    <div class="footer">
        <p>{{ $companySettings->slogan ?? '' }}</p>
        <p>Comprobante generado electrónicamente - {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>

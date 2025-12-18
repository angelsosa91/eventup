<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {{ $sale->sale_number }}</title>
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
        .invoice-info {
            float: right;
            width: 35%;
            text-align: right;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-number {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 3px;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .customer-section {
            margin: 20px 0;
            padding: 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .customer-label {
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background-color: #1f2937;
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
            border-top: 2px solid #333;
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            margin-top: 80px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        .payment-info {
            margin: 20px 0;
            padding: 10px;
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
        }
        .payment-label {
            font-weight: bold;
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
                    <strong>Teléfono:</strong> {{ $companySettings->phone }}<br>
                @endif
                @if($companySettings && $companySettings->email)
                    <strong>Email:</strong> {{ $companySettings->email }}
                @endif
            </div>
        </div>
        <div class="invoice-info">
            <div class="invoice-title">FACTURA</div>
            <div class="invoice-number">{{ $sale->sale_number }}</div>
            <div><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</div>
            <div><strong>Estado:</strong> {{ $sale->status === 'confirmed' ? 'CONFIRMADA' : 'PENDIENTE' }}</div>
        </div>
    </div>

    <div class="customer-section">
        <div><span class="customer-label">Cliente:</span> {{ $sale->customer->name }}</div>
        @if($sale->customer->tax_id)
            <div><span class="customer-label">RUC/CI:</span> {{ $sale->customer->tax_id }}</div>
        @endif
        @if($sale->customer->address)
            <div><span class="customer-label">Dirección:</span> {{ $sale->customer->address }}</div>
        @endif
        @if($sale->customer->phone)
            <div><span class="customer-label">Teléfono:</span> {{ $sale->customer->phone }}</div>
        @endif
    </div>

    @if($sale->payment_type === 'cash')
        <div class="payment-info">
            <span class="payment-label">Forma de Pago:</span> CONTADO - {{ strtoupper($sale->payment_method) }}
        </div>
    @else
        <div class="payment-info">
            <span class="payment-label">Forma de Pago:</span> CRÉDITO ({{ $sale->credit_days }} días)
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
            @foreach($sale->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->tax_rate }}%</td>
                    <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">{{ number_format($sale->subtotal, 0, ',', '.') }} Gs.</td>
                </tr>
                @if($sale->iva_5 > 0)
                    <tr>
                        <td class="label">IVA 5%:</td>
                        <td class="amount">{{ number_format($sale->iva_5, 0, ',', '.') }} Gs.</td>
                    </tr>
                @endif
                @if($sale->iva_10 > 0)
                    <tr>
                        <td class="label">IVA 10%:</td>
                        <td class="amount">{{ number_format($sale->iva_10, 0, ',', '.') }} Gs.</td>
                    </tr>
                @endif
                @if($sale->discount > 0)
                    <tr>
                        <td class="label">Descuento:</td>
                        <td class="amount">-{{ number_format($sale->discount, 0, ',', '.') }} Gs.</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td class="label">TOTAL:</td>
                    <td class="amount">{{ number_format($sale->total, 0, ',', '.') }} Gs.</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>{{ $companySettings->slogan ?? '' }}</p>
        <p>Factura generada electrónicamente - {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        @if($sale->notes)
            <p style="margin-top: 10px;"><strong>Observaciones:</strong> {{ $sale->notes }}</p>
        @endif
    </div>
</body>
</html>

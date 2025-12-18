<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Recibo de Aporte {{ $contribution->contribution_number }}</title>
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
            color: #2563eb;
        }

        .receipt-number {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .contribution-details {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8fafc;
            border: 2px solid #2563eb;
            border-radius: 5px;
        }

        .row {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .row:last-child {
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
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>

<body>
    <div class="header clearfix">
        <div class="company-info">
            @if($companySettings && $companySettings->logo_path)
                <img src="{{ public_path('storage/' . $companySettings->logo_path) }}" alt="Logo"
                    style="max-height: 60px; margin-bottom: 10px;">
            @endif
            <div class="company-name">{{ $companySettings->company_name ?? 'EVENTUP' }}</div>
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
            <div class="receipt-title">RECIBO DE APORTE</div>
            <div class="receipt-number">{{ $contribution->contribution_number }}</div>
            <div><strong>Fecha:</strong> {{ $contribution->contribution_date->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="contribution-details">
        <div class="row">
            <span class="label">Recibimos de:</span>
            {{ $contribution->customer->name }}
        </div>
        <div class="row">
            <span class="label">Documento:</span>
            {{ $contribution->customer->ruc ?? 'N/A' }}
        </div>
        @if($contribution->customer->grade)
            <div class="row">
                <span class="label">Curso/Grado:</span>
                {{ $contribution->customer->grade->name }}
            </div>
        @endif
        <div class="row">
            <span class="label">Concepto:</span>
            Aporte voluntario de alumno / cuota de aporte.
        </div>
        <div class="row">
            <span class="label">Método de Pago:</span>
            {{ strtoupper($contribution->payment_method) }}
            @if($contribution->reference)
                - Ref: {{ $contribution->reference }}
            @endif
        </div>
    </div>

    <div class="amount-box">
        <div class="amount-label">MONTO TOTAL DEL APORTE</div>
        <div class="amount-value">{{ number_format($contribution->amount, 0, ',', '.') }} Gs.</div>
        <div style="margin-top: 10px; font-style: italic; color: #475569;">
            {{-- Aquí se podría agregar la conversión a letras si se tiene el helper --}}
        </div>
    </div>

    @if($contribution->notes)
        <div style="margin: 20px 0; padding: 10px; background-color: #fff9db; border-left: 4px solid #fab005;">
            <strong>Observaciones:</strong> {{ $contribution->notes }}
        </div>
    @endif

    <div class="signature-section clearfix">
        <div class="signature-box signature-left">
            Firma del Alumno/Responsable<br>
            {{ $contribution->customer->name }}
        </div>
        <div class="signature-box signature-right">
            Recibido por (Admin)<br>
            {{ $companySettings->company_name ?? 'EVENTUP' }}
        </div>
    </div>

    <div class="footer">
        <p>{{ $companySettings->slogan ?? '' }}</p>
        <p>Documento generado por EventUP - {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>

</html>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuenta - {{ $customer->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }

        .header-left {
            display: table-cell;
            width: 70%;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: middle;
        }

        .company-logo {
            max-height: 60px;
            max-width: 150px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 16px;
            color: #34495e;
            margin-top: 10px;
        }

        .info-section {
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            color: #34495e;
        }

        .info-value {
            display: table-cell;
            width: 70%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #34495e;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 10px;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .positive-amount {
            color: #27ae60;
            font-weight: bold;
        }

        .negative-amount {
            color: #e74c3c;
            font-weight: bold;
        }

        .balance-col {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .summary-box {
            background-color: #34495e;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .summary-label {
            display: table-cell;
            width: 70%;
            font-size: 12px;
        }

        .summary-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-size: 12px;
            font-weight: bold;
        }

        .final-balance {
            font-size: 16px;
            border-top: 2px solid white;
            padding-top: 10px;
            margin-top: 10px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
            border-top: 1px solid #ecf0f1;
            padding-top: 10px;
        }

        .refund-badge {
            background-color: #e74c3c;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Encabezado -->
    <div class="header">
        <div class="header-left">
            @if($company_logo)
                <img src="{{ public_path('storage/' . $company_logo) }}" alt="Logo" class="company-logo">
            @endif
            <div class="company-name">{{ $company_name }}</div>
            <div class="document-title">ESTADO DE CUENTA DEL ALUMNO</div>
        </div>
        <div class="header-right">
            <div style="font-size: 10px; color: #7f8c8d;">Generado</div>
            <div style="font-weight: bold;">{{ $generated_date }}</div>
        </div>
    </div>

    <!-- Información del Alumno -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Alumno:</div>
            <div class="info-value">{{ $customer->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Documento:</div>
            <div class="info-value">{{ $customer->document_number ?? 'N/A' }}</div>
        </div>
        @if($customer->email)
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $customer->email }}</div>
            </div>
        @endif
        @if($customer->phone)
            <div class="info-row">
                <div class="info-label">Teléfono:</div>
                <div class="info-value">{{ $customer->phone }}</div>
            </div>
        @endif
    </div>

    <!-- Tabla de Movimientos -->
    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 15%;">Número</th>
                <th style="width: 28%;">Descripción</th>
                <th style="width: 13%;">Método</th>
                <th style="width: 16%;" class="text-right">Monto (Gs.)</th>
                <th style="width: 16%;" class="text-right">Saldo (Gs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement['date'] }}</td>
                    <td>{{ $movement['number'] }}</td>
                    <td>
                        {{ $movement['description'] }}
                        @if($movement['is_refund'])
                            <span class="refund-badge">DEVOLUCIÓN</span>
                        @endif
                        @if($movement['notes'])
                            <br><span style="font-size: 9px; color: #7f8c8d;">{{ $movement['notes'] }}</span>
                        @endif
                    </td>
                    <td>{{ $movement['payment_method'] }}</td>
                    <td class="text-right {{ $movement['amount'] < 0 ? 'negative-amount' : 'positive-amount' }}">
                        {{ number_format($movement['amount'], 0, ',', '.') }}
                    </td>
                    <td class="text-right balance-col">
                        {{ number_format($movement['balance'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #7f8c8d;">
                        No hay movimientos registrados para este alumno.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Resumen -->
    <div class="summary-box">
        <div class="summary-row">
            <div class="summary-label">Total Aportes:</div>
            <div class="summary-value positive-amount">{{ number_format($total_contributions, 0, ',', '.') }} Gs.</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Total Devoluciones:</div>
            <div class="summary-value negative-amount">{{ number_format($total_refunds, 0, ',', '.') }} Gs.</div>
        </div>
        <div class="summary-row final-balance">
            <div class="summary-label">SALDO FINAL:</div>
            <div class="summary-value">{{ number_format($final_balance, 0, ',', '.') }} Gs.</div>
        </div>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        Este documento fue generado electrónicamente y es válido sin firma ni sello.<br>
        {{ $company_name }} - Estado de Cuenta del Alumno
    </div>
</body>

</html>
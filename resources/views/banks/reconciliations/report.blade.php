<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Conciliación Bancaria - {{ $reconciliation->reconciliation_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .summary-table {
            width: 50%;
            margin: 20px 0;
            border-collapse: collapse;
            float: right;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f2f2f2;
        }
        .summary-table td:last-child {
            text-align: right;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            clear: both;
        }
        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .transactions-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .transactions-table td.number {
            text-align: right;
        }
        .transactions-table td.center {
            text-align: center;
        }
        .text-success {
            color: green;
        }
        .text-danger {
            color: red;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            border-radius: 3px;
        }
        .bg-success { background-color: #28a745; color: white; }
        .bg-danger { background-color: #dc3545; color: white; }
        .bg-info { background-color: #17a2b8; color: white; }
        .bg-warning { background-color: #ffc107; color: black; }
        .bg-primary { background-color: #007bff; color: white; }
        .bg-secondary { background-color: #6c757d; color: white; }
        .totals-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        @media print {
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">
            Imprimir Reporte
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px;">
            Cerrar
        </button>
    </div>

    <div class="header">
        <h1>REPORTE DE CONCILIACIÓN BANCARIA</h1>
        <h2>{{ $reconciliation->reconciliation_number }}</h2>
        <h2>{{ $reconciliation->bankAccount->bank_name }}</h2>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Cuenta Bancaria:</td>
                <td>{{ $reconciliation->bankAccount->account_name }} ({{ $reconciliation->bankAccount->account_number }})</td>
            </tr>
            <tr>
                <td>Fecha de Conciliación:</td>
                <td>{{ \Carbon\Carbon::parse($reconciliation->reconciliation_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Período:</td>
                <td>Del {{ \Carbon\Carbon::parse($reconciliation->statement_start_date)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($reconciliation->statement_end_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Estado:</td>
                <td>
                    @if($reconciliation->status === 'posted')
                        <span class="badge bg-success">Publicado</span>
                    @elseif($reconciliation->status === 'draft')
                        <span class="badge bg-secondary">Borrador</span>
                    @else
                        <span class="badge bg-danger">Cancelado</span>
                    @endif
                </td>
            </tr>
            @if($reconciliation->reconciledBy)
            <tr>
                <td>Conciliado por:</td>
                <td>{{ $reconciliation->reconciledBy->name }}</td>
            </tr>
            @endif
            @if($reconciliation->posted_at)
            <tr>
                <td>Fecha de Publicación:</td>
                <td>{{ \Carbon\Carbon::parse($reconciliation->posted_at)->format('d/m/Y H:i') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <table class="summary-table">
        <tr>
            <th>Concepto</th>
            <th>Monto</th>
        </tr>
        <tr>
            <td>Saldo Inicial (Estado de Cuenta)</td>
            <td>{{ number_format($reconciliation->opening_balance, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Saldo Final (Estado de Cuenta)</td>
            <td><strong>{{ number_format($reconciliation->closing_balance, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Saldo en Sistema</td>
            <td><strong>{{ number_format($reconciliation->system_balance, 0, ',', '.') }}</strong></td>
        </tr>
        <tr style="@if(abs($reconciliation->difference) < 1) background-color: #d4edda; @elseif(abs($reconciliation->difference) < 10000) background-color: #fff3cd; @else background-color: #f8d7da; @endif">
            <td><strong>Diferencia</strong></td>
            <td>
                <strong>
                    @if(abs($reconciliation->difference) < 1)
                        0 ✓
                    @else
                        {{ number_format($reconciliation->difference, 0, ',', '.') }}
                    @endif
                </strong>
            </td>
        </tr>
    </table>

    @if($reconciliation->notes)
    <div style="clear: both; margin: 20px 0; padding: 10px; background-color: #e7f3ff; border-left: 4px solid #2196F3;">
        <strong>Notas:</strong> {{ $reconciliation->notes }}
    </div>
    @endif

    <h3 style="margin-top: 30px;">Transacciones Conciliadas ({{ $reconciliation->lines->count() }})</h3>

    <table class="transactions-table">
        <thead>
            <tr>
                <th style="width: 8%;">Fecha</th>
                <th style="width: 12%;">Número</th>
                <th style="width: 10%;" class="center">Tipo</th>
                <th style="width: 30%;">Concepto</th>
                <th style="width: 12%;">Referencia</th>
                <th style="width: 14%;" class="number">Monto</th>
                <th style="width: 14%;" class="number">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDeposits = 0;
                $totalWithdrawals = 0;
            @endphp
            @foreach($reconciliation->lines as $line)
            @php
                $transaction = $line->bankTransaction;
                if (in_array($transaction->type, ['deposit', 'transfer_in', 'interest'])) {
                    $totalDeposits += $transaction->amount;
                } else {
                    $totalWithdrawals += $transaction->amount;
                }
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</td>
                <td>{{ $transaction->transaction_number }}</td>
                <td class="center">
                    @if($transaction->type === 'deposit')
                        <span class="badge bg-success">Depósito</span>
                    @elseif($transaction->type === 'withdrawal')
                        <span class="badge bg-danger">Retiro</span>
                    @elseif($transaction->type === 'transfer_in')
                        <span class="badge bg-info">Transf. In</span>
                    @elseif($transaction->type === 'transfer_out')
                        <span class="badge bg-warning">Transf. Out</span>
                    @elseif($transaction->type === 'check')
                        <span class="badge bg-primary">Cheque</span>
                    @elseif($transaction->type === 'charge')
                        <span class="badge bg-secondary">Cargo</span>
                    @else
                        <span class="badge bg-success">Interés</span>
                    @endif
                </td>
                <td>{{ $transaction->concept }}</td>
                <td>{{ $transaction->reference }}</td>
                <td class="number">
                    @if(in_array($transaction->type, ['deposit', 'transfer_in', 'interest']))
                        <span class="text-success">+{{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    @else
                        <span class="text-danger">-{{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    @endif
                </td>
                <td class="number">{{ number_format($transaction->balance_after, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="5" style="text-align: right;">Total Depósitos:</td>
                <td class="number text-success">+{{ number_format($totalDeposits, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr class="totals-row">
                <td colspan="5" style="text-align: right;">Total Retiros:</td>
                <td class="number text-danger">-{{ number_format($totalWithdrawals, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            <tr class="totals-row">
                <td colspan="5" style="text-align: right;">Diferencia Neta:</td>
                <td class="number"><strong>{{ number_format($totalDeposits - $totalWithdrawals, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 50px; page-break-inside: avoid;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: center; padding-top: 40px; border-top: 1px solid #000;">
                    Preparado por
                </td>
                <td style="width: 50%; text-align: center; padding-top: 40px; border-top: 1px solid #000;">
                    Revisado por
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 30px; font-size: 10px; color: #666; text-align: center;">
        Impreso el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>

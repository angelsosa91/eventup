<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Presupuesto - {{ $eventBudget->customer->name }}</title>
    <style>
        @page {
            margin: 30px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-info {
            float: left;
            width: 60%;
        }

        .budget-info {
            float: right;
            width: 35%;
            text-align: right;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .section-title {
            background: #f1f5f9;
            padding: 5px 10px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            border-left: 4px solid #2563eb;
        }

        .details-box {
            margin-bottom: 20px;
        }

        .details-row {
            margin-bottom: 5px;
        }

        .label {
            font-weight: bold;
            width: 120px;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
        }

        td {
            border: 1px solid #e2e8f0;
            padding: 8px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-box {
            margin-top: 20px;
            text-align: right;
            padding: 10px;
            background: #dbeafe;
            border: 2px solid #2563eb;
            border-radius: 5px;
        }

        .total-label {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
        }

        .total-value {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .guest-list {
            column-count: 2;
            column-gap: 40px;
        }

        .guest-item {
            margin-bottom: 3px;
            border-bottom: 1px dotted #ccc;
        }
    </style>
</head>

<body>
    <div class="header clearfix">
        <div class="company-info">
            <h1 style="margin:0; color:#1e3a8a;">{{ $companySettings->company_name ?? 'EVENTUP' }}</h1>
            <p>{{ $companySettings->address ?? '' }}</p>
            <p>Tel: {{ $companySettings->phone ?? '' }} | RUC: {{ $companySettings->ruc ?? '' }}</p>
        </div>
        <div class="budget-info">
            <div class="title">PRESUPUESTO FLIAR</div>
            <div><strong>Nro:</strong> {{ str_pad($eventBudget->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div><strong>Fecha:</strong> {{ $eventBudget->budget_date->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="details-box">
        <div class="section-title">Información del Evento y Familia</div>
        <div class="details-row"><span class="label">Evento:</span> {{ $eventBudget->event->name }}</div>
        <div class="details-row"><span class="label">Fecha Evento:</span>
            {{ $eventBudget->event->event_date->format('d/m/Y') }}</div>
        <div class="details-row"><span class="label">Familia:</span> {{ $eventBudget->family_name }}</div>
        <div class="details-row"><span class="label">Alumno:</span> {{ $eventBudget->customer->name }}</div>
    </div>

    <div class="section-title">Detalle de Costos</div>
    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="text-center" width="80">Cant.</th>
                <th class="text-right" width="120">Precio Unit.</th>
                <th class="text-right" width="120">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($eventBudget->items as $item)
                <tr>
                    <td>{{ $item->description }}<br><small style="color:#666">{{ $item->notes }}</small></td>
                    <td class="text-center">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>{{ number_format($item->total, 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <span class="total-label">TOTAL PRESUPUESTO:</span>
        <span class="total-value">{{ number_format($eventBudget->total_amount, 0, ',', '.') }} Gs.</span>
    </div>

    @if($eventBudget->guests->count() > 0)
        <div class="section-title">Lista de Invitados ({{ $eventBudget->guests->count() }})</div>
        <div class="guest-list">
            @foreach($eventBudget->guests as $guest)
                <div class="guest-item">
                    {{ $guest->name }} @if($guest->table) <span style="font-size:10px; color:#1e40af;">(Mesa:
                    {{ $guest->table->name }})</span> @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($eventBudget->notes)
        <div class="section-title">Notas / Observaciones</div>
        <p>{{ $eventBudget->notes }}</p>
    @endif

    <div class="footer">
        <p>Este presupuesto tiene carácter informativo y está sujeto a confirmación.</p>
        <p>Generado por EventUP el {{ date('d/m/Y H:i') }}</p>
    </div>
</body>

</html>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Distribución de Mesas - {{ $event->name }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .table-box {
            border: 1px solid #ccc;
            margin-bottom: 15px;
            padding: 10px;
            page-break-inside: avoid;
        }

        .table-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            background-color: #f0f0f0;
            padding: 5px;
        }

        .family-group {
            margin-left: 10px;
            margin-bottom: 5px;
        }

        .family-name {
            font-weight: bold;
            color: #555;
        }

        .guest-list {
            margin-left: 20px;
            color: #333;
        }

        .guest-item {
            margin-bottom: 2px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ $event->name }}</h2>
        <p>Fecha: {{ $event->event_date->format('d/m/Y') }} | Mesas: {{ $event->tables->count() }}</p>
    </div>

    @foreach($event->tables as $table)
        <div class="table-box" style="border-left: 5px solid {{ $table->color }};">
            <div class="table-title">
                {{ $table->name }} (Capacidad: {{ $table->capacity }})
            </div>

            @if($table->budgets->count() > 0)
                @foreach($table->budgets as $budget)
                    <div class="family-group">
                        <div class="family-name">- Familia: {{ $budget->family_name }} ({{ $budget->guests->count() }} invitados)
                        </div>
                        <div class="guest-list">
                            @foreach($budget->guests as $guest)
                                <div class="guest-item">• {{ $guest->name }} @if($guest->cedula) ({{ $guest->cedula }}) @endif</div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <p style="margin-left: 10px; color: #999;">Sin asignaciones</p>
            @endif
        </div>
    @endforeach
</body>

</html>
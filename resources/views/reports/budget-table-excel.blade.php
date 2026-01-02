<table>
    <thead>
        {{-- Fila 1: Nombre del Evento --}}
        <tr>
            <th colspan="{{ count($itemDescriptions) + 5 }}" 
                style="background-color: #d9edf7; font-weight: bold; border: 1px solid #000; text-align: center; height: 30px; font-size: 14pt;">
                EVENTO: {{ $event->name }} ({{ $event->event_date->format('d/m/Y') }})
            </th>
        </tr>
        {{-- Encabezados --}}
        <tr>
            <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000; width: 50px;">#</th>
            <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000; width: 100px;">Mesa</th>
            <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000; width: 80px;">Color</th>
            <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000; width: 250px;">Familia / Alumno</th>
            @foreach($itemDescriptions as $desc)
                <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000;">
                    {{ $desc }} <br>
                    <small>(Gs. {{ number_format($unitPrices->get($desc), 0, ',', '.') }})</small>
                </th>
            @endforeach
            <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000; width: 150px;">Monto Total</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $totals = array_fill_keys($itemDescriptions, 0);
            $grandTotalAmount = 0;
        @endphp
        @foreach($data as $index => $row)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000;">{{ $row['table_name'] }}</td>
                <td style="border: 1px solid #000; text-align: center; background-color: {{ $row['table_color'] }};">
                </td>
                <td style="border: 1px solid #000;">{{ $row['family_name'] }}</td>
                @foreach($itemDescriptions as $desc)
                    <td style="border: 1px solid #000; text-align: center;">
                        @php 
                            $qty = $row['items'][$desc] ?? 0;
                            $totals[$desc] += $qty;
                        @endphp
                        {{ $qty > 0 ? number_format($qty, 0, ',', '.') : '-' }}
                    </td>
                @endforeach
                <td style="border: 1px solid #000; text-align: right;">
                    {{ number_format($row['total_amount'], 0, ',', '.') }}
                    @php $grandTotalAmount += $row['total_amount']; @endphp
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <td colspan="4" style="border: 1px solid #000; text-align: right;">TOTALES:</td>
            @foreach($itemDescriptions as $desc)
                <td style="border: 1px solid #000; text-align: center;">
                    {{ number_format($totals[$desc], 0, ',', '.') }}
                </td>
            @endforeach
            <td style="border: 1px solid #000; text-align: right;">
                {{ number_format($grandTotalAmount, 0, ',', '.') }}
            </td>
        </tr>
    </tfoot>
</table>
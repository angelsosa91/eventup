<table>
    <thead>
        <tr>
            <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000;">Familia / Alumno</th>
            <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000;">Mesa</th>
            @foreach($itemDescriptions as $desc)
                <th style="background-color: #f2f2f2; font-weight: bold; border: 1px solid #000;">{{ $desc }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td style="border: 1px solid #000;">{{ $row['family_name'] }}</td>
                <td style="border: 1px solid #000;">{{ $row['table_name'] }}</td>
                @foreach($itemDescriptions as $desc)
                    <td style="border: 1px solid #000; text-align: center;">
                        {{ $row['items'][$desc] ?? 0 }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
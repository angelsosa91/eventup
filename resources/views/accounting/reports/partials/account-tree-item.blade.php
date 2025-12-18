<tr class="{{ $account['is_detail'] ? '' : 'fw-bold' }}">
    <td style="width: 100px;">{{ $account['code'] }}</td>
    <td style="padding-left: {{ $account['level'] * 20 }}px;">
        {{ $account['name'] }}
    </td>
    <td class="text-end" style="width: 150px;">
        @if($account['is_detail'] || !empty($account['children']))
            {{ number_format(abs($account['balance']), 0, ',', '.') }}
        @endif
    </td>
</tr>

@if(!empty($account['children']))
    @foreach($account['children'] as $child)
        @include('accounting.reports.partials.account-tree-item', ['account' => $child])
    @endforeach
@endif

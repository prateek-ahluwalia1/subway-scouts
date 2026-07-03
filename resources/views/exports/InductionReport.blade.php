<table>
    <thead>
        <tr>
            <th style="text-align:center; background-color: #808080; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 200px;">Induction Name</th>
            <th style="text-align:center; background-color: #808080; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 200px;">Guard Name</th>
            <th style="text-align:center; background-color: #808080; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 200px;">Date</th>
            <th style="text-align:center; background-color: #808080; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 200px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $guard)
            <tr>
                <td style="text-align:left;">{{ $guard['questionnaire_title'] ?? 'N/A' }}</td>
                <td style="text-align:left;">{{ $guard['name'] ?? 'N/A' }}</td>
                <td style="text-align:left;">{{ $guard['date'] ?? 'N/A' }}</td>
                <td style="text-align:left;">
                    @if(isset($guard['read_status']))
                        {{ $guard['read_status'] == 1 ? 'Completed' : 'Unread' }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
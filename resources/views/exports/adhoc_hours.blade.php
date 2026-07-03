<?php 
$permanentHoursSum = 0;
$adhocHoursSum = 0;
$groupedData = [];

foreach ($data as $item) {
    if ($item->shift_type == 'permanent') {
        $permanentHoursSum += $item->hours;
    } elseif ($item->shift_type == 'adhoc') {
        $adhocHoursSum += $item->hours;
    }

    $groupedData[$item->site_name][] = $item;
}
?>

<table>
    <thead>
        <tr>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Employee ID</th>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Start Date</th>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">End Date</th>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Name</th>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Status</th>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Start Time</th>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">End Time</th>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Client</th>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Site</th>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Adhoc Hours</th>
            <th style="text-align:center; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 20px;">Permanent Hours</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($groupedData as $siteName => $siteData)
            @foreach ($siteData as $key => $val)
                <tr>
                    <td style="text-align:center;border:1px solid #000000;">{{ $val->external_id }}</td>
                    <td style="text-align:center;border:1px solid #000000;">{{ $val->temp_date }}</td>
                    <td style="text-align:center;border:1px solid #000000;">{{ \Carbon\Carbon::parse($val->end)->format('Y-m-d') }}</td>
                    <td style="text-align:center;border:1px solid #000000;">{{ $val->guard_name }}</td>
                    <td style="text-align:center;border:1px solid #000000;">{{ $val->position }}</td>
                    <td style="text-align:center;border:1px solid #000000;">{{ \Carbon\Carbon::parse($val->start)->format('H:i:s') }}</td>
                    <td style="text-align:center;border:1px solid #000000;">{{ \Carbon\Carbon::parse($val->end)->format('H:i:s') }}</td>
                    <td style="text-align:center;border:1px solid #000000;">{{ $val->customer_name }}</td>
                    <td style="text-align:center;border:1px solid #000000;">{{ $val->site_name }}</td>
                    @if ($val->shift_type == 'adhoc')
                        <td style="text-align:center;border:1px solid #000000;">{{ $val->hours }}</td>
                        <td style="text-align:center;border:1px solid #000000;"></td>
                    @elseif ($val->shift_type == 'permanent')
                        <td style="text-align:center;border:1px solid #000000;"></td>
                        <td style="text-align:center;border:1px solid #000000;">{{ $val->hours }}</td>
                    @endif
                </tr>
            @endforeach
        @endforeach
        <tr>
            <td style="text-align:center;border:1px solid #000000;"><h2>Grand Sum</h2></td>
            <td style="text-align:center;border:1px solid #000000;"></td>
            <td style="text-align:center;border:1px solid #000000;"></td>
            <td style="text-align:center;border:1px solid #000000;"></td>
            <td style="text-align:center;border:1px solid #000000;"></td>
            <td style="text-align:center;border:1px solid #000000;"></td>
            <td style="text-align:center;border:1px solid #000000;"></td>
            <td style="text-align:center;border:1px solid #000000;"></td>
            <td style="text-align:center;border:1px solid #000000;"></td>
            <td style="text-align:center;border:1px solid #000000;"><h2>{{ $adhocHoursSum }}</h2></td>
            <td style="text-align:center;border:1px solid #000000;"><h2>{{ $permanentHoursSum }}</h2></td>
        </tr>
    </tbody>
</table>

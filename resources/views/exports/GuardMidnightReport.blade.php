<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guard Midnight Report</title>
</head>
<body>
    <table>
        <tr style="background-color:#01a37e; color: #fff;">
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 30px;">Sr#</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 30px;">Wilson Id</th>
            <!-- <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 30px;">Certis Id</th> -->
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 30px;">Guard Name</th>
            <!-- <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 30px;">Start</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 30px;">End</th> -->
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 30px;">Total Hours</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px; width: 30px;">Saturday Hours</th>
        </tr>
        @foreach ($data as $key => $guard)
                <tr>
                    <th style="text-align:center">{{ (int)$key + 1 }}</th>
                    <th style="text-align:center">{{ isset($guard['wilson']) ? $guard['wilson'] : 'N/A' }}</th>
                    <!-- <th style="text-align:center">{{ isset($guard['certis']) ? $guard['certis'] : 'N/A' }}</th> -->
                    <th style="text-align:center">{{ isset($guard['guard_name']) ? $guard['guard_name'] : 'N/A' }}</th>
                    <!-- <th style="text-align:center">{{ isset($guard->temp_start) ? $guard->temp_start : 'N/A' }}</th>
                    <th style="text-align:center">{{ isset($guard->temp_end) ? $guard->temp_end : 'N/A' }}</th> -->
                    <th style="text-align:center">{{ isset($guard['hours']) ? $guard['hours'] : '0' }}</th>
                    <th style="text-align:center">{{ round(is_object($guard) ? $guard->saturday_hours : $guard['saturday_hours'], 2) }}</th>
                </tr>
        @endforeach
    </table>
</body>
</html>

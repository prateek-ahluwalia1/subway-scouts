<!DOCTYPE html>
<html>
<head>
    <title>Guest Auth Report</title>
    
</head>
<body>
    <table style="border:1px solid #000000;width: 100%;">
        <thead>
            <tr>
                <th style="border:1px solid #000000;width: 20%;font-weight:bold">Guest Name</th>
                <th style="border:1px solid #000000;width: 20%;font-weight:bold">SignIn Time</th>
                <th style="border:1px solid #000000;width: 20%;font-weight:bold">SignIn Notes</th>
                <th style="border:1px solid #000000;width: 20%;font-weight:bold">SignOut Time</th>
                <th style="border:1px solid #000000;width: 20%;font-weight:bold">SignOut Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $reportdata)
            <tr>
                <td style="border:1px solid #000000;width: 20%;">{{ $reportdata->name }}</td>
                <td style="border:1px solid #000000;width: 20%;">{{ $reportdata->signin_time }}</td>
                <td style="border:1px solid #000000;width: 20%;">{{ $reportdata->signin_notes }}</td>
                <td style="border:1px solid #000000;width: 20%;">{{ $reportdata->signout_time }}</td>
                <td style="border:1px solid #000000;width: 20%;">{{ $reportdata->signout_notes }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

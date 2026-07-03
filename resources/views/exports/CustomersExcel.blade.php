<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers Excel</title>
</head>
<body>
    <table>
        <tr>
            <th style="height:80px; background-color:#FFFFFF;" colspan="30"></th>
        </tr>
        <tr style="background-color:#01a37e; color: #fff;">
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">#</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Email</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Phone</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Address</th>
        </tr>
        @foreach ($data as $key => $lead)
            <tr>
                <th style="text-align:center">{{$key+1}}</th>
                <th style="text-align:center">{{$lead->name}}</th>
                <th style="text-align:center">{{$lead->email}}</th>
                <th style="text-align:center">{{$lead->phone}}</th>
                <th style="text-align:center">{{$lead->address}}</th>
            </tr>
        @endforeach
    </table>
</body>
</html>
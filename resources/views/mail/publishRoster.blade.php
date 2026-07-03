<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
</head>
<body>
    <p>Hello {{ $name }},</p>

    <p>{!! $message_body !!}</p>

    <div style="padding-bottom: 30px">
        Please acknowledge the new shift by clicking 
        <a href="{{ $link }}" rel="noopener" target="_blank" style="text-decoration:none;color: #00B2FF">here</a>.
    </div>

    <div style="padding-bottom: 10px">
        Kind regards,<br>
        <span style="color:blue;">National Operation Center<br>1300 613 975</span><br><br>

        <span style="font-size:large;font-weight:bold;color:black;">
            AMG PTY LTD<br>NOC: 1300 613 975<br>M: 0487 966 9778<br>
            P.O Box 6155 Point Cook Vic 3030
        </span><br>

        <span style="font-size:small;font-weight:bold;">
            E: operations@amgsecurity.com.au
        </span><br>

        <span style="font-weight:bold;">
            W: <a href="https://www.amgsecurity.com.au">www.amgsecurity.com.au</a>
        </span><br><br><br>

        <img src="{{ $logo_url }}" style="width:100%;float:left;" alt="Email Footer">
    </div>
</body>
</html>

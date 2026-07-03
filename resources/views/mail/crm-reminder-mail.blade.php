<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>

<HEAD>
    <TITLE>Lead Reminder</TITLE>
    <META content="text/html; charset=utf-8" http-equiv=Content-Type>
</head>

<body style="font-size: 10pt; font-family: Arial, sans-serif;">

    <p>Hello {{$data->createdBy->name}}</p><br>

    <p>I hope you're all doing well. This is a friendly reminder that today, <b>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $data->date)->format('d-m-Y') }}</b>, you have a scheduled reminder from AMG Security CRM.<br>
    <b>Subject:</b> {{$data->subject}}<br>
    <b>Description:</b> {{$data->description}}
</p>
<br>
<b>Best regards,<br>
TheScouts</b>
</body>

</HTML>

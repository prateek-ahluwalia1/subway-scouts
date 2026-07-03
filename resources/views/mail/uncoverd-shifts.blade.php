<!DOCTYPE html>
    <html>
    <head>
        <title>AMG Security</title>

        <style>
            #customers {
                font-family: Arial, Helvetica, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            #customers td, #customers th {
                border: 1px solid #ddd;
                padding: 8px;
            }

            #customers tr:nth-child(even) {
                background-color: #f2f2f2;
            }

            #customers tr:hover {
                background-color: #ddd;
            }

            #customers th {
                padding-top: 12px;
                padding-bottom: 12px;
                text-align: left;
                background-color: #04AA6D;
                color: white;
            }
        </style>
    </head>

    <body>
    <table style="padding: 0; border: 0; width: 100%; background-color: #f2f8f9;" id="customers">
        <tr>
            
                <img style="width: 8rem; height: 6rem;"
                    src="https://app.247staffingsolutions.com.au/assets/images/logo/scouts.png" alt="AMG Security">
        </tr>
        <tr>
            <th>Location Name</th>
            <th>Location Description</th>
            <th>Start</th>
            <th>End</th>
            <th>Date</th>
        </tr>
        @foreach ($records as $value)
            <tr>
                <td>{{ !empty($value->site) ? $value->site->site_name : 'N/A' }}</td>
                <td>{{ !empty($value->site) ? $value->site->site_description : 'N/A' }}</td>
                <td>{{ !empty($value->start) ? usaToAusTime($value->start) : 'N/A' }}</td>
                <td>{{ !empty($value->end) ? usaToAusTime($value->end) : 'N/A' }}</td>
                <td>{{ !empty($value->start) ? usaToAus($value->start) : 'N/A' }}</td>
            </tr>
        @endforeach
    </table>
    </body>
    </html>

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
            <th>Staff Name</th>
            <th>Document Name</th>
            <th>Document Expiry</th>
        </tr>
        @foreach ($records as $value)
            <tr>
                <td>{{ !empty($value->first_name)  ? $value->first_name.' '.$value->last_name : 'N/A' }}</td>
                <td>{{ !empty($value->document_expire) ? usaToAus($value->document_expire) : 'N/A' }}</td>
                <td>{{ !empty($value->document_name) ? $value->document_name : 'N/A' }}</td>
            </tr>
        @endforeach
    </table>
    </body>
    </html>

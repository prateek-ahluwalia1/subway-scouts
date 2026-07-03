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
            <th>Name</th>
            <th>Guard Email</th>
            <th>Guard Status</th>
            <th>Staff Type</th>

        </tr>
        @foreach($records as $record)
            <tr>
                <td>{{ !empty($record->name)  ? $record->name : 'N/A' }}</td>
                <td>{{ !empty($record->email) ? $record->email : 'N/A' }}</td>
                <td>{{ !empty($record->guard_status) ? $record->guard_status : 'N/A' }}</td>
                <td>{{ !empty($record->staff_type) ? $record->staff_type : 'N/A' }}</td>

            </tr>
        @endforeach
    </table>
    </body>
    </html>

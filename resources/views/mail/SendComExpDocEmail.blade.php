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
            <th>Company Document Name</th>
            <th>Company Document Expiry</th>
        </tr>
            <tr>
                <td>{{ !empty($records->created_by)  ? $records->created_by : 'N/A' }}</td>
                <td>{{ !empty($records->name) ? $records->name : 'N/A' }}</td>
                <td>{{ !empty($records->expiry_date) ? usaToAus($records->expiry_date) : 'N/A' }}</td>
            </tr>
    </table>
    </body>
    </html>

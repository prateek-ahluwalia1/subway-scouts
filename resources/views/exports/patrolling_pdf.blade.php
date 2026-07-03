<!DOCTYPE html>
<html>

<head>
    <style>
        .bod {
            font-size: 14px;
            line-height: 1.42857143;
            color: #333;
            background-color: #fbfbfb;
        }

        .container-fluid {
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }

        .title {
            font-size: 25px;
            color: #000;
        }

        td {
            padding: 5px 0px;
        }

        strong {
            font-size: 16px;
        }

        th {
            text-align: left;
        }

        .bod-1 {
            border: 1px solid #000;
            text-align: left;

        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
        }

        /* Table header styles */
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        /* Table header specific styles */
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        /* Alternating row colors */
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        /* Hover effect for table rows */
        tr:hover {
            background-color: #f1f1f1;
        }

    </style>
</head>

<body class="bod">

    <div class="container-fluid">
        <div class="row">
            <table style="width:100%;">
                <tr>
                    <td>
                        <div>
                                <div style="text-align: center;" class="title">AMG
                                PATROLLING REPORT</div>
                        </div>
                    </td>
                </tr>
            </table>
            <table style="width:100%;">
                <tr>
                    <td>
                        <div>
                            <strong>Location of Patrolling:</strong> {{$data->sites->site_name}}
                        </div>
                    </td>
                    <td>
                        <!-- <div>
                            <strong>Location of Patrolling:</strong> {{$data->sites->site_description}}
                        </div> -->
                    </td>
                </tr>
            </table>
            <table style="width:100%;">
                <tr>
                    <td>
                        <div>
                            <strong>Name of Guard:</strong> {{$data->guards->first_name}} {{$data->guards->last_name}}
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong>Contact Details:</strong> {{$data->guards->phone}}
                        </div>
                    </td>
                    
                </tr>
            </table>
            <table style="width:100%;">
                <tr>
                    <td>
                        <div>
                            <strong>Job Start:</strong> {{date('d-m-Y H:i', strtotime($data->start))}}
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong>Job End:</strong> {{date('d-m-Y H:i', strtotime($data->end))}}
                        </div>
                    </td>
                </tr>
            </table>
            <h2>Patrolling Details</h2>
            <div>
                    <table>
                        <tr>
                            <th>Location</th>
                            <th>Scanner Name</th>
                            <th>Scan At</th>
                            <th>Status</th>
                        </tr>
                    @foreach ($patrolling_report as $key => $scans)
                        <tr>
                            <td>{{$scans->site_name}}</td>
                            <td>{{$scans->name}}</td>
                            <td>
                                @if($scans->scan_at)
                                    {{date('d-m-Y H:i', strtotime($scans->scan_at))}}
                                @else
                                    Missed
                                @endif
                            </td>
                            <td>{{$scans->status}}</td>

                        </tr>
                    @endforeach
                    </table>        
            </div>
        </div>
    </div>
</body>

</html>

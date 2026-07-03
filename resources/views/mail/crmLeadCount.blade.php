<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM</title>

    <style>
        .card {
            display: block;
            flex-wrap: wrap;
            align-items: stretch;
            width: 400px;
            flex-direction: column;
            border-radius: 0.25rem;
            padding: 1.5rem;
            margin: auto;
        }

        /* .header {
            display: flex;
            flex-direction: column;
        } */

        .title {
            font-size: 1.5rem;
            line-height: 2rem;
            font-weight: 700;
            color: #fff
        }

        .price {
            font-size: 3.75rem;
            line-height: 1;
            font-weight: 700;
            color: #fff
        }

        .desc {
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
            line-height: 1.625;
            color: rgb(208, 210, 212);
        }

        .lists {
            margin-bottom: 1.5rem;
            flex: 1 1 0%;
            color: rgb(208, 210, 212);

        }

        .lists .list {
            margin-bottom: 0.5rem;
            display: flex;
            margin-left: 0.5rem
        }

        .action {
            border: none;
            outline: none;
            display: inline-block;
            border-radius: 0.25rem;
            background-color: rgba(167, 139, 250, 1);
            padding-left: 1.25rem;
            padding-right: 1.25rem;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            text-align: center;
            font-weight: 600;
            letter-spacing: 0.05em;
            cursor: pointer;
            color: rgba(17, 24, 39, 1);
        }
    </style>
</head>

<body>
    <div class="card">
        <div>
            <span><h2>CRM User Lead Report</h2></span><br>
        </div>
        <p>The data below is of date {{$data['date']}}</p>
        <table style="text-align: center; border: 1px solid black; border-collapse: collapse;">
            <tr>
                <th style="border: 1px solid black;"><b>Name</b></th>
                <th style="border: 1px solid black;"><b>Created</b></th>
                <th style="border: 1px solid black;"><b>Won</b></th>
                <th style="border: 1px solid black;"><b>Loss</b></th>
                <th style="border: 1px solid black;"><b>Contacted</b></th>
                <th style="border: 1px solid black;"><b>Last Login</b></th>
                <th style="border: 1px solid black;"><b>Last Logout</b></th>
            </tr>
            @foreach($data['data'] as $item)
            <tr>
                <td style="border: 1px solid black;">{{$item->user_name}}</td>
                <td style="border: 1px solid black;">{{$item->created_count}}</td>
                <td style="border: 1px solid black;">{{$item->won_count}}</td>
                <td style="border: 1px solid black;">{{$item->lost_count}}</td>
                <td style="border: 1px solid black;">{{$item->contact_count}}</td>
                <td style="border: 1px solid black;">{{$item->last_login}}</td>
                <td style="border: 1px solid black;">{{$item->last_logout}}</td>
            </tr>
            @endforeach
        </table>

    </div>
</body>

</html>
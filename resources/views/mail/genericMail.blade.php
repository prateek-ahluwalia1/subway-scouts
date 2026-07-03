<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        .card {
            display: block;
            flex-wrap: wrap;
            align-items: stretch;
            width: 400px;
            flex-direction: column;
            border-radius: 0.25rem;
            background-color: rgba(17, 24, 39, 1);
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
        <div class="header">
            <span class="title">Assigned New</span><br><br><br>
            <span class="price">Lead</span>
        </div>
        <p class="desc">You've been assigned to a new lead. Please login to your account and check details.</p>
        <ul class="lists">
            <li class="list">
               <span><strong>Lead Name</strong>: </span>
                <span>{{$data->name}}</span>
            </li>
            <li class="list">
               <span><strong>Lead Client Name</strong>: </span>
                <span>{{$data->leaad_client_name}}</span>
            </li>
            <li class="list">
               <span><strong>Phone</strong>: </span>
                <span>{{$data->phone}}</span>
            </li>
            <li class="list">
                <span><strong>Email</strong>: </span>
                <span>{{$data->email}}</span>
            </li>
            <li class="list">
                <span><strong>Company</strong>: </span>
                <span>{{$data->company}}</span>
            </li>
            <li class="list">
                <span><strong>Sub Company</strong>: </span>
                <span>{{$data->sub_company}}</span>
            </li>
        </ul>
        <a href="https://app.thescouts.com.au/#/crm/customer-detail/{{$data->id}}?type=won" target="_blank" rel="noopener noreferrer"><button type="button"  class="action">Get Started</button></a>
    </div>
</body>

</html>
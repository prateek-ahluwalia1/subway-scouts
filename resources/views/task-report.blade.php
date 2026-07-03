<!DOCTYPE html>
<html>
<head>
<title>Task Report</title>
<style type="text/css">
    th{
        font-size: 13px;
    }
    @page {size: 595px 842px; margin:0!important; padding:0!important}
</style>
</head>
<body style="padding: 0px !important; margin:0px !important">
    <div class="bg" style="height: 100vh; position: relative;padding: 0px !important; margin:0px !important">
    <img style="width: 100%; height: auto;" src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('company_documents/AmgCoverImg.png'))) }}" alt="Report Cover">
    <div class="content" style="position: absolute; top: 15%; right: 1%; padding: 20px; color: black; text-align: center;">
        <p style="font-weight: bold; font-size: 45px; margin-bottom: 0;">Task Report</p>
    </div>
</div>
    <table class="table table-responsive table-hover table-striped  gy-5 gs-7" style="width:100%;">
        <thead>
            <tr>
                <td style="text-align: center;">
                    <!--<img height="100" src="{{'data:image/png;base64,'.base64_encode(file_get_contents(asset('logo.png')))}}"><br>-->
                <h3>Task Report</h3>
            </td>
            </tr>
            
        </thead>
    </table>
    <table class="table table-responsive table-hover table-striped" style="width:100%; margin: 10px !important;">
        <thead>
            <tr>
                <td style="float: left; text-align:left;"><b>Customer Name:</b> {{@$data[0]->name}}<br><b>Location Name:</b> {{@$data[0]->site_name}}<br><b>Staff Name:</b> {{@$data[0]->first_name .' '.@$data[0]->middle_name. ' '. @$data[0]->last_name}}</td>
                <td style="float: right; text-align:left;"><b>Shift Date:</b> {{date('d-m-Y', strtotime(@$data[0]->start))}}<br><b>Shift Start:</b> {{date('H:i', strtotime(@$data[0]->start))}}<br><b>Shift End:</b> {{date('H:i', strtotime(@$data[0]->end))}}<br></td>
            </tr>
        </thead>
    </table>
<table class="table table-responsive table-hover table-striped" style="width:100%;margin: 10px !important;padding-right:20px">
        <thead>
            <tr style="background-color:#01a37e; color: #fff;">

                    <th style="padding: 5px 10px;">#</th>
                    <th style="padding: 5px 10px;">Task Name</th>
                    <th style="padding: 5px 10px;">Assigned Task Time</th>
                    <th style="padding: 5px 10px;">Task Started At</th>
                    <th style="padding: 5px 10px;">Task Completed At</th>
                    <th style="padding: 5px 10px;">Task Status</th>
                    <th style="padding: 5px 10px;">Task Location</th>
            </tr>
        </thead>
                <tbody id="example2_body" style="text-align:center;">
                <?php
                foreach ($data as $key => $value) {
                ?>
                <tr>
                <td>{{ $key = $key + 1 }}</td>
                <td>{{ $value->task }}</td>
                <td>{{ date('H:i', strtotime($value->task_start)) }}</td>
                <td>{{ $value->start_time != null ? date('H:i', strtotime($value->start_time)) : 'N/A' }}</td>
                <td>{{ $value->end_time != null ?  date('H:i', strtotime($value->end_time)) : 'N/A' }}</td>
                <td>{{ $value->status }}</td>
                <td>{{($value->start_location != null ? coordinates_to_address($value->start_location) : 'N/A')}}</td> 
                   
                </tr>  
                <?php } ?>                                  
                </tbody>

       
    </table>
</body>
</html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
        .box {
            width: 22%;
            height: 100px;
            margin: 1%;
            color: black;
            font-weight: bold;
            line-height: 50px;
            box-sizing: border-box;
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }
        .inner-box {
            display: flex;
            flex-direction: column;
            height: 100%;
            justify-content: center;
        }
        .inner-box div {
            line-height: 50px;
        }
.table-class {
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
  }

  th {
    background-color: #ECECEC;
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    padding: 8px 4px 8px 4px;
    text-align: left;
    border: 1px solid #ddd;
  } 
tr {
    border-bottom: 1px solid #000;
}
tr:nth-child(odd) {
    background-color: #f2f2f2;
}
 td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
}
}
</style>
</head>
<body style="height: 100%;">
<div style="width: 100%; height: auto; text-align: right; padding-right: 20px;">
    <p style="font-weight: bold; font-size: 35px; margin-top: -600px; color: black;">Monthly Report</p>
</div>




        <div style="max-width: 800px; margin: 0 auto; background-color: #fff; padding: 40px; box-shadow: 0 6px 12px rgba(0,0,0,0.1); border-radius: 10px; border-left: 8px solid #29a2c3;">
            <h1 style="text-align: center; color: #333; margin-bottom: 20px; font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px;">Table of Contents</h1>
    
            <ul style="list-style-type: none; padding: 0;">
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Reporting by Month</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Graph of security incidents and operations of the selected month.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Security Incident Count Historical</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Graph of all security incidents and operations.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Incident Categories Historical Records</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Detailed records of various incident categories for the selected month.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Site Daily Shift Report</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Daily reports on shifts conducted at multiple sites.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Patrolling Report</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Records and summaries of patrolling activities.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Guard Leave Request</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Documentation and tracking of guard leave requests.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Guards</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Information about guards assigned to various posts.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Staff Injury</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Records of staff injuries and associated reports.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Near Misses/Hazards</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Documentation of near misses and identified hazards.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Monthly Leave Request</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Summary of manually adding monthly leave requests.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Point of Contact</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Summary of manually adding a point of contact.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
                <li style="margin-bottom: 2px;">
                    <h2 style="color: #29a2c3; font-size: 15px; margin-bottom: 5px; font-weight: bold;">Year To Date Summary</h2>
                    <p style="margin: 0; color: #555; font-size: 12px;">Summary of Previous 6 months record.</p>
                    <hr style="border: 0; border-top: 2px solid #e0e0e0; margin: 8px 0;">
                </li>
            </ul>
        </div>

<div style="border: 1px solid; margin-top:12px; padding: 0px 7px;">
   <h3 style="margin:0;">Executive Summary</h3>
   <p style="font-weight: bold;">{{ $report['month_year'] }} has focused on active reporting, service delivery and continuous uplift of our team's performance. Although staff have been encouraged to actively report incidents, no incidents were recorded this month, reflecting a stable and well-managed environment across all areas of operation.</p>
   <!-- <p style="font-weight: bold;">Our staff have been instructed to actively report incidents and the record of incidents has improved over the last two months 
      since the commencement of new reporting.</p> -->
   <!-- <p style="font-weight: bold;">We are currently seeing consistent issues with mainly external tenants.</p>
   <p style="font-weight: bold;">We continue to uplift and improve the service through innovation and sourcing of staff and training.</p>
   <p style="font-weight: bold;">We have new team members this month and continuously work on quality staff retention despite the sometimes volatile interactions with the public that our staff have to deal with.</p> -->
   <p style="font-weight: bold;">We remain committed to strengthening our service capability through ongoing development, clear operayional guidance, and a focus on consistent service quality. Our team continues to demonstrate professionalism and resilience, and we will maintain our efforts to support a positive and well-coordinated work environment.</p>
</div>

<div style="border: 1px solid; margin-top:12px; page-break-after: always;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Reporting By {{ $report['month_year'] }}</h2>

<div style="display: flex; text-align: center;">
<img src="{{'data:image/png;base64,'.base64_encode(file_get_contents($report['monthlyImage']))}}" alt="Cinque Terre" style="width: 75%; height: auto;">
</div>
</div>

<div style="border: 1px solid; margin-top:12px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Security Incident Count Historical</h2>

  <div style="display: flex; text-align: center; margin-bottom:10px;">
  <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents($report['allIncidentChart']))}}" alt="Cinque Terre" style="width: 75%; height: auto;">
  </div>
</div>

<div style="border: 1px solid; margin-top:12px; padding: 0px 7px; page-break-after: always;">
     <p style="font-weight: bold;">This month has been incident-free, reflecting the effectiveness of our service and the professionalism of our team. We remain focused on maintaining a safe and welcoming environment for the public and will continue to support our staff in delivering a high standard of service.</p>
   <!-- <p>At this time the highest reports are for Theft and Behavioural Issues and some additional assaults occurring than usual.</p>
   <p>We have had a drop in activity over the past month as per last month which is positive and we are satisfied that we have also not had an
      increase in the serious nature of incidents.</p>
   <p>We hope the volume of activity continues to drop and our staff continue to provide a safer public space for all to enjoy.</p> -->
</div>

<div style="border: 1px solid; margin-top:20px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Incident Categories Historical Records</h2>

  <div class="table-class">
  <table>
   <colgroup>
    <col style="width: 1%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 75%;">
  </colgroup>
  <tr>
    <th>Sr#</th>
    <th>Date/Time of Report</th>
    <th>I am reporting a (Report Type)</th>
    <th>Description of Incident (What happened)</th>
  </tr>
  @foreach($report['incident'] as $key => $incident)
  <tr>
    <td>{{ $key + 1 }}</td>
    <td>{{ $incident->incident_date . ' ' . $incident->incident_time }}</td>
    <td>{{ $incident->injury_type }}</td>
    <td>{{ $incident->injury_detail }}</td>
  </tr>
   @endforeach
</table>
  </div>
</div>
@php
    use Carbon\Carbon;
@endphp

<div style="border: 1px solid; margin-top:12px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Site Daily Shift Report</h2>
  <div class="table-class">
    <table style="width: 100%;">
   <colgroup>
    <col style="width: 1%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 25%;">
    <col style="width: 50%;">
  </colgroup>
  <tr>
    <th>Sr#</th>
    <th>Guard Name</th>
    <th>Date/Time of Report</th>
    <th>Site Name</th>
    <th>Description</th>
  </tr>
  @foreach($report['foot_petrol'] as $key => $foot_petrol)
  <tr>
    <td>{{ $key + 1 }}</td>
    <td>{{ $foot_petrol->guard_name }}</td>
    <td>
    {{ Carbon::parse($foot_petrol->start_date)->format('d-m-Y') }}
    ({{ Carbon::parse($foot_petrol->start_time)->format('H:i') . ' - ' . Carbon::parse($foot_petrol->finish_time)->format('H:i') }})
    </td>    
    <td>{{ $foot_petrol->site_name }}</td>
    <td>{{ $foot_petrol->summary }}</td>

  </tr>
  @endforeach
</table>
</div>
</div>

<div style="border: 1px solid; margin-top:12px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Patrolling Report</h2>
  <div class="table-class">
    <table style="width: 100%;">
   <colgroup>
    <col style="width: 1%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 25%;">
    <col style="width: 25%;">
    <col style="width: 25%;">
  </colgroup>
  <tr>
    <th>Sr#</th>
    <th>Guard Name</th>
    <th>Job Date/Time</th>
    <th>Site Name</th>
    <th>Status</th>
    <th>No of Scan</th>
  </tr>
  @foreach($report['patrolling_report'] as $key => $patrolling)
  <tr>
    <td>{{ $key + 1 }}</td>
    <td>{{ $patrolling->first_name . '' . $patrolling->last_name }}</td>
    <td>
    {{ Carbon::parse($patrolling->start_time)->format('d-m-Y') }}
    ({{ Carbon::parse($patrolling->start_time)->format('H:i') . ' - ' . Carbon::parse($patrolling->end_time)->format('H:i') }})
    </td>    
    <td>{{ $patrolling->site_name }}</td>
    <td>{{ $patrolling->status }}</td>
    <td>{{ $patrolling->scanner_count }}</td>
  </tr>
  @endforeach
</table>
</div>
</div>

<div style="border: 1px solid; margin-top:12px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Guard Leave Request</h2>
  <div class="table-class">
    <table style="width: 100%;">
   <colgroup>
    <col style="width: 1%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 25%;">
    <col style="width: 50%;">
  </colgroup>
  <tr>
    <th>Sr#</th>
    <th>Guard Name</th>
    <th>Start Date</th>
    <th>End Date</th>
    <th>Status</th>
  </tr>
  @foreach($report['guard_leave_request'] as $key => $guard_on_leave)
  <tr>
    <td>{{ $key + 1 }}</td>
    <td>{{ $guard_on_leave->first_name . '' . $guard_on_leave->last_name }}</td>
    <td>{{ Carbon::parse($guard_on_leave->start_date)->format('d-m-Y') }}</td> 
    <td>{{ Carbon::parse($guard_on_leave->end_date)->format('d-m-Y') }}</td>    
    <td>{{ $guard_on_leave->status }}</td>
  </tr>
  @endforeach
</table>
</div>
</div>

<div style="border: 1px solid; margin-top:12px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Guards</h2>
  <div class="table-class">
    <table style="width: 100%;">
   <colgroup>
    <col style="width: 1%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 12%;">    
    <col style="width: 25%;">
    <col style="width: 25%;">

  </colgroup>
 <tr>
  <th>Sr#</th>
  <th>Guard Name</th>
  <th>License Number</th>
  <th>License Expiry</th>
  <th>First Aid Expiry</th>
  <th>WWC Expiry</th>
  <th>Id</th>
  <!--<th>Wilson Id</th>-->
  <!--<th>Certis Id</th>-->
</tr>

@if(isset($report['guards']) && $report['guards'])
  @foreach($report['guards'] as $key => $guard)
  @php
    try {
        $licenseExp = Carbon::parse($guard['security_license_exp'])->format('d-m-Y');
    } catch(Exception $e) {
        $licenseExp = $guard['security_license_exp'] ?? 'N/A';
    }
@endphp
    <tr>
      <td>{{ $key + 1 }}</td>
      <td>{{ $guard['guard_name'] }}</td>
      <td>{{ $guard['security_license_no'] }}</td> 
      <td>{{ $licenseExp }}</td> working_with_children
      <td>{{ !empty($guard['first_aid_exp']) && $guard['first_aid_exp'] != 'N/A' ? Carbon::parse($guard['first_aid_exp'])->format('d-m-Y') : 'N/A' }}</td>  
      <td>{{ !empty($guard['working_with_children_exp']) && $guard['working_with_children_exp'] != 'N/A' ? Carbon::parse($guard['working_with_children_exp'])->format('d-m-Y') : 'N/A' }}</td>      

      @if(in_array($guard['customer_id'], [1, 2]))
       @if(!empty($guard['wilson']) && $guard['wilson'] != 'N/A')
        <td>Wilson ID {{ !empty($guard['wilson']) && $guard['wilson'] != 'N/A' ? $guard['wilson'] : 'N/A' }}</td>
       @endif
       @if(!empty($guard['certis']) && $guard['certis'] != 'N/A')
        <td>Certis ID {{ !empty($guard['certis']) && $guard['certis'] != 'N/A' ? $guard['certis'] : 'N/A' }}</td>
       @endif
      @else
        <td>N/A</td>
      @endif
    </tr>
  @endforeach
@endif

</table>
</div>
</div>

<div style="border: 1px solid; margin-top:12px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Staff Injury</h2>
  <div class="table-class">
    <table style="width: 100%;">
   <colgroup>
    <col style="width: 1%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 25%;">
    <col style="width: 13%;">
    <col style="width: 12%;">
    <col style="width: 25%;">
  </colgroup>
  <tr>
    <th>Sr#</th>
    <th>Date</th>
    <th>Staff Member</th>
    <th>injury type</th>
    <th>Days Lost</th>
    <th>Description of incident</th>
  </tr>
  @foreach($report['staff_injury'] as $key => $monthly_staff_injury)
  <tr>
    <td>{{ $key + 1 }}</td>
    <td>{{ Carbon::parse($monthly_staff_injury->date)->format('d-m-Y') }}</td> 
    <td>{{ $monthly_staff_injury->guard_name }}</td>
    <td>{{ $monthly_staff_injury->injury_type }}</td>
    <td>{{ $monthly_staff_injury->days_lost }}</td>
    <td>{{ $monthly_staff_injury->description }}</td>
  </tr>
  @endforeach
</table>
</div>
</div>

<div style="border: 1px solid; margin-top:12px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Near Misses/Hazards</h2>
  <div class="table-class">
    <table style="width: 100%;">
   <colgroup>
    <col style="width: 1%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 25%;">
    <col style="width: 25%;">
  </colgroup>
  <tr>
    <th>Sr#</th>
    <th>Date</th>
    <th>Staff Member</th>
    <th>Description of near miss</th>
    <th>Action taken</th>
  </tr>
  @foreach($report['near_misses'] as $key => $monthly_near_misses)
  <tr>
    <td>{{ $key + 1 }}</td>
    <td>{{ Carbon::parse($monthly_near_misses->date)->format('d-m-Y') }}</td> 
    <td>{{ $monthly_near_misses->guard_name }}</td>
    <td>{{ $monthly_near_misses->description }}</td>
    <td>{{ $monthly_near_misses->action_taken }}</td>
  </tr>
  @endforeach
</table>
</div>
</div>

<div style="border: 1px solid; margin-top:12px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Monthly Leave Request</h2>
  <div class="table-class">
    <table style="width: 100%;">
   <colgroup>
    <col style="width: 1%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 13%;">
    <col style="width: 25%;">
  </colgroup>
  <tr>
    <th>Sr#</th>
    <th>Site Name</th>
    <th>Guard Name</th>
    <th>Start</th>
    <th>End</th>
    <th>Reason</th>
  </tr>
  @foreach($report['monthly_leave'] as $key => $monthly_leave)
  <tr>
    <td>{{ $key + 1 }}</td>
    <td>{{ $monthly_leave->guard_name }}</td>
    <td>{{ $monthly_leave->site_name }}</td>
    <td>{{ Carbon::parse($monthly_leave->start)->format('d-m-Y') }}</td>
    <td>{{ Carbon::parse($monthly_leave->end)->format('d-m-Y') }}</td> 
    <td>{{ $monthly_leave->description }}</td>
  </tr>
  @endforeach
</table>
</div>
</div>

<div style="border: 1px solid; margin-top:12px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">Point Of Contact</h2>
  <div class="table-class">
    <table style="width: 100%;">
   <colgroup>
    <col style="width: 1%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 25%;">
    <col style="width: 25%;">
  </colgroup>
  <tr>
    <th>Sr#</th>
    <th>Site Name</th>
    <th>Name</th>
    <th>Phone</th>
    <th>Email</th>
  </tr>
  @foreach($report['point_contact'] as $key => $point_contact)
  <tr>
    <td>{{ $key + 1 }}</td>
    <td>{{ $point_contact->site_name }}</td>
    <td>{{ $point_contact->name }}</td>
    <td>{{ $point_contact->phone }}</td>
    <td>{{ $point_contact->email }}</td>
  </tr>
  @endforeach
</table>
</div>
</div>

<div style="border: 1px solid; margin-top:12px;">
  <h2 style="margin:0; background-color: #00ADEE; color:#fff; text-align:center;">{{ $report['month_year'] }} Summary</h2>
  <div class="table-class">
    <table style="width: 100%;">
   <colgroup>
    <col style="width: 1%;">
    <col style="width: 12%;">
    <col style="width: 12%;">
    <col style="width: 25%;">
    <col style="width: 25%;">
  </colgroup>
  <tr>
    <th>Sr#</th>
    <th>Month</th>
    <th>No. of Injury</th>
    <th>No. of Near Misses</th>
    <th>Lost Time</th>
  </tr>
@php
    $selectedMonthYear = $report['month_year'];
    $selectedSummary = collect($report['year_to_date'])->first(function($item) use ($selectedMonthYear) {
        // Extract just the month name from the full date string
        $monthName = \Carbon\Carbon::parse($item['month'])->format('F');
        return $monthName === $selectedMonthYear;
    });
@endphp

@php
    // Create dummy data if no match found
    if (!$selectedSummary) {
        $selectedSummary = [
            'month' => $selectedMonthYear,
            'injury_count' => 0,
            'near_miss_count' => 0,
            'days_lost_total' => 0
        ];
    }
@endphp

<tr>
    <td>1</td>
    <td>{{ $selectedSummary['month'] }}</td>
    <td>{{ $selectedSummary['injury_count'] }}</td>
    <td>{{ $selectedSummary['near_miss_count'] }}</td>
    <td>{{ $selectedSummary['days_lost_total'] }}</td>
</tr>
</table>
</div>
</div>
<br><br>
<div style="position: relative;">
  <div style="position: absolute; margin-left: 45%;"> <img alt="AMG Security" style="margin-bottom:20%;" src="" class="h-70px"></div>
</div>

</body>
</html>
<!DOCTYPE html>
<html>
<head>
<title>Incident Report</title>
<style type="text/css">
    @page {size: 595px 842px; margin:0!important; padding:0!important}
</style>
</head>
<body style="padding: 0px !important; margin:0px !important">
    <div class="bg" style="height: 100vh; position: relative;padding: 0px !important; margin:0px !important">
    <img style="width: 100%; height: auto;" src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('company_documents/AmgCoverImg.png'))) }}" alt="Report Cover">
    <div class="content" style="position: absolute; top: 15%; right: 1%; padding: 20px; color: black; text-align: center;">
        <p style="font-weight: bold; font-size: 45px; margin-bottom: 0;">Incident Report</p>
    </div>
</div>

            <!--Incident report pdf-->
            <div style="line-height:35px; padding-left: 3rem; padding-right: 3rem;" class="report">

                <div class="custom-row" style="background-color: #00a37e; margin-top: 5px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
                    <h4 style="color: #fff; text-align: center; padding-top: 5px;">Incident Report</h4>
                </div>

                <div style="margin: 10px 0px 20px 0px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
                    <!-- Left Column -->
                    <div style="flex: 1; padding: 0 10px;">
                        <div style="font-weight: bold;">Customer Name: <span style="font-weight: normal;">{{$report->customer_name}}</span></div>
                        <div style="font-weight: bold;">Location: <span style="font-weight: normal;">{{$report->site_name}}</span></div>
                        <div style="font-weight: bold;">Staff Name: <span style="font-weight: normal;">{{$report->guard_name}} {{$report->middle_name}} {{$report->last_name}}</span></div>
                        <div style="font-weight: bold;">Shift Timings: <span style="font-weight: normal;">{{usaToAusDateTime($report->start)}} - {{usaToAusDateTime($report->end)}}</span></div>
                    </div>
                
                    <!-- Right Column -->
                    <div style="flex: 1; padding: 0 10px;">
                    </div>
                </div>
                <hr>

                <table class="table-1" style="width: 100%;text-align:left">
                    <tr>
                        <td><strong>Incident Date</strong> </td>
                        <td><strong>Incident Time</strong> </td>
                        <td><strong>Incident type</strong> </td>
                    </tr>
                    <tr class="text" >
                        <td>{{$report->incident_date}}</td>
                        <td>{{$report->incident_time}}</td>
                        <td><button style="background-color: #DDF4EF; padding: 0px 10px; border-radius: 15px;">{{$report->injury_type}}</button></td>
                    </tr>
                </table>
                <p class="" style="margin-top: 0.5rem; margin-bottom: 0; font-weight: bold;">Incident details</p>
                <p class="" style="margin: 0;">{{$report->injury_detail}}</p>
                <hr>
                @if(!empty($report->people_involved))
                <table class="table-2" style="width: 100%;">
                    <p class="" style="margin-top: 0.5rem; margin-bottom: 0; font-weight: bold;">People Involved</p>
                    <tr style="font-weight: 630;">
                        <th style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">#</th>
                        <th style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Gender</th>
                        <th style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Name</th>
                        <th style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Phone</th>
                        <th style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Gmail</th>
                        <th style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Hair Color</th>
                                        <th style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Weight</th>
                                        <th style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Height</th>
                                        <th style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Mark</th>
                    </tr>
                    @foreach($report->people_involved as $key => $people_involved)
                    <tr class="text">
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;"><button style="background-color: #DDF4EF; padding: 0px 10px; border-radius: 15px;">{{$key+1}}</button></td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$people_involved['gender']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$people_involved['name']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$people_involved['phone']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$people_involved['email']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px; overflow-wrap: anywhere;">{{$people_involved['hair']}}</td>
                                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$people_involved['weight']}}</td>
                                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$people_involved['height']}}</td>
                                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px; overflow-wrap: break-word;">{{$people_involved['marks']}}</td>
                    </tr>
                    @endforeach

                </table><br>
                <hr>
                @endif
                @if(!empty($report->vehicle))
                @php
    $vehicles = json_decode($report->vehicle, true);
@endphp
                <table class="table-4" style="width: 100%;">
                    <p class="" style="margin-top: 0.5rem; margin-bottom: 0; font-weight: bold;">Vehicle Involved</p>
                    <tr style="font-weight: 630;">
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">No of Vehicle</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Rego Number</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Model</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Vehicle Type</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Make</td>
                    </tr>
            @foreach($vehicles as $key => $vehicle)

                    <tr class="text">
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$key+1}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$vehicle['vehicle_rander']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$vehicle['model']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{!empty($vehicle['vehicle_type'])? $vehicle['vehicle_type'] : ''}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$vehicle['make']}}</td>

                    </tr>
              @endforeach

                </table>
                <hr>
                @endif
                @if(!empty($report->emergency_services))
@php
$emergency_services = json_decode($report->emergency_services, true);

@endphp
                <table class="table-5" style="width: 100%;">
                    <p class="" style="width: 145%; margin-top: 0.5rem; margin-bottom: 0; font-weight: bold;">Emergency Service Involved</p>
                    <tr style="font-weight: 630;">
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Emergency Type</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Supervisor</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Position</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Phone</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Gmail</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Address</td>

                    </tr>
                    <tr class="text">
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;"><button style="background-color: #DDF4EF; padding: 0px 10px; border-radius: 15px;">{{$emergency_services['emergency_type']}}</button></td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$emergency_services['supervisor_name']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$emergency_services['position']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$emergency_services['phone']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$emergency_services['email']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$emergency_services['address']}}</td>

                    </tr>
                </table>
                <hr>
                @endif
@if(!empty($report->wittness))
@php
$wittness = json_decode($report->wittness, true);

@endphp
                <table class="table-6" style="width: 100%;">
                    <p class="" style="margin-top: 0.5rem; margin-bottom: 0; font-weight: bold;">Witness Involved</p>
                    <tr style="font-weight: 630;">
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">No of Witness</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Full Name</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Phone</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">Address</td>
                    </tr>
            @foreach($wittness as $key => $wittnes)

                    <tr class="text">
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$key+1}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$wittnes['wittness_name']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$wittnes['wittness_phone']}}</td>
                        <td style="border: 1px solid rgb(68, 64, 64); border-collapse: collapse; padding-left: 5px;">{{$wittnes['wittness_address']}}</td>

                    </tr>
              @endforeach

                </table>
                <hr>
                @endif
@if(!empty($report->photo != ''))
@php
$images = json_decode($report->photo , true);
@endphp
                <p class="" style="margin-top: 0.5rem; margin-bottom: 0; font-weight: bold;">Pictures</p>
                      @if (is_array($images))
                      <table style="width:100%">
                        <tr>
                        @foreach ($images as $image)
                            @php
                                $imageUrl = 'https://appapi.subway.thescouts.com.au/public/uploads/' . $image['imgPath'];

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $imageUrl);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $imageData = curl_exec($ch);
                                curl_close($ch);

                                $base64 = base64_encode($imageData);
                            @endphp

                            <td>
                                <img src="data:image/png;base64,{{ $base64 }}" style="width: 25%; height: 13%;">
                                <div>{{ $image['timestamp'] }}</div>
                            </td>
                        @endforeach
                        </tr>
                    </table>
                    @else
                    <div style="height: 200px; overflow: hidden;" class="rounded border">
                        <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents(public_path('incident/'.$report->image['imgPath'])))}}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    @endif
                @endif
                <hr>
                
                <p>By Signing below you confirm and acknowledge that the report being submitted is a true and accurate record of the incident that occurred on the date in question.</p>
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;">
                    <div>
                        <p class="" style="font-weight: bold;">Signature</p>
                        @if($report->signature != '')
                        @php
                            $signatureUrl = 'https://appapi.subway.thescouts.com.au/public/uploads/' . $report->signature;

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $signatureUrl);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $signatureData = curl_exec($ch);
                            curl_close($ch);

                            $signatureBase64 = base64_encode($signatureData);
                        @endphp
                        <img src="data:image/png;base64,{{ $signatureBase64 }}" style="height: 100px; width: 100px;">                         @endif
                    </div>
                </div>

            </div>
</body>
</html>



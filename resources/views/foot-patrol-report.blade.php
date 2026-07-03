<!DOCTYPE html>
<html>

<head>
    <title>{{$report->customer_name}} Daily Shift Report</title>
    <style type="text/css">
        @page {size: 595px 842px; margin:0!important; padding:0!important}
    </style>
</head>

<body style="padding: 0px !important; margin:0px !important">
      <div class="bg" style="height: 100vh; position: relative;padding: 0px !important; margin:0px !important">
    <img style="width: 100%; height: auto;" src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('company_documents/AmgCoverImg.png'))) }}" alt="Report Cover">
    <div class="content" style="position: absolute; top: 15%; right: 1%; padding: 20px; color: black; text-align: center;">
        <p style="font-weight: bold; font-size: 45px; margin-bottom: 0;">Daily Shift<br>Report</p>
    </div>
</div>
    
    <div style="line-height:35px; padding-left: 3rem; padding-right: 3rem;" class="report">
        <div class="custom-row" style="margin-top: 5px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap; background-color:#01ACED; color: #fff;">
            <h3 style="text-align: center; padding-top: 3px;">{{$report->customer_name}} Daily Shift Report</h3>
        </div>

        <div class="table-1" style="width: 100%; text-align: left;">
            <div style="display: table; width: 100%;">
                <div style="display: table-row;">
                    <div style="display: table-cell; width: 33.33%;"><strong>Date of Report</strong></div>
                    <div style="display: table-cell; width: 33.33%;"><strong>Time of Report</strong></div>
                    <div style="display: table-cell; width: 33.33%;"><strong>Site Name</strong></div>
                </div>
                <div style="display: table-row;">
                    <div style="display: table-cell; width: 33.33%;">{{usaToAus($report->created_at)}}</div>
                    <div style="display: table-cell; width: 33.33%;">{{usaToAusTime($report->created_at)}}</div>
                    <div style="display: table-cell; width: 33.33%;">{{$report->site_name}}</div>
                </div>
            </div>
        </div>

        <div class="table-1" style="width: 100%; text-align: left;">
            <div style="display: table; width: 100%;">
                <div style="display: table-row;">
                    <div style="display: table-cell; width: 33.33%;"><strong>Name of Guard</strong></div>
                    <div style="display: table-cell; width: 33.33%;"><strong>Job Start</strong></div>
                    <div style="display: table-cell; width: 33.33%;"><strong>Job End</strong></div>
                </div>
                <div style="display: table-row;">
                    <div style="display: table-cell; width: 33.33%;">{{$report->guard_name}} {{$report->middle_name}} {{$report->last_name}}</div>
                    <div style="display: table-cell; width: 33.33%;">{{usaToAusDateTime($report->start)}}</div>
                    <div style="display: table-cell; width: 33.33%;">{{usaToAusDateTime($report->end)}}</div>
                </div>
            </div>
        </div>

        <div class="table-1" style="width: 100%; text-align: left;">
            <div style="display: table; width: 100%;">
                <div style="display: table-row;">
                    <div style="display: table-cell; width: 100%;"><strong>Contact Details</strong></div>
                </div>
                <div style="display: table-row;">
                    <div style="display: table-cell; width: 100%;">{{$report->phone}}</div>
                </div>
            </div>
        </div>
       
        <p style="margin-top: 0.5rem; margin-bottom: 0; font-weight: bold;">Details</p>
        <p style="margin: 0;">{{$report->patrolling_detail}}</p>
        <hr>

          <h6 style="text-align: center;">WORK HEALTH AND SAFETY CHECK</h6>
        <p>As you are compleing your shift at this time,AMG is reminding all employees about the importance of
            safety not only for themselves but his responsibiliy to others.The below question is for the purposes of
            determining your safety when completing your shift and ensuring you are safe o ravel home.</p>
        <p>If any employee is no fit to travel home via any means of transport or mehod of travel due to fatigue or
            tiredness they are o advise an AMG Securiy Manager immediately before leaving their shift.</p>

        @if(!empty($report->photo))
            @php
                $images = json_decode($report->photo , true);
            @endphp

            <p style="margin-bottom: 0; font-weight: bold;">Pictures</p><br>

            @if (is_array($images))
                <table style="width:100%">
                    <tr>
                        @foreach ($images as $key => $value)
                                  @php
                                $imageUrl = 'https://app-apis.amgsystem.com.au/uploads/' . $value['imgPath'];

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $imageUrl);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $imageData = curl_exec($ch);
                                curl_close($ch);

                                $base64 = base64_encode($imageData);
                            @endphp
                            <td>
                                <img src="data:image/png;base64,{{ $base64 }}" alt="Image" style="width: 75%; height: auto;">
                                <span>{{ $value['timestamp'] }}</span>
                            </td>
                        @endforeach
                    </tr>
                </table>
            @else
                <div style="height: 200px; overflow: hidden;" class="rounded border">
                    <img src="https://app-apis.amgsystem.com.au/uploads/{{$report->image}}" alt="" style="width: 98%; height: 100%; object-fit: cover;">
                </div>
            @endif
        @endif

        <div><hr></div>

        <div style="justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;">
            <div>
                <p style="font-weight: bold;">By Signing below you confirm and acknowledge that the report being submitted is a
            true and accurate record of the incident that occurred on the date in question.</p>
                <p style="font-weight: bold;">Signature</p>
                @if(!empty($report->signature))
                        @php
                            $signatureUrl = 'https://app-apis.amgsystem.com.au/uploads/' . $report->signature;

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $signatureUrl);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $signatureData = curl_exec($ch);
                            curl_close($ch);

                            $signatureBase64 = base64_encode($signatureData);
                        @endphp
                    <img src="data:image/png;base64,{{ $signatureBase64 }}" style="height:100px; width:100px" />
                @endif
            </div>
        </div>
    </div>

</body>

</html>

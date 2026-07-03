<!DOCTYPE html>
<html>
<head>
<title>Audit Report</title>
<style type="text/css">
    th{
        font-size: 13px;
    }
    @page {size: 595px 842px; margin:0!important; padding:0!important}
</style>
</head>
<body style="padding: 0px !important; margin:0px !important">
    <div class="bg" style="height: 100vh; position: relative;padding: 0px !important; margin:0px !important">
    <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents(asset('Final-cover-Image-new.png')))}}" alt="Report Cover" style="width: 100%; height: 100%;">
    <div class="content" style="position: absolute; top: 15%; right: 1%; padding: 20px; color: black; text-align: center;">
        <p style="font-weight: bold; font-size: 45px; margin-bottom: 0;">Audit Report</p>
    </div>
</div>
<div class="padding" style="padding: 10px;">
    <!--<h2 style="text-align: center;">Audit Form Report</h2>-->
    <!--<div class="site"
        style="background-color: black; text-align: center; border-radius: 10px; height: 40px; padding-top: 5px;">
        <h2 class="siteDetails" style="color: white; margin-top: 10px;">Audit Form Report</h2>
    </div>-->

    <div class="about-section" style="padding: 50px; text-align: center; background-color: #00A37E; color: white;">
        <h1>Audit Report</h1>
    </div>

    <div style="text-align: right; margin-top: -10px;">
        <p><b>Date:</b> {{usaToAusDateTime($data->created_at)}}</p>
    </div>
    <!--<table style="width: 100%;">
        <tr>
            <td style="width: 50%; text-align: left;">
                <h4 style="margin: 0;">Site Name:</h4>
            </td>
            <td style="width: 50%; text-align: right;">
                <p style="margin: 0;">{{$data->site_name}}</p>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; text-align: left;">
                <h4 style="margin: 0;">Audit By:</h4>
            </td>
            <td style="width: 50%; text-align: right;">
                <p style="margin: 0;">{{$data->audit_by}}</p>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; text-align: left;">
                <h4 style="margin: 0;">Name of Guards:</h4>
            </td>
            <td style="width: 50%; text-align: right;">
                <p style="margin: 0;">{{$data->guard_name}}</p>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; text-align: left;">
                <h4 style="margin: 0;">Contact Details:</h4>
            </td>
            <td style="width: 50%; text-align: right;">
                <p style="margin: 0;">{{$data->guard_phone}}</p>
            </td>
        </tr>
    </table>-->


       <table class="table table-responsive table-hover table-striped  gy-5 gs-7" style="width:100%; margin: 10px !important;">
        <thead>
            <tr>
                <td style="float: left; text-align:left;"><b>Site Name:</b> {{$data->site_name}}<br><b>Name of Guards:</b> {{$data->guard_name}}<br><b>License No.:</b> {{$data->guard_security_license}}</td>
                <td style="float: right; text-align:left;"><b>Audit By:</b> {{$data->audit_by}}<br><b>Contact Details:</b> {{$data->guard_phone}}<br><b>License Exp Date:</b> {{$data->guard_license_expiry}}</td>
            </tr>
        </thead>
    </table>


    <div>
        <div>
            <table class="table-row" style="width: 100%; border-collapse: collapse; padding-right:20px;">
                <tr class="row-height" style="height: 40px;background-color: #00A37E; ">
                    <th class="header-cell-left" style="text-align: left; color: white;padding-left:10px;">Questions
                    </th>
                    <th class="header-cell-right" style="color: white; margin-right: 20px;">Status</th>
                    <th class="header-cell-right" style="color: white; margin-right: 20px;">Image</th>
                </tr>
                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff is on-site?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{$data->on_site_text ?? 'N/A'}}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black">
                        <span class="text" style="display: block; margin-bottom: 10px;">{{$data->have_on_site}}</span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black">
                        @if($data->on_site_image)
                        <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->on_site_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>
                </tr>
                <tr class="row-width" style="background-color: white">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has Signed-in on time on the app?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->on_time_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text" style="display: block; margin-bottom: 10px;">{{ $data->on_time }}</span>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->on_time_image)
                        <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->on_time_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>
                </tr>
                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border"
                        style="position: relative; border: 1px solid black; padding-left: 10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Does the guard have proper uniform?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->uniform_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text" style="display: block; margin-bottom: 10px;">{{ $data->have_uniform }}</span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->uniform_image)
                        <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents($data->uniform_image))}}" class="img-style"
                            style="width: 100px; height: 100px;">
                        @endif
                    </td>
                </tr>
                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff is well groomed (according to job
                            requirement)?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->well_groomed_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text" style="display: block; margin-bottom: 10px;">{{ $data->have_well_groomed }} </span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->well_groomed_image)
                        <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->well_groomed_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>
                </tr>

                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has access to Site equipment?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->site_eqipment_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text"
                            style="display: block; margin-bottom: 10px;">{{ $data->have_site_eqipment }}</span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black;">
                          @if($data->site_eqipment_image)
                            <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->site_eqipment_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                          @endif
                    </td>

                </tr>

                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has access to stationary items?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->notebook_pen_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text"
                            style="display: block; margin-bottom: 10px;">{{ $data->have_notebook_pen }}</span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->notebook_pen_image)
                            <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->notebook_pen_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>

                </tr>

                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has their security license on them?
                        </h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->license_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text" style="display: block; margin-bottom: 10px;">{{ $data->have_license }}</span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->license_image)
                        <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->license_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>
                </tr>

                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has their Induction card on them?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->induction_card_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text" style="display: block; margin-bottom: 10px;">{{ $data->have_induction_card }}</span>
                    </td>

                   <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->induction_card_image)
                        <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->induction_card_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>

                </tr>
                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">

                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has Working with Children Check on
                            them?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->children_check_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text"
                            style="display: block; margin-bottom: 10px;">{{ $data->have_children_check }}</span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->children_check_image)
                            <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->children_check_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>
                </tr>





                <tr class="row-width" style="background-color: white">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">

                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has White Card on them?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->white_card_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text"
                            style="display: block; margin-bottom: 10px;">{{ $data->have_white_card }}</span>
                    </td>

                   <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->white_card_image)
                            <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->white_card_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>
                </tr>




                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has RSA certificate on them?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->rsa_certificate_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text"
                            style="display: block; margin-bottom: 10px;">{{ $data->have_rsa_certificate }}</span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->rsa_certificate_image)
                            <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->rsa_certificate_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>
                </tr>




                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">

                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has done regular Patrols of assigned
                            areas?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->assigned_petrol_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text"
                            style="display: block; margin-bottom: 10px;">{{ $data->have_assigned_petrol }}</span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->assigned_petrol_image)
                            <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->assigned_petrol_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>
                </tr>




                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has knowledge of Site,i.e, doors,
                            windows, all entry points?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->site_knowledge_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black">
                        <span class="text"
                            style="display: block; margin-bottom: 10px;">{{ $data->have_site_knowledge }}</span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black">
                        @if($data->site_knowledge_image)
                            <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->site_knowledge_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>
                </tr>




                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has knowledge of First aid procedures
                            and knows the whereabouts of the first aid kit.?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->firstaid_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid ">
                        <span class="text"
                            style="display: block; margin-bottom: 10px;">{{ $data->have_firstaid }}</span>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid ">
                        @if($data->firstaid_image)
                            <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->firstaid_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>

                </tr>




                <tr class="row-width" style="background-color: white;">
                    <td class="no-center-border" style="position: relative; border: 1px solid black;padding-left:10px;">
                        <h4 style="text-align: left; margin-bottom: 10px;">Staff has the Know-how of Emergency protocols
                            ,i.e , Police, Fire brigade, Ambulancet?</h4>
                        <strong>
                            <p class="left-align" style="margin: 0;">Comments</p>
                        </strong>
                        <p style="margin-top: 0; margin-bottom: 0;">{{ $data->emergency_protocol_text ?? 'N/A' }}</p>
                    </td>
                    <td class="img" style="text-align: center; border: 1px solid black;">
                        <span class="text"
                            style="display: block; margin-bottom: 10px;">{{ $data->have_emergency_protocol }} </span>
                    </td>
                    </td>

                    <td class="img" style="text-align: center; border: 1px solid black;">
                        @if($data->emergency_protocol_image)
                            <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->emergency_protocol_image)) }}" class="img-style"
                            style="width: 100px; height: 100px">
                            @endif
                    </td>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <table style="width: 100%;">
        <tr>
            <td style="padding: 10px;">
                <h4 style="text-align: left; margin: 0;font-size: 19px;">Audit Notes</h4>
                <p style="margin: 10px 0px;">{{ $data->notes ?? 'N/A' }}</p>
            </td>


        </tr>
        <tr>

          <td style="padding: 10px;">
                <h4 style="text-align: left; margin: 0;font-size: 19px;">Signature</h4>
                <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents($data->signature)) }}" class="img-style"
                    style="width: 100px; height: 100px">
          </td>
        </tr>
    </table>
</div>
</body>
</html
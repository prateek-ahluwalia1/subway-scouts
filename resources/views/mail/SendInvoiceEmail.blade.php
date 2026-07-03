<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4;
            margin: 0;
        }

        header {
            background: #f0f0f0;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header img {
            width: 100%;
            max-width: 100px;
            height: auto;
        }

        header div {
            text-align: right;
            width: 50%;
            padding: 10px;
            color: white;
            position: relative;
            margin-right: 10px;
        }

        header div::before {
            content: "";
            position: absolute;
            top: 0;
            left: 40%;
            width: 80%;
            height: 120%;
            background-image: linear-gradient(to right, #29b0917f, #0c8a6c, #29b0917f);
            border-top-left-radius: 50%;
            border-bottom-left-radius: 50%;
        }

        header hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        main {
            padding: 20px;
            margin: 0 auto;
            max-width: 800px;
        }

        footer {
            text-align: center;
            padding: 10px;
        }

        @media (max-width: 600px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            header div {
                text-align: left;
                width: 100%;
                margin-top: 10px;
                margin-right: 0;
            }
        }

        @media print {
            table {
                page-break-inside: avoid;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>

<body style="max-width: 800px;
max-height: 100vh;position: relative;
    left: 25%; ">
    <div style="    
    background-color: gainsboro;
    width: 100%;
    position: relative;
    height: 120px;">
        <div style="width: 50%; position: absolute; left: 0; top: 0;">
            <h2 style="font-size: 36px; color: #0c8a6c; margin: 15px 0 0 0">
                <img style="height: 50%; width: 30%; margin-left: 30%; padding-bottom: 10%;" src="https://app.247staffingsolutions.com.au/assets/images/logo/scouts.png" alt="logo">
            </h2>
        </div>
        <div style="width: 50%; margin-left: 40%; right: 0; top: 0; text-align: right; position: relative;">
            <div style="background: linear-gradient(to right, rgba(41, 176, 145, 0.4980392157), #0c8a6c);
            border-radius: 75px 0 0 75px;
            height: 130px;
         
            position: relative;
            top: -3px;
            right: -80px;
            text-align: center;
            display: flex;
            
            flex-direction: column;
            align-items: center;">
                <h4 style="font-size: 24px; color: white; margin: 10px 0;">Invoice</h4>
                <p style="color: white; margin: 0;"><strong>Invoice Number:</strong> {{$invoice_no}}</p>
                <p style="color: white; margin: 0;"><strong>Date:</strong> {{ $date->format('d-M-Y') }}</p>
                <p style="color: white; margin: 0;"><strong>Due Date:</strong> {{ $due_date->format('d-M-Y') }}</p>
            </div>
        </div>
    </div>
    
    <main>
        <div style="width: 100%; position: relative; height: 155px">
            <div style="width: 50%; position: absolute; left: 0; top: 0">
                <h6 style="font-size: 18px; margin-left: 30px; color: #0c8a6c;">Invoice To</h6>
                <div style="margin-left: 30px;">
                    <p style="margin: 0 0 5px 0">{{ $invoice_to['name'] }}</p>
                    <p style="margin: 0 0 5px 0">Email: {{ $invoice_to['email'] }}</p>
                    <p style="margin: 0 0 5px 0">Phone: {{ $invoice_to['phone'] }}</p>
                    <p style="margin: 0 0 5px 0">ABN: {{ $invoice_to['abn'] }}</p>
                    @if(!empty($invoice_to['invoiceDescriptionTo']))
                    <p style="margin: 0 0 5px 0">Notes: {{ $invoice_to['invoiceDescriptionTo'] }}</p>
                    @endif
                </div>
            </div>
            <div style="width: 50%; position: absolute; right: 0; top: 0; text-align: right">
                <h6 style="font-size: 18px; margin-right: 30px; color: #0c8a6c;">Invoice From</h6>
                <div style="margin-right: 30px;">
                    <p style="margin: 0 0 5px 0">{{ $invoice_from['name'] }}</p>
                    <p style="margin: 0 0 5px 0">Email: {{ $invoice_from['email'] }}</p>
                    <p style="margin: 0 0 5px 0">Phone: {{ $invoice_from['phone'] }}</p>
                    <p style="margin: 0 0 5px 0">ABN: {{ $invoice_from['abn'] }}</p>
                    @if(!empty($invoice_from['invoiceDescriptionFrom']))
                    <p style="margin: 0 0 5px 0">Notes: {{ $invoice_from['invoiceDescriptionFrom'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body" style="margin-top: 15%;">
            <div class="table-responsive">
                <table class="table" style="color: #333; vertical-align: top; border-color: #dee2e6; caption-side: bottom; border-collapse: collapse; width: 100%;">
                @if($type == "normal")
                    <thead style="color: #0c8a6c; background-color: gainsboro;">
                        <tr class="tr">
                            <th style="text-align: center;">No.</th>
                            <th style="text-align: center;">Name</th>
                            <th style="text-align: center;">Hours</th>
                            <th style="text-align: center;">Price</th>
                            <th style="text-align: center;">Total</th>
                        </tr>
                    </thead>
                    <tbody>  
                        @php
                            $sum = 0;
                        @endphp
                        @foreach ($records as $value)
                        <tr>
                            <td style="text-align: center; padding: 15px; margin-left: 10%; border-bottom: 1px solid #00000020">{{ $loop->iteration }}</td>
                            <td style="text-align: center; padding: 15px; border-bottom: 1px solid #00000020">{{ $value['name'] }}</td>
                            <td style="text-align: center; padding: 15px; border-bottom: 1px solid #00000020">{{ $value['hours'] }}</td>
                            <td style="text-align: center; padding: 15px; border-bottom: 1px solid #00000020">${{ $value['payrate'] }}</td>
                            <td style="text-align: center; padding: 15px; border-bottom: 1px solid #00000020">${{ round($value['totalpay'], 2) }}</td>
                        </tr>
                        @php
                            $sum += $value['totalpay'];
                            $cl_gst = $sum * $GST / 100;
                            $grand_total = $sum + $cl_gst;
                        @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="card-footer" style="text-align: right;">
                        <tr>
                            <td style="padding: 20px; text-align: right" colspan="3">
                                <strong>Sub Total</strong>
                            </td>
                            <td style="padding: 15px; text-align: right" colspan="1">${{ round($sum, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; text-align: right" colspan="3">
                                <strong>Tax</strong>
                            </td>
                            <td style="padding: 15px; text-align: right" colspan="1">0</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; text-align: right" colspan="3">
                                <strong>GST</strong>
                            </td>
                            <td style="padding: 15px; text-align: right" colspan="1">{{ $GST }} %</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; text-align: right" colspan="3">
                                <strong>Total</strong>
                            </td>
                            <td style="padding: 15px; text-align: right" colspan="1">${{ round($grand_total, 2) }}</td>
                        </tr>
                    </tfoot>
                    @elseif($type == "summariz")
                    <thead style="color: #0c8a6c; background-color: gainsboro;">
                        <tr class="tr">
                            <th style="text-align: center;">No.</th>
                            <th style="text-align: center;">Description</th>
                            <th style="text-align: center;">State</th>
                            <th style="text-align: center;">Hours</th>
                            <th style="text-align: center;">Price</th>
                            <th style="text-align: center;">Total</th>
                        </tr>
                    </thead>
                    <tbody>  
                        @php
                            $sum = 0;
                        @endphp
                        @foreach ($records as $value)
                        <tr>
                            <td style="text-align: center; padding: 15px; margin-left: 10%; border-bottom: 1px solid #00000020">{{ $loop->iteration }}</td>
                            <td>
                                <div style="display:flex; flex-direction:column;">
                                    <span>{{ \Carbon\Carbon::parse($value['start'])->format('Y-m-d') . ', ' . $value['hour_type']}}</span>
                                    <span>{{ $value['first_name'] }} {{ $value['last_name'] }}</span>
                                    <span>{{ \Carbon\Carbon::parse($value['start'])->format('Hi') . ',' . \Carbon\Carbon::parse($value['end'])->format('Hi')}}</span>                         
                                </div>
                           </td>
                            <td style="text-align: center; padding: 15px; border-bottom: 1px solid #00000020">{{ $value['state'] }}</td>
                            <td style="text-align: center; padding: 15px; border-bottom: 1px solid #00000020">{{ $value['hours'] }}</td>
                            <td style="text-align: center; padding: 15px; border-bottom: 1px solid #00000020">${{ $value['rate'] }}</td>
                            <td style="text-align: center; padding: 15px; border-bottom: 1px solid #00000020">${{ round($value['hours'] * $value['rate'], 2) }}</td>
                        </tr>
                        @php
                            $sum += $value['hours'] * $value['rate'];
                            $cl_gst = $sum * $GST / 100;
                            $grand_total = $sum + $cl_gst;
                        @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="card-footer" style="text-align: right;">
                        <tr>
                            <td style="padding: 20px; text-align: right" colspan="3">
                                <strong>Sub Total</strong>
                            </td>
                            <td style="padding: 15px; text-align: right" colspan="1">${{ round($sum, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; text-align: right" colspan="3">
                                <strong>Tax</strong>
                            </td>
                            <td style="padding: 15px; text-align: right" colspan="1">0</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; text-align: right" colspan="3">
                                <strong>GST</strong>
                            </td>
                            <td style="padding: 15px; text-align: right" colspan="1">{{ $GST }} %</td>
                        </tr>
                        <tr>
                            <td style="padding: 15px; text-align: right" colspan="3">
                                <strong>Total</strong>
                            </td>
                            <td style="padding: 15px; text-align: right" colspan="1">${{ round($grand_total, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
                <div style="width: 100%; position: relative; height: 155px">
                    <div style="width: 50%; position: absolute; left: 0; top: 0">
                        <h6 style="font-size: 18px; margin-left: 30px; color: #0c8a6c;">Notes</h6>
                        <div style="margin-left: 30px;">
                            <p style="margin: 0 0 5px 0">{{ $notes }}</p>
                        </div>
                    </div>
                    @if($payment_method == 'bank_transfer')
                    <div style="width: 50%; position: absolute; right: 0; top: 0; text-align: right">
                        <h6 style="font-size: 18px; margin-right: 30px; color: #0c8a6c;">Payment Method</h6>
                        <div style="margin-right: 30px;">
                            <p style="margin: 0 0 5px 0">Bank Name: {{ $payment_type['bank_name'] }}</p>
                            <p style="margin: 0 0 5px 0">Account No: {{ $payment_type['account_number'] }}</p>
                            <p style="margin: 0 0 5px 0">BSB: {{ $payment_type['bsb'] }}</p>
                        </div>
                    </div>
                    @elseif($payment_method == 'bpay')
                    <div style="width: 50%; position: absolute; right: 0; top: 0; text-align: right">
                        <h6 style="font-size: 18px; margin-right: 30px; color: #0c8a6c;">Payment Method</h6>
                        <div style="margin-right: 30px;">
                            <p style="margin: 0 0 5px 0">Biller Code: {{ $payment_type['biller_code'] }}</p>
                            <p style="margin: 0 0 5px 0">Reference No: {{ $payment_type['reference_no'] }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <footer style="background-color: #0c8a6c; margin-top: 20px;">
        <strong>&copy; AMG Security</strong>
    </footer>
</body>

</html>



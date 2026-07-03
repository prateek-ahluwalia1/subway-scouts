<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
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

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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

        .wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 120px); /* Adjust this value based on your header height */
        }

        main {
            padding: 20px;
            margin: 0 auto;
            max-width: 800px;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: #0c8a6c;
            color: white;
            margin-top: auto;
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
    </style>
</head>

<body style="max-width: 800px; max-height: 100vh; left: 25%;">
    <div class="wrapper">
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
            <div style="width: 50%;margin-left: 40%;right: 0;top: 0;position: relative;">
                <div style="
                background: linear-gradient(to right, rgba(41, 176, 145, 0.4980392157), #0c8a6c);
                border-radius: 75px 0 0 75px;
                height: 130px;
                position: relative;
               
                right: -80px;
               
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                align-content: flex-start;
                ">
                    <h4 style="font-size: 24px;color: white;margin: 14px 11px 3px 48px;">INVOICE</h4>
                    <p style="color: white;margin: 0 0 0 48px;"><strong style="padding-right: 20px;">Invoice Number:</strong>{{ $data['invoice_no'] }}</p>
                    <p style="CONTAIN-INTRINSIC-BLOCK-SIZE: AUTO 100PX;margin: 0 0 0 48px;color: white;"><strong style="
                            padding-right: 41px;
                        ">Invoice Date:</strong> {{ $data['date'] }}</p>
                        <p style="CONTAIN-INTRINSIC-BLOCK-SIZE: AUTO 100PX;margin: 0 0 0 48px;color: white;"><strong style="
                            padding-right: 41px;
                        ">Due Date:</strong> {{ $data['due_date'] }}</p>
                </div>
            </div>
        </div>

        <div style="width: 100%; position: relative; height: 130px">
            <div style="width: 50%; position: absolute; left: 0; top: 0">
                <h6 style="font-size: 18px; margin-left: 30px; color: #0c8a6c;">Invoice To</h6>
                <div style="margin-left: 30px;">
                    <p style="margin: 0 0 5px 0">{{ $data['invoice_to']['name'] }}</p>
                    <p style="margin: 0 0 5px 0">Email: {{ $data['invoice_to']['email'] }}</p>
                    <p style="margin: 0 0 5px 0">Phone: {{ $data['invoice_to']['phone'] }}</p>
                    <p style="margin: 0 0 5px 0">ABN: {{ $data['invoice_to']['abn'] }}</p>
                    @if(!empty($data['invoice_to']['invoiceDescriptionTo']))
                    <p style="margin: 0 0 5px 0">Notes: {{ $data['invoice_to']['invoiceDescriptionTo'] }}</p>
                    @endif
                </div>
            </div>
            <div style="width: 50%; position: absolute; right: 0; top: 0; text-align: left">
                <h6 style="font-size: 18px; margin-right: 30px; color: #0c8a6c;">Invoice From</h6>
                <div style="margin-right: 30px;">
                    <p style="margin: 0 0 5px 0">{{ $data['invoice_from']['name'] }}</p>
                    <p style="margin: 0 0 5px 0">Email: {{ $data['invoice_from']['email'] }}</p>
                    <p style="margin: 0 0 5px 0">Phone: {{ $data['invoice_from']['phone'] }}</p>
                    <p style="margin: 0 0 5px 0">ABN: {{ $data['invoice_from']['abn'] }}</p>
                    @if(!empty($data['invoice_from'][' invoiceDescriptionFrom']))
                    <p style="margin: 0 0 5px 0">Notes: {{ $data['invoice_from'][' invoiceDescriptionFrom'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body" style="margin-top: 15%;">
            <div class="table-responsive">
                <table class="table" style="color: #333; vertical-align: top; border-color: #dee2e6; caption-side: bottom; border-collapse: collapse; width: 100%;">
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
                        @foreach ($data['rates'] as $value)
                        <tr>
                            <td style="text-align: center; padding: 10px; margin-left: 10%; border-bottom: 1px solid #00000020">{{ $loop->iteration }}</td>
                            <td style="text-align: center; padding: 10px; border-bottom: 1px solid #00000020">{{ $value['name'] }}</td>
                            <td style="text-align: center; padding: 10px; border-bottom: 1px solid #00000020">{{ $value['hours'] }}</td>
                            <td style="text-align: center; padding: 10px; border-bottom: 1px solid #00000020">${{ $value['payrate'] }}</td>
                            <td style="text-align: center; padding: 10px; border-bottom: 1px solid #00000020">${{ $value['totalpay'] }}</td>
                        </tr>
                        @php
                            $sum += $value['totalpay'];
                            $cl_gst = $sum * $data['GST'] / 100;
                            $grand_total = $sum + $cl_gst;
                        @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="card-footer" style="text-align: right;">
                        <tr>
                            <td style="padding: 10px; text-align: right" colspan="4">
                                <strong>Sub Total</strong>
                            </td>
                            <td style="padding: 10px; text-align: right; text-align: center;" colspan="1">${{ round($sum, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; text-align: right" colspan="4">
                                <strong>Tax</strong>
                            </td>
                            <td style="padding: 10px; text-align: center;" colspan="1">$0</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; text-align: right" colspan="4">
                                <strong>GST</strong>
                            </td>
                            <td style="padding: 10px; text-align: center;" colspan="1">{{ $data['GST'] }} %</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; text-align: right" colspan="4">
                                <strong>Total</strong>
                            </td>
                            <td style="padding: 10px; text-align: center;" colspan="1">${{ round($grand_total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
                <div style="width: 100%; position: relative; height: 155px">
                    <div style="width: 50%; position: absolute; left: 0; top: 0">
                        <h6 style="font-size: 18px; margin-left: 30px; color: #0c8a6c;">Additional Notes</h6>
                        <div style="margin-left: 30px;">
                            <p style="margin: 0 0 5px 0">{{ $data['notes'] }}</p>
                        </div>
                    </div>
                    @if($data['payment_method'] == 'bank_transfer')
                    <div style="width: 50%; position: absolute; right: 0; top: 0; text-align: right">
                        <h6 style="font-size: 18px; margin-right: 30px; color: #0c8a6c;">Payment Method</h6>
                        <div style="margin-right: 30px;">
                            <p style="margin: 0 0 5px 0">Bank Name: {{ $data['payment_type']['bank_name'] }}</p>
                            <p style="margin: 0 0 5px 0">Account No: {{ $data['payment_type']['account_number'] }}</p>
                            <p style="margin: 0 0 5px 0">BSB: {{ $data['payment_type']['bsb'] }}</p>
                        </div>
                    </div>
                    @elseif($data['payment_method'] == 'bpay')
                    <div style="width: 50%; position: absolute; right: 0; top: 0; text-align: right">
                        <h6 style="font-size: 18px; margin-right: 30px; color: #0c8a6c;">Payment Method</h6>
                        <div style="margin-right: 30px;">
                            <p style="margin: 0 0 5px 0">Biller Code: {{ $data['payment_type']['biller_code'] }}</p>
                            <p style="margin: 0 0 5px 0">Reference No: {{ $data['payment_type']['reference_no'] }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>

</html>

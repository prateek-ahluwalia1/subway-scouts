<table>
    <thead>

        <tr>
            <th style="height:80px; background-color:#FFFFFF;" colspan="30"></th>
        </tr>

        <tr>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Customer</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Date From</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Date To</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Total Hrs Worked</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Travel Time</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Invoice Hrs</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Pay Hrs</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Diff in Hrs</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Charge Amount</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Gross Payout</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Gross PnL</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Super Amount</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Tax Deducted</th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Net Payout</th>
        </tr>

    </thead>
    <tbody>
    @foreach($data as $key =>$val)
    <tr>
        @php
        $percent = 0.11;
        $eleven_percent =  (int)$val['pay_amount'] * $percent;
        $net_amount = (int)$val['pay_amount'] - (int)$val['tax'];
        $net_pnl = ((int)$val['chargerate_hours'] - (int)$net_amount) + (int)$eleven_percent;
        @endphp
        <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']->name}}</td>
        <td style="text-align:center;border:1px solid #000000;">{{usaToAus($val['data_from'])}}</td>
        <td style="text-align:center;border:1px solid #000000;">{{usaToAus($val['data_to'])}}</td>
        <td style="text-align:center;border:1px solid #000000;">{{(int)floatval(str_replace(',', '', $val['chargerate_hours']))}}</td>
        <td style="text-align:center;border:1px solid #000000;">{{$val['travel_time']}}</td>
        <td style="text-align:center;border:1px solid #000000;">{{(int)floatval(str_replace(',', '', $val['chargerate_hours'])) + (int)$val['travel_time']}}</td>
        <td style="text-align:center;border:1px solid #000000;">{{$val['pay_hours']}}</td>
        <td style="text-align:center;border:1px solid #000000;">{{((int)floatval(str_replace(',', '', $val['chargerate_hours'])) + (int)$val['travel_time']) - (int)$val['pay_hours']}}</td>
        <td style="text-align:center;border:1px solid #000000;">${{$val['charge_amount']}}</td>
        <td style="text-align:center;border:1px solid #000000;">${{round((int)$val['pay_amount'], 2)}}</td>  <!-- Gross --> 
        <td style="text-align:center;border:1px solid #000000;">${{(int)$val['charge_amount'] - $net_amount + $eleven_percent}}</td>
        <td style="text-align:center;border:1px solid #000000;">${{round($eleven_percent, 2)}}</td>  <!-- Super --> 
        <td style="text-align:center;border:1px solid #000000;">${{round($val['tax'], 2)}}</td>  <!-- Tax --> 
        <td style="text-align:center;border:1px solid #000000;">${{round($net_amount , 2)}}</td>  <!-- Net --> 
    </tr>
    @endforeach

    </tbody>

</table>

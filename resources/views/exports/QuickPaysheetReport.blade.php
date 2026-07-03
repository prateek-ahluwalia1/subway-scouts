<table style="width:100%;">
    <thead>

        <tr>
            <th style="height:80px; background-color:#FFFFFF;" colspan="30"></th>
        </tr>

        <tr>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Name
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                BSB
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                A/C
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Tax File - ABN
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Phone
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Day Hours
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Day Pay
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Night Hours
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Night Pay
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Saturday Hours
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Saturday Pay
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Sunday Hours
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Sunday Pay
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Public Holiday Hours
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
                Public Holiday Pay
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
               Total  Hours
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
               Gross Amount
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
               Tax
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
               Super
            </th>
            <th
                style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">
               Total Payable
            </th>
        </tr>
    </thead>
    @php
    $grand_total = 0;
    $grand_super = 0;
    $gross_grand = 0;
    $grand_tax = 0;
    $grand_hours = 0;
    $day_pay_grand = 0;
    $night_pay_grand = 0;
    $saturday_pay_grand = 0;
    $sunday_pay_grand = 0;
    $ph_pay_grand = 0;
    $day_grand = 0;
    $night_grand = 0;
    $saturday_grand = 0;
    $sunday_grand = 0;
    $ph_grand = 0;
    @endphp
    @foreach($data as $pi)
        @php
            $total_rate = $pi['day_pay'] + $pi['night_pay'] + $pi['saturday_pay'] + $pi['sunday_pay'] + $pi['ph_pay'];
            $annual_income = $total_rate * 26;
            if ($annual_income <= 18200) {
                $tax = 0;
            } elseif ($annual_income > 18200 && $annual_income <= 45000) {
                $tax = (0.19 * ($annual_income - 18200))/26;
            } elseif ($annual_income > 45000 && $annual_income <= 120000) {
                $tax = (5092 + 0.325 * ($annual_income - 45000))/26;
            } elseif ($annual_income > 120000 && $annual_income <= 180000) {
                $tax = (29467 + 0.37 * ($annual_income - 120000))/26;
            } elseif($annual_income > 180000){
                $tax = (51667 + 0.45 * ($annual_income - 180000))/26;
            }
            $percent = 0.11;
            $super = $total_rate * $percent;
            $grand_total += round($total_rate - $tax,2);
            $grand_super += round($super, 2);
            $grand_hours += $pi['total_hours' ];
            $grand_tax += $tax;
            $gross_grand += $pi['day_pay'] + $pi['night_pay'] + $pi['saturday_pay'] + $pi['sunday_pay'] + $pi['ph_pay'];
            $day_pay_grand += $pi['day_pay'];
            $night_pay_grand += $pi['night_pay'];
            $saturday_pay_grand += $pi['saturday_pay'];
            $sunday_pay_grand += $pi['sunday_pay'];
            $ph_pay_grand += $pi['ph_pay'];

            $day_grand += $pi['day'];
            $night_grand += $pi['night'];
            $saturday_grand += $pi['saturday'];
            $sunday_grand += $pi['sunday'];
            $ph_grand += $pi['ph'];
        @endphp
        <tr>
            <td class="bod-1">{{ $pi['name'] }}</td>
            <td class="bod-1">{{ $pi['bsb'] }}</td>
            <td class="bod-1">{{ $pi['bank'] }}</td>
            <td class="bod-1">{{ $pi['tfn'] }} - {{ $pi['payroll_abn_number'] }}</td>
            <td class="bod-1">{{ $pi['phone'] }}</td>
            <td class="bod-1">{{ $pi['day'] == 0 ? '-' : $pi['day'] }}</td>
            <td class="bod-1">{{ $pi['day_pay'] == 0 ? '-' : '$'.$pi['day_pay'] }}</td>
            <td class="bod-1">{{ $pi['night'] == 0 ? '-' : $pi['night'] }}</td>
            <td class="bod-1">{{ $pi['night_pay'] == 0 ? '-' : '$'.$pi['night_pay'] }}</td>
            <td class="bod-1">{{ $pi['saturday'] == 0 ? '-' : $pi['saturday'] }}</td>
            <td class="bod-1">{{ $pi['saturday_pay'] == 0 ? '-' : '$'.$pi['saturday_pay'] }}</td>
            <td class="bod-1">{{ $pi['sunday'] == 0 ? '-' : $pi['sunday'] }}</td>
            <td class="bod-1">{{ $pi['sunday_pay'] == 0 ? '-' : '$'.$pi['sunday_pay'] }}</td>
            <td class="bod-1">{{ $pi['ph'] == 0 ? '-' : $pi['ph'] }}</td>
            <td class="bod-1">{{ $pi['ph_pay'] == 0 ? '-' : '$'.$pi['ph_pay'] }}</td>
            <td class="bod-1">{{ $pi['total_hours' ] }}</td>
            <td class="bod-1">
                @if($pi['day_pay'] + $pi['night_pay'] + $pi['saturday_pay'] + $pi['sunday_pay'] + $pi['ph_pay'] == 0)
                -
                @else
                ${{ $pi['day_pay'] + $pi['night_pay'] + $pi['saturday_pay'] + $pi['sunday_pay'] + $pi['ph_pay'] }}
                @endif
            </td>
            <td class="bod-1">${{ round($tax, 2) }}</td>
            <td class="bod-1">${{ round($super, 2) }}</td>
            <td class="bod-1">${{ round($total_rate - $tax,2) }}</td>
            
        </tr>
        @if ($loop->last)
        <tr style="background-color: #dee2e6;">
            <td class="bod-1" style="background: #dee2e6;"></td>
            <td class="bod-1" style="background: #dee2e6;"></td>
            <td class="bod-1" style="background: #dee2e6;"></td>
            <td class="bod-1" style="background: #dee2e6;"></td>
            <td class="bod-1" style="background: #dee2e6;"></td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">{{round($day_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">${{round($day_pay_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">{{round($night_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">${{round($night_pay_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">{{round($saturday_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">${{round($saturday_pay_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">{{round($sunday_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">${{round($sunday_pay_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">{{round($ph_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">${{round($ph_pay_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">{{round($grand_hours, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">${{round($gross_grand, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">${{round($grand_tax, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold">${{round($grand_super, 2)}}</td>
            <td class="bod-1" style="background: #dee2e6;font-weight:bold"><h2>${{round($grand_total, 2)}}</h2></td>
        </tr>
        @endif
     @endforeach
</table>


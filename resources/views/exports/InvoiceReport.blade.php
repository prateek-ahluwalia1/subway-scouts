<table>
 <thead>
     
<tr>
   <th style="height:80px; background-color:#FFFFFF;" colspan="30"></th>
 </tr>

  <tr>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">State</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Name</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Level</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Staff</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Staff Phone</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Customer</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Date</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Shift Start</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Shift End</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Sign In</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Sign Out</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Hours</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">M-F Weekday</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">M-F Day Rates</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">M-F Weeknight</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">M-F Night Rates</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Saturday</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Saturday Rates</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Sunday</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Sunday Rates</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Public Holiday Hours</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Public Holiday Rates</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Travel Hours</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Travel Rates</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Reimbursement Text</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Reimbursement</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Total Amount</th>
   <!-- <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Payroll</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Bank Name</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">BSB</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Bank Account Number</th> -->
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">P.O/W.O</th>
   <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Training</th>
 </tr>
 
</thead>
<tbody>

  <?php 
  
  $site_name = '';
  $total_actual_hours = 0;
  $total_morning_hours = 0;
  $total_night_hours = 0;
  $total_saturday_morning_hours = 0;
  $total_saturday_night_hours = 0;
  $total_sunday_morning_hours = 0;
  $total_sunday_night_hours = 0;
  $total_ph_hours = 0;
  $total_ph_morning_hours = 0;
  $total_ph_night_hours = 0;
  $total_day_rate = 0;
  $total_night_rate = 0;
  $total_public_holiday_rate = 0;
  $total_saturday_rate = 0;
  $total_sunday_rate = 0;
  $travel_rate = 0;
  $reimbursement_rate = 0;
  $travel_hours = 0;
  $total_amount = 0;
  $grand_total = 0;

  $tauh = 0;
  $tath = 0;
  $dh = 0;
  $dr = 0;
  $nh = 0;
  $nr = 0;
  $sadh = 0;
  $sanh = 0;
  $sr = 0;
  $sudh = 0;
  $sunh = 0;
  $sur = 0;
  $phh = 0;
  $phdh = 0;
  $phr = 0;
  $phnh = 0;
  $tr = 0;
  $rr = 0;
  $th = 0;
  $ttamt = 0;
  $ttp = 0;

  ?>
 @foreach($data as $key => $val)
 <?php 
 $val['site_name'] = $val['site_name'] != '' ? $val['site_name'] : 'N/A';
  if ($site_name != '' && $site_name != $val['site_name']) {
    ?>
    <tr>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">{{number_format($total_actual_hours, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">{{number_format($total_morning_hours, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">${{number_format($total_day_rate, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">{{number_format($total_night_hours, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">${{number_format($total_night_rate, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">{{number_format($total_saturday_morning_hours + $total_saturday_night_hours, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">${{number_format($total_saturday_rate, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">{{number_format($total_sunday_morning_hours + $total_sunday_night_hours, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">${{number_format($total_sunday_rate, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">{{number_format($total_ph_morning_hours + $total_ph_night_hours, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">${{number_format($total_public_holiday_rate, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">{{number_format($travel_hours, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">${{number_format($travel_rate, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">${{number_format($reimbursement_rate, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;">${{number_format($total_amount, 2)}}</td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;"></td>
  <td style="text-align:center; background-color: #BFBFBF; border:1px solid #000000; font-weight: bold;"></td>

  


</tr>
    <?php
  $total_actual_hours = 0;
  $total_morning_hours = 0;
  $total_night_hours = 0;
  $total_saturday_morning_hours = 0;
  $total_saturday_night_hours = 0;
  $total_sunday_morning_hours = 0;
  $total_sunday_night_hours = 0;
  $total_ph_hours = 0;
  $total_ph_morning_hours = 0;
  $total_ph_night_hours = 0;
  $total_day_rate = 0;
  $total_night_rate = 0;
  $total_public_holiday_rate = 0;
  $total_saturday_rate = 0;
  $total_sunday_rate = 0;
  $total_amount = 0;
  $travel_hours = 0;
  $travel_rate = 0;
  $reimbursement_rate = 0;
  $total_travel_hours = 0;
  }

 if(!empty($val['signin_time'])){
  $signin_time = $val['signin_time'];
  $signin_time = str_replace('T', ' ', $signin_time);
  $signin_time = explode('+', $signin_time);
  $signin_time = $signin_time[0];
 }else{
    $signin_time = 'N/A';
}

if(!empty($val['signout_time'])){
  $signout_time = $val['signout_time'];
  $signout_time = str_replace('T', ' ', $signout_time);
  $signout_time = explode('+', $signout_time);
  $signout_time = $signout_time[0];
}else{
  $signout_time = 'N/A';
}

$base_rate = 0;
?>

<!-- <tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['state']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} ({{$val['site_description']}})</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['level']}}</td>
  
  <td style="text-align:center;border:1px solid #000000;">{{$val['guard_full_name'] != '' ? $val['guard_full_name'] : 'N/A'}}</td>
 
  <td style="text-align:center;border:1px solid #000000;">{{$val['phone']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['start']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['end']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['hours']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{ number_format(($val['hours'] + $val['travel_time']), 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{number_format($val['morning_hours'], 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['saturday_morning_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['saturday_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['sunday_morning_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['sunday_night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{($val['ph_morning_hours'])}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{($val['ph_night_hours'])}}</td>
</tr> -->
<tr>
  <td style="text-align:center;border:1px solid #000000;">{{$val['state']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['site_name']}} ({{$val['site_description']}})</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['level']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{ $val['guard_full_name']  }}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['phone']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['customer_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{date('d-m-Y', strtotime($val['start']))}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{ date('H:i', strtotime($val['start'])) }}</td>
  <td style="text-align:center;border:1px solid #000000;">{{ date('H:i', strtotime($val['end'])) }}</td>
  <td style="text-align:center;border:1px solid #000000;">{{ date('H:i', strtotime($val['signin_time'])) }}</td>
  <td style="text-align:center;border:1px solid #000000;">{{ date('H:i', strtotime($val['signout_time'])) }}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['hours']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{number_format($val['morning_hours'], 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{$val['day_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['night_hours']}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{$val['night_rate']}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{($val['saturday_morning_hours'] + $val['saturday_night_hours'])}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{$val['saturday_rate']}}</td>
  <!-- <td style="text-align:center;border:1px solid #000000;">{{$val['saturday_night_hours']}}</td> -->
  <td style="text-align:center;border:1px solid #000000;">{{($val['sunday_morning_hours']+$val['sunday_night_hours'])}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{$val['sunday_rate']}}</td>
  <!-- <td style="text-align:center;border:1px solid #000000;">{{$val['sunday_night_hours']}}</td> -->
  <td style="text-align:center;border:1px solid #000000;">{{($val['ph_morning_hours'] + $val['ph_night_hours'])}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{$val['public_holiday_rate']}}</td>
  <!-- <td style="text-align:center;border:1px solid #000000;">{{($val['ph_night_hours'])}}</td> -->


  <td style="text-align:center;border:1px solid #000000;">{{number_format($val['travel_time_value'], 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{number_format($val['travel_time_value'] * $val['day_rate'], 2)}}</td>
  <td style="text-align:center;border:1px solid #000000;">{{$val['reimbursement_text']}}</td>
  <td style="text-align:center;border:1px solid #000000;">${{number_format($val['reimbursement_value'], 2)}}</td>
  
<!-- ot hour -->
 <!--  <td style="text-align:center;border:1px solid #000000;color: red;">0</td>
  <td style="text-align:center;border:1px solid #000000;color: red;">${{($val['ot'] == 1.5) ? 1.5 * $base_rate : 1.5 * $base_rate}}</td>
  <td style="text-align:center;border:1px solid #000000;color: red;">0</td>
  <td style="text-align:center;border:1px solid #000000;color: red;">${{($val['ot'] == 2) ? 2 * $base_rate : 2 * $base_rate}}</td>
  <td style="text-align:center;border:1px solid #000000;color: red;">0</td>
  <td style="text-align:center;border:1px solid #000000;color: red;">${{($val['ot'] == 2.5) ? 2.5 * $base_rate : 2.5 * $base_rate}}</td> -->
<!-- ot hour -->

  <td style="text-align:center;border:1px solid #000000;background-color: #A9D08E; border:1px solid #000000;">${{$val['total_amount']}}</td>
  <!-- <td style="text-align:center;border:1px solid #000000;background-color: #B4C6E7">{{($val['payrol'] ? $val['payrol'] : 'N/A')}}</td>
  <td style="text-align:center;border:1px solid #000000;background-color: #B4C6E7">{{$val['payroll_bank_name']}}</td>
  <td style="text-align:center;border:1px solid #000000;background-color: #B4C6E7">{{$val['bsb']}}</td>
  <td style="text-align:center;border:1px solid #000000;background-color: #B4C6E7">{{$val['payroll_bank_account_number']}}</td> -->
  <td style="text-align:center;border:1px solid #000000;background-color: #B4C6E7">{{$val['po_wo'] ?: ($val['site_po_wo'] ?: 'N/A') }}</td>
  <td style="text-align:center;border:1px solid #000000;background-color: #B4C6E7">{{($val['training'] == 0 ? 'no' : 'yes' )}}</td>
</tr>
<?php

  $site_name = $val['site_name'] != '' ? $val['site_name'] : 'N/A';
  $total_actual_hours = $total_actual_hours + ($val['hours'] + $val['travel_time']);
  $total_morning_hours = $total_morning_hours + $val['morning_hours'];
  $total_night_hours = $total_night_hours + $val['night_hours'];
  $total_saturday_morning_hours = $total_saturday_morning_hours + $val['saturday_morning_hours'];
  $total_saturday_night_hours = $total_saturday_night_hours + $val['saturday_night_hours'];
  $total_sunday_morning_hours = $total_sunday_morning_hours + $val['sunday_morning_hours'];
  $total_sunday_night_hours = $total_sunday_night_hours + $val['sunday_night_hours'];
  $total_ph_hours = $total_ph_hours + $val['ph_morning_hours'] + $val['ph_night_hours'];
  $total_ph_morning_hours = $total_ph_morning_hours + $val['ph_morning_hours'];
  $total_ph_night_hours = $total_ph_night_hours + $val['ph_night_hours'];
  $total_day_rate = $total_day_rate + $val['day_rate'];
  $total_night_rate = $total_night_rate + $val['night_rate'];
  $total_public_holiday_rate = $total_public_holiday_rate + $val['public_holiday_rate'];
  $total_saturday_rate = $total_saturday_rate + $val['saturday_rate'];
  $total_sunday_rate = $total_sunday_rate + $val['sunday_rate'];
  $travel_hours = $travel_hours + $val['travel_time_value'];
  $travel_rate = $travel_rate + $val['travel_time_value'] * $val['day_rate'];
  $reimbursement_rate = $reimbursement_rate + $val['reimbursement_value'];
  $total_amount = $total_amount + $val['total_amount'] + $val['travel_time_value'] * $val['day_rate'];
  $grand_total += $val['total_amount'];

  $tauh += $val['hours'];
  $tath += ($val['hours'] + $val['travel_time']);
  $dh += $val['morning_hours'];
  $dr += $total_day_rate;
  $nr += $val['night_hours'];
  $nh += $total_night_rate;
  $sadh += $val['saturday_morning_hours'];
  $sanh += $val['saturday_night_hours'];
  $sr += $total_saturday_rate;
  $sudh += $val['sunday_morning_hours'];
  $sunh += $val['sunday_night_hours'];
  $sur += $total_sunday_rate;
  $phh += $val['ph_morning_hours'] + $val['ph_night_hours'];
  $phdh += $val['ph_morning_hours'];
  $phnh += $val['ph_night_hours'];
  $phr += $total_public_holiday_rate;
  $th += $travel_hours;
  $tr += $travel_rate;
  $rr += $val['reimbursement_value'];
  $ttamt += 0;
  $ttp += 0;
  
  

  if ($key == count($data) - 1) {
?>
    
    <tr>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td> 
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">{{$tauh}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">{{$dh}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">${{$dr}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">{{$nh}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">${{$nr}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">{{$sadh + $sanh}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">${{$sr}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">{{$sudh + $sunh}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">${{$sur}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">{{$phh}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">${{$phr}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">{{$th}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">${{$tr}}</td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;font-size:16px; font-weight:600;">${{$rr}}</td>
  <td style="text-align:center; background-color: #D3D3D3; border:1px solid #000000; font-size:16px; font-weight:600;">${{$grand_total}}</td>
  <!-- <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td> -->
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  <td style="text-align:center; background-color: #808080; border:1px solid #000000;"></td>
  

</tr>
    <?php
  }
?>
@endforeach

</tbody>

</table>
<!DOCTYPE html>
<html>
<body>
  <table>
    <tr>
      <th style="text-align:center; font-weight:bold;width: 100px;">Date</th>
      <th style="text-align:center; font-weight:bold;width: 450px;">Site Name</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Wilson Id</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Certis Id</th>
      <th style="text-align:center; font-weight:bold;width: 250px;">Staff Name</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Site Level</th>
      <th style="text-align:center; font-weight:bold;width: 150px;">Customer Name</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Start</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">End</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Total Hours</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Signin Time</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Signout Time</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Auto Signout</th>
      <!-- <th style="text-align:center; font-weight:bold;width: 100px;">Status</th> -->
      <!-- <th style="text-align:center; font-weight:bold;width: 100px;">Status Changed By</th> -->
      <th style="text-align:center; font-weight:bold;width: 100px;">Notes</th>
      {{-- <th style="text-align:center; font-weight:bold;width: 100px;">Staff Phone</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Staff Type</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">State</th> --}}
    </tr>
@foreach($data['results'] as $ts)
 <tr style="text-align: left;">
   <td>{{ !empty($ts->start) ? date('d-m-Y', strtotime($ts->start)) : 'N/A' }}</td>
   <td>{{ $ts->site_name ?? 'N/A' }}</td>
   <td>{{ $ts->guard_id1 ?? 'N/A' }}</td>
   <td>{{ $ts->guard_id2 ?? 'N/A' }}</td>
   <td>{{ trim(($ts->first_name ?? '') . ' ' . ($ts->middle_name ?? '') . ' ' . ($ts->last_name ?? '')) ?: 'N/A' }}</td>
   <td>{{ $ts->level ?? 'N/A' }}</td>
   <td>{{ $ts->customer_name ?? 'N/A' }}</td>
   <td>{{ !empty($ts->start) ? date('H:i', strtotime($ts->start)) : 'N/A' }}</td>
   <td>{{ !empty($ts->end) ? date('H:i', strtotime($ts->end)) : 'N/A' }}</td>
   <td>{{ $ts->hours ?? 0 }}</td>
   <td>{{ !empty($ts->signin_time) ? date('H:i', strtotime($ts->signin_time)) : 'N/A' }}</td>
   <td>{{ !empty($ts->signout_time) ? date('H:i', strtotime($ts->signout_time)) : 'N/A' }}</td>
   <td>{{ $ts->auto_signout ?? 'N/A' }}</td>
   <!-- <td>{{ ($ts->in_paysheet ?? 0) == 1 ? 'Active' : 'Inactive' }}</td> -->
   <!-- <td>{{ $ts->admin_name ?? 'N/A' }}</td> -->
   <!-- <td>{{ $ts->operation_notes ?? 'N/A' }}</td> -->
    <td>{{ $ts->operation_notes === '' ? 'N/A' : ($ts->operation_notes ?? 'N/A') }}</td>
    {{-- <td>{{ $ts->guard_phone ?? 'N/A' }}</td>
    <td>Part Time</td>
    <td>{{ $ts->guard_state ?? 'N/A' }}</td> --}}
</tr>
@endforeach
  </table>
</body>
</html>
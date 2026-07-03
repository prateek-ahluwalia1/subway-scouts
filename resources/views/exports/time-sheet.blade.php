<!DOCTYPE html>
<html>
<body>
  <table>
    <tr>
      <th style="text-align:center; font-weight:bold;width: 200px;">Staff Name</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Day Hours</th>
      <th style="text-align:center; font-weight:bold;width: 100px;">Night Hours</th>
      <th style="text-align:center; font-weight:bold;width: 200px;">Saturday</th>
      <th style="text-align:center; font-weight:bold;width: 200px;">Sunday</th>
      <th style="text-align:center; font-weight:bold;width: 200px;">Public Holiday</th>
      <th style="text-align:center; font-weight:bold;width: 200px;">Total Hours</th>
    </tr>
    @foreach($timesheet as $ts)
    <tr style="text-align: left;">
      <td>{{ trim(($ts['first_name'] ?? '') . ' ' . ($ts['middle_name'] ?? '') . ' ' . ($ts['last_name'] ?? '')) ?: 'N/A' }}</td>
      <td>{{ $ts['morning_hours'] ?? 0 }}</td>
      <td>{{ $ts['night_hours'] ?? 0 }}</td>
      <td>{{ ($ts['saturday_morning_hours'] ?? 0) + ($ts['saturday_night_hours'] ?? 0) }}</td>
      <td>{{ ($ts['sunday_morning_hours'] ?? 0) + ($ts['sunday_night_hours'] ?? 0) }}</td>
      <td>{{ ($ts['ph_morning_hours'] ?? 0) + ($ts['ph_night_hours'] ?? 0) }}</td>
      <td>{{ $ts['hours'] ?? 0 }}</td>
    </tr>
    @endforeach
  </table>
</body>
</html>

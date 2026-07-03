
<table>
    <thead>
        <tr>
            <th style="height:80px; background-color:#FFFFFF;" colspan="30"></th>
        </tr>
        
        
        <tr>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Date</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Client Name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Start Time</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">End Time</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Sign in Time</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Sign out Time</th>
            @php
                $gcCount = 0;
                $wcCount = 0;
                $maxgccount = 0;
            @endphp
            @for ($i = 0; $i < count($data) && $maxgccount < 2; $i++)
                @if ($data[$i]['call_type'] == 'Both')
                    @if ($maxgccount < 2)
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">{{ ++$maxgccount }} - Green Call Sent Date</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Green Call Sent Time</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response Date</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response Time</th>
                    @endif
                @endif

                @if ($maxgccount >= 2)
                    @break
                @endif
            @endfor

            @foreach ($data as $item)
                @if($item['call_type'] == 'Both')
                    @if ($wcCount < 25 && $item['type'] == 'WC')
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">{{ ++$wcCount }} - WF Call Sent Date</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">WF Call Sent Time</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response Date</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response Time</th>
                    @endif
                @else
                    @if ($gcCount < 2 && $item['type'] == 'GC')
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">{{ ++$gcCount }} - Green Call Sent Date</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Green Call Sent Time</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response Date</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response Time</th>
                    @elseif ($wcCount < 25 && $item['type'] == 'WC')
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">{{ ++$wcCount }} - WF Call Sent Date</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">WF Call Sent Time</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response Date</th>
                        <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Guard Response Time</th>
                    @endif
                @endif

                @if ($gcCount >= 2 && $wcCount >= 25)
                    @break
                @endif
            @endforeach
        </tr>
    </thead>
    <tbody>
    <?php
        //dd($data);
        $sites = $data->pluck('roster_id')->unique();
         $previous_site_name = null;

    ?>
        @foreach ($sites as $site)
        <?php
            $site_records = $data->where('roster_id', $site);
        ?>
        
        @foreach ($site_records as $index => $item)
            <?php
                $date = $item->date;
                $site_name = $item->site_name;
                $customer_name = $item->customer_name;
                $guard_name = $item->first_name . ' ' . $item->last_name;
                $start_time = $item->start_time;
                $end_time = $item->end_time;
                $sign_in = $item->signin_time;
                $sign_out = $item->signout_time;
            ?>
            @if ($site_name != $previous_site_name && $previous_site_name !== null)
                <tr>
                  <td colspan="{{ $item->type == 'GC' ? 18 : ($item->type == 'WC' ? 133 : 60) }}" style="background-color: #d1e6c8;">&nbsp;</td>
                </tr>
            @endif
            <?php $previous_site_name = $site_name; ?>
        @endforeach
           
            <tr>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ !empty($date) ? date('d-m-Y', strtotime($date)) : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site_name) ? $site_name : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($customer_name) ? $customer_name : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($guard_name) ? $guard_name : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ !empty($start_time) ? date('H:i', strtotime($start_time)) : 'N/A' }}</td>           
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ !empty($end_time) ? date('H:i', strtotime($end_time)) : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ !empty($sign_in) ? date('H:i', strtotime($sign_in)) : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ !empty($sign_out) ? date('H:i', strtotime($sign_out)) : 'N/A' }}</td>
                
                <?php
                    $count = $data->where('type', 'GC')->where('roster_id', $site)->count();
                    $wcount = 0;
                ?>

                @foreach ($site_records as $index => $item)
                        
                        @if ($item['call_type'] == 'Both')
                            @if ($item['type'] == 'GC')
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['send_time']) ? date('d-m-Y', $item['send_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['send_time']) ? date('H:i', $item['send_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response']) ? $item['response'] : 'N/A' }} ({{ isset($item['note']) ? $item['note'] : 'N/A' }})</td>                       
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response_time']) ? date('d-m-Y', $item['response_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response_time']) ? date('H:i', $item['response_time']) : 'N/A' }}</td>
                            @elseif ($item['type'] == 'WC')
                                 @if($wcount == 0 && $count == 0)
                                 <?php $wcount++; ?>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                @endif
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['send_time']) ? date('d-m-Y', $item['send_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['send_time']) ? date('H:i', $item['send_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response']) ? $item['response'] : 'N/A' }} ({{ isset($item['note']) ? $item['note'] : 'N/A' }})</td>                       
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response_time']) ? date('d-m-Y', $item['response_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response_time']) ? date('H:i', $item['response_time']) : 'N/A' }}</td>
                            @endif
                            @if($item['type'] == 'GC' && $count == 1)
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                            @elseif($item['type'] == 'GC' && $count == 0)
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                            @endif
                        @else
                            @if ($item['type'] == 'GC')
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['send_time']) ? date('d-m-Y', $item['send_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['send_time']) ? date('H:i', $item['send_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response']) ? $item['response'] : 'N/A' }} ({{ isset($item['note']) ? $item['note'] : 'N/A' }})</td>                       
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response_time']) ? date('d-m-Y', $item['response_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response_time']) ? date('H:i', $item['response_time']) : 'N/A' }}</td>
                            @elseif ($item['type'] == 'WC')
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['send_time']) ? date('d-m-Y', $item['send_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['send_time']) ? date('H:i', $item['send_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response']) ? $item['response'] : 'N/A' }} ({{ isset($item['note']) ? $item['note'] : 'N/A' }})</td>                       
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response_time']) ? date('d-m-Y', $item['response_time']) : 'N/A' }}</td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($item['response_time']) ? date('H:i', $item['response_time']) : 'N/A' }}</td>
                            @endif
                            @if($item['type'] == 'GC' && $count == 1)
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                            @elseif($item['type'] == 'GC' && $count == 0)
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"></td>
                            @endif
                        @endif
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

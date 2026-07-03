

<table>
    <thead>
        <tr>
            <th style="height:80px; background-color:#FFFFFF;" colspan="30"></th>
        </tr>

        <tr>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">P.O/W.O</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Client Name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Location Name</th>
            @foreach ($data['dates'] as $date)
                <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">{{ $date }}</th>
            @endforeach
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Grand Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $previousCustomerId = null;
            $grandTotal = array_fill_keys($data['dates'], 0);
        ?>

        @foreach ($data['mainArr'] as $customerId => $customerData)
            <?php
                $grandTotalForRow = array_fill_keys($data['dates'], 0);
            ?>
            @foreach ($customerData as $siteId => $site)
                <tr>
                    <td style="text-align:center; border:1px solid #000000;">{{ !empty($site['po_wo']) ? $site['po_wo'] : 'N/A' }}</td>
                    @if($site['customer_id'])
                      @if ($site['customer_id'] !== $previousCustomerId)
                          <td style="text-align:center; border:1px solid #000000;">{{ $site['customer_name'] }}</td>
                             @php
                                $previousCustomerId = $site['customer_id'];
                            @endphp
                      @else
                            <td style="text-align:center; border:1px solid #000000;"></td>
                      @endif
                    @endif
                    <td style="text-align:center; border:1px solid #000000;">{{ $site['site_name'] }}</td>
                    <?php
                        $rowTotal = 0;
                    ?>
                    @foreach ($data['dates'] as $date)
                        @if (isset($site['dates'][$date]))
                            <?php
                                $hours = $site['dates'][$date];
                                $rowTotal += $hours;
                                $grandTotalForRow[$date] += $hours;
                            ?>
                            <td style="text-align:center; border:1px solid #000000;">
                                {{ $hours }}
                            </td>
                        @else
                            <td style="text-align:center; border:1px solid #000000;">
                                0
                            </td>
                        @endif
                    @endforeach
                    <td style="text-align:center; border:1px solid #000000;">
                        {{ $rowTotal }}
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>


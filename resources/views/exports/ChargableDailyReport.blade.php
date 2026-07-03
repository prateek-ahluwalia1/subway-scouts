<table>
    <thead>
        <tr>
            <th style="height:80px; background-color:#FFFFFF;" colspan="30"></th>
        </tr>

        <tr>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">P.O/W.O</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Client Name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Service Contract Name</th>
            @foreach ($data['dates'] as $date)
                <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">{{ $date }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($data['mainArr'] as $customerId => $customerData)
            @foreach ($customerData as $siteId => $site)
                <tr>
                    <td style="text-align:center; border:1px solid #000000;">{{ !empty($site['po_wo']) ? $site['po_wo'] : 'N/A' }}</td>
                    <td style="text-align:center; border:1px solid #000000;">{{ $site['customer_name'] }}</td>
                    <td style="text-align:center; border:1px solid #000000;">{{ $site['site_name'] }}</td>
                    @foreach ($data['dates']  as $date)
                        <td style="text-align:center; border:1px solid #000000;">
                            {{ isset($site['dates'][$date]) ? $site['dates'][$date] : 0 }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach

        <!-- Subtotals for each date -->
        <tr>
            <td style="text-align:center; border:1px solid #000000;">Subtotal</td>
            <td></td>
            <td></td>
            @foreach ($data['dates'] as $date)
                <td style="text-align:center; border:1px solid #000000;">
                    <?php
                        $dateTotal = 0;
                        foreach ($data['mainArr'] as $customerId => $customerData) {
                            foreach ($customerData as $siteId => $site) {
                                $dateTotal += isset($site['dates'][$date]) ? $site['dates'][$date] : 0;
                            }
                        }
                        echo $dateTotal;
                    ?>
                </td>
            @endforeach
        </tr>
    </tbody>
</table>

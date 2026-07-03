<!DOCTYPE html>
<html lang="en">
<body>
    <table border="1">
        <thead>
            <tr>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Lead Created By</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Date of Lead</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Lead Agent Name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Lead Won Date</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Lead Name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Lead Client Name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Phone</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Email</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Travel Date</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Sub Company name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Operating Agent</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Original Lead Price</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Won Price</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">Booking Price</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 170px;">PnL</th>
            </tr>
        </thead>
        <tbody>
        <?php
         $total_pl = 0;
         ?>
        @foreach ($data as $key => $lead)
        <?php
        $pl = ($lead->actual_revenue ?? 0) - ($lead->booking_price ?? 0);
        $total_pl += $pl;
         ?>
            <tr>        
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ !empty($lead->CreatedBy) ? $lead->CreatedBy->name : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ !empty($lead->created_at) ? date('d-m-Y', strtotime($lead->created_at)) : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ !empty($lead->HandledBy) ? $lead->HandledBy->name : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ !empty($lead->updated_at) ? date('d-m-Y', strtotime($lead->updated_at)) : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ $lead->name ?? 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ !empty($lead->LeadClientName) ? $lead->LeadClientName->name : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ $lead->phone ?? 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ $lead->email ?? 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ $lead->travel_date ?? 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ $lead->sub_company ?? 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ !empty($lead->AssignOperation) ? $lead->AssignOperation->name : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">{{ $lead->annual_revenue ?? $lead->manual_revenue ?? 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">${{ $lead->actual_revenue ?? 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">${{ $lead->booking_price ?? 'N/A' }}</td>     
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 170px;">${{ $pl ?? 'N/A' }}</td>     
            </tr>
        @endforeach
            <tr>        
                <td style="text-align:center; background-color: #d3d3d3;" colspan="11"></td>
                <td style="text-align:center; border:1px solid #000000; background-color: #d3d3d3; font-size: 14px;width: 170px;">${{$total_pl}}</td>     
            </tr>
            
        </tbody>
    </table>
</body>
</html>

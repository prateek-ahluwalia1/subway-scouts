
<table>
    <thead>
        <tr>
            <th style="height:80px; background-color:#FFFFFF;" colspan="28"></th>
        </tr>
        
        
        <tr>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Name</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Description</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Customer</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Type</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">State</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Status</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Address</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">SOS Phone</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Level</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Hourly Rate</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Payrol</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Payrate</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Charge Rate</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Chargerate Level</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Payrate Level</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Site Hours</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Is Patrolling Site</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Patrolling Type</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Monitoring Person</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Monitoring Contact</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Po Wo</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Break</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Break Chargeable</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Break Payable</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Break Deduction Payable</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Break Deduction Chargeable</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Signin Radius</th>
            <th style="text-align:center; background-color: #d1e6c8; font-weight:bold; border:1px solid #000000; font-size: 14px;width: 100px;">Alert Radius</th>

        </tr>
    </thead>
    <tbody>
    @foreach($data as $site) 
    <tr>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->site_name) ? $site->site_name : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->site_description) ? $site->site_description : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->customer_name) ? $site->customer_name : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->type) ? $site->type : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->state) ? $site->state : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->site_status) ? $site->site_status : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->address) ? $site->address : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->sos_phone) ? $site->sos_phone : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->level) ? $site->level : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->hourly_rate) ? $site->hourly_rate : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->payrol) ? $site->payrol : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->site_payrate) ? $site->site_payrate : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->site_charge_rate) ? $site->site_charge_rate : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->site_chargerate_level) ? $site->site_chargerate_level : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->site_payrate_level) ? $site->site_payrate_level : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->site_hours) ? $site->site_hours : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;"> {{ isset($site->is_patrolling_site) ? ($site->is_patrolling_site ? 'Yes' : 'No') : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->patrolling_type) ? $site->patrolling_type : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->monitoring_person) ? $site->monitoring_person : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->monitoring_contact) ? $site->monitoring_contact : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->po_wo) ? $site->po_wo : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->break) ? ($site->break ? 'Yes' : 'No') : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->break_chargeable) ? $site->break_chargeable : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->break_payable) ? $site->break_payable : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->break_deduction_payable) ? $site->break_deduction_payable : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->break_deduction_chargeable) ? $site->break_deduction_chargeable : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->signin_radius) ? $site->signin_radius : 'N/A' }}</td>
                <td style="text-align:center; border:1px solid #000000; font-size: 14px;width: 100px;">{{ isset($site->alert_radius) ? $site->alert_radius : 'N/A' }}</td>
                </tr>
    @endforeach
    </tbody>
</table>

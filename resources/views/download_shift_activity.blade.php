<style>
    /* Simple CSS that DomPDF handles well */
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        margin: 0;
        padding: 20px;
    }
    
    .header {
        background-color: #02ABED;
        color: white;
        text-align: center;
        padding: 10px;
        margin-bottom: 20px;
    }
    
    .info-block {
        margin-bottom: 20px;
    }
    
    .info-line {
        margin-bottom: 8px;
        clear: both;
    }
    
    .label {
        font-weight: bold;
        float: left;
        width: 120px;
    }
    
    .value {
        margin-left: 130px;
    }
    
    .divider {
        border-top: 2px solid #02ABED;
        margin: 20px 0;
    }
    
    .timeline-item {
        margin-bottom: 15px;
        padding-left: 20px;
        border-left: 3px solid #02ABED;
    }
    
    .activity-title {
        color: #02ABED;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .footer {
        text-align: center;
        margin-top: 30px;
        padding-top: 10px;
        border-top: 1px solid #ccc;
        font-size: 10px;
        color: #666;
    }
</style>

<body>
    <div class="header">
        <h3>Shift Activity Detail</h3>
    </div>
    
    <div class="info-block">
        <div class="info-line">
            <span class="label">Customer Name:</span>
            <span class="value">{{ $customer ?? 'N/A' }}</span>
        </div>
        <div class="info-line">
            <span class="label">Staff Name:</span>
            <span class="value">{{ $staff ?? 'N/A' }}</span>
        </div>
        <div class="info-line">
            <span class="label">Location:</span>
            <span class="value">{{ $location ?? 'N/A' }}</span>
        </div>
        <div class="info-line">
            <span class="label">Shift Timings:</span>
            <span class="value">{{ $shift_start ?? 'N/A' }} - {{ $shift_end ?? 'N/A' }}</span>
        </div>
    </div>
    
    <div class="divider"></div>
    
    <div>
        <h4>Activity Timeline</h4>
        @if(isset($datas) && count($datas) > 0)
            @foreach($datas as $item)
                <div class="timeline-item">
                    <div class="activity-title">{{ $item->activity ?? 'Activity' }}</div>
                    <div>
                        @php
                            $timestamp = $item->activity_time;
                            if (strlen($timestamp) > 10) {
                                $timestamp = $timestamp / 1000;
                            }
                            echo date('Y-m-d H:i:s', $timestamp);
                        @endphp
                    </div>
                    @if(!empty($item->type))
                        <div>Type: {{ $item->type }}</div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="timeline-item">
                <div class="activity-title">No Activities Recorded</div>
                <div>No activity data available for this shift</div>
            </div>
        @endif
    </div>
    
    <div class="footer">
        <div>Report Generated on: {{ $generated_date ?? date('F d, Y') }}</div>
        <div>&copy; {{ date('Y') }} AMG Security</div>
    </div>
</body>
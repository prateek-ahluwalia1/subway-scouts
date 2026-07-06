<!DOCTYPE html>
<html>
<head>
    <title>Shift Task Details</title>
    <meta charset="utf-8">
    <style type="text/css">
        @page {
            size: A4;
            margin: 15mm;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            padding: 0;
            margin: 0;
            background: #ffffff;
            font-size: 14px;
        }
        
        .header-section {
            background: linear-gradient(135deg, #00a37e 0%, #008e6b 100%);
            color: white;
            padding: 25px;
            text-align: center;
            margin-bottom: 25px;
            border-radius: 8px;
        }
        
        .header-title {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .business-name {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            color: #105652;
            margin: 20px 0;
            padding: 12px 0;
            border-bottom: 3px solid #00a37e;
        }
        
        .section-title {
            background: #105652;
            color: #fff;
            text-align: center;
            padding: 10px;
            margin: 20px 0 15px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-grid {
            margin: 20px 0;
            padding: 18px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
        
        .info-item {
            margin-bottom: 12px;
            line-height: 1.8;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 700;
            color: #105652;
            font-size: 14px;
            display: inline;
        }
        
        .info-value {
            color: #333;
            font-size: 14px;
            display: inline;
            margin-left: 8px;
        }
        
        .timeline {
            position: relative;
            padding: 15px 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            width: 2px;
            background: #00a37e;
            top: 0;
            bottom: 0;
            left: 22px;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 25px;
            padding-left: 60px;
        }
        
        .timeline-icon {
            position: absolute;
            left: 10px;
            top: 15px;
            width: 26px;
            height: 26px;
            background: white;
            border: 2px solid #00a37e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: #105652;
            z-index: 1;
        }
        
        .timeline-content {
            padding: 18px;
            background: #ffffff;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .task-title {
            font-size: 16px;
            font-weight: 700;
            color: #105652;
            margin: 0 0 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #00a37e;
        }
        
        .time-section {
            margin-bottom: 15px;
        }
        
        .time-header {
            margin: 0 0 8px;
            font-size: 15px;
            color: #105652;
            font-weight: 700;
            border-bottom: 3px solid #00a37e;
            padding-bottom: 4px;
            padding-top: 5px;
        }
        
        .time-header.actual {
            border-bottom-color: #ff6b6b;
        }
        
        .time-row {
            margin-bottom: 5px;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .time-label {
            font-weight: 700;
            color: #555;
            display: inline;
        }
        
        .time-value {
            font-weight: 700;
            color: #333;
            display: inline;
            margin-left: 8px;
        }
        
        .status-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .status-label {
            font-weight: 700;
            color: #555;
            font-size: 13px;
        }
        
        .status-badge {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-in-progress {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .note-box {
            background: #fffbf0;
            padding: 12px;
            border-radius: 4px;
            border-left: 4px solid #00a37e;
            margin-bottom: 15px;
        }
        
        .note-label {
            font-weight: 700;
            color: #105652;
            font-size: 13px;
            margin-bottom: 4px;
            display: block;
        }
        
        .note-text {
            color: #555;
            font-style: italic;
            font-size: 13px;
        }
        
        .images-section {
            padding-top: 12px;
            border-top: 1px dashed #ddd;
        }
        
        .images-label {
            font-weight: 700;
            color: #105652;
            margin-bottom: 10px;
            display: block;
            font-size: 13px;
        }
        
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        
        .image-item {
            text-align: center;
        }
        
        .image-box {
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            padding: 8px;
            background: #fafafa;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .report-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .image-timestamp {
            font-size: 10px;
            color: #777;
            margin-top: 4px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #777;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                font-size: 11pt;
            }
            
            .header-section {
                padding: 20px;
            }
            
            .header-title {
                font-size: 22pt;
            }
            
            .timeline-content {
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="business-name">{{ $business_name }}</div>
    
    <div class="section-title">Shift Task Details</div>
    
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Customer Name:</span>
            <span class="info-value">{{ $customer }}</span>
        </div>
        
        <div class="info-item">
            <span class="info-label">Staff Name:</span>
            <span class="info-value">{{ $staff }}</span>
        </div>
        
        <div class="info-item">
            <span class="info-label">Location:</span>
            <span class="info-value">{{ $location }}</span>
        </div>
        
        <div class="info-item">
            <span class="info-label">Shift Timings:</span>
            <span class="info-value">{{ $shift_start }} - {{ $shift_end }}</span>
        </div>
    </div>

    <div class="timeline">
        @foreach($data as $index => $d)
        <div class="timeline-item">
            <div class="timeline-icon">{{ $index + 1 }}</div>
            
            <div class="timeline-content">
                <h3 class="task-title">{{ $d->task }}</h3>
                
                <div class="time-section">
                    <h4 class="time-header">Scheduled</h4>
                    <div class="time-row">
                        <span class="time-label">Start:</span>
                        <span class="time-value">{{ usaToAusTime($d->task_start) }}</span>
                    </div>
                    <div class="time-row">
                        <span class="time-label">End:</span>
                        <span class="time-value">{{ usaToAusTime($d->task_end) }}</span>
                    </div>
                </div>
                
                <div class="time-section">
                    <h4 class="time-header actual">Actual</h4>
                    <div class="time-row">
                        <span class="time-label">Start:</span>
                        <span class="time-value">
                            @if(!empty($d->start_time))
                                {{ date("H:i", strtotime($d->start_time)) }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="time-row">
                        <span class="time-label">End:</span>
                        <span class="time-value">
                            @if(!empty($d->end_time))
                                {{ date("H:i", strtotime($d->end_time)) }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                </div>
                
                <div class="status-bar">
                    <span class="status-label">Status:</span>
                    <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $d->status)) }}">
                        {{ $d->status }}
                    </span>
                </div>
                
                <div class="note-box">
                    <span class="note-label">Note:</span>
                    <span class="note-text">{{ !empty($d->note) ? $d->note : 'No notes provided.' }}</span>
                </div>
                
                @if($d->task_end_imgs != '')
                    @php
                        $images = json_decode(json_decode($d->task_end_imgs, true), true);
                    @endphp
                    
                    @if(is_array($images) && count($images) > 0)
                    <div class="images-section">
                        <span class="images-label">Task Images:</span>
                        <div class="images-grid">
                            @foreach($images as $key => $value)
                            <div class="image-item">
                                <div class="image-box">
                                    <img src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents('https://appapi.subway.thescouts.com.au/public/uploads/' . $value['imgPath'])) }}" 
                                         class="report-image" 
                                         alt="Image {{ $key + 1 }}">
                                </div>
                                <span class="image-timestamp">
                                    {{ isset($value['timestamp']) ? $value['timestamp'] : 'No timestamp' }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </div>
        
        @if(($index + 1) % 3 === 0 && $index + 1 < count($data))
        <div class="page-break"></div>
        @endif
        
        @endforeach
    </div>

    <div class="footer">
        <p>Report generated: {{ date('d/m/Y H:i') }} | &copy; {{ date('Y') }} {{ $business_name }}</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const badges = document.querySelectorAll('.status-badge');
            badges.forEach(el => {
                const text = el.textContent.toLowerCase().trim();
                if (text.includes('complete')) el.classList.add('status-completed');
                else if (text.includes('pending')) el.classList.add('status-pending');
                else if (text.includes('progress')) el.classList.add('status-in-progress');
                else if (text.includes('cancel')) el.classList.add('status-cancelled');
            });
        });
    </script>
</body>
</html>
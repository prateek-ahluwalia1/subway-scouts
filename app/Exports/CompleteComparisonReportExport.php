<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class CompleteComparisonReportExport implements FromView, WithTitle
{
    // Set the sheet title
    public function title(): string
    {
        return 'CompleteComparisonReport';  // Set custom sheet name
    }

    // Return the view data
    public function view(): View
    {
        // Use the full Request object instead of just the data array
        $request = request();  // Get the full Request object
        
        // Pass the Request object to the controller's method
        $data = app('App\Http\Controllers\reports\CompleteComparisonReportController')->getCompleteComparisonReportData($request);
        
        return view('exports.CompleteComparisonReportData', [
            'data' => $data,
        ]);
    }
}

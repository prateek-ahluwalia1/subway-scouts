<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Http\Request;

class SigninoutReport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $request = request()->all(); 
        $data = app('App\Http\Controllers\reports\RosterReport')->signin_out_report($request);
            return view('exports.signin_out_report', [
            'data' => $data
        ]);
        
    }
}

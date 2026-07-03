<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\portal\PortalSettings;
use DB;

class PortalSettingController extends Controller
{
    function updatePortalColors(Request $request)
    {
        $name = 'colors';
        $settings = [
            'background_color' => $request->has('background_color') ? $request->background_color : '',
            'primary_color' => $request->has('primary_color') ? $request->primary_color : '',
            'secondary_color' => $request->has('secondary_color') ? $request->secondary_color : '',
            'pending_shifts' => $request->has('pending_shifts') ? $request->pending_shifts : '',
            'rejected_shifts' => $request->has('rejected_shifts') ? $request->rejected_shifts : '',
            'publish_shifts' => $request->has('publish_shifts') ? $request->publish_shifts : '',
            'unpublish_shifts' => $request->has('unpublish_shifts') ? $request->unpublish_shifts : '',
            'mock_shifts' => $request->has('mock_shifts') ? $request->mock_shifts : '',
            'missed_shifts' => $request->has('missed_shifts') ? $request->missed_shifts : '',
            'uncoverd_shifts' => $request->has('uncoverd_shifts') ? $request->uncoverd_shifts : '',
            'operational_notes_shifts' => $request->has('operational_notes_shifts') ? $request->operational_notes_shifts : '',
            'unpublish_site_shifts' => $request->has('unpublish_site_shifts') ? $request->unpublish_site_shifts : '',
            'completed_shift' => $request->has('completed_shift') ? $request->completed_shift : '',
            'confirmed_shift' => $request->has('confirmed_shift') ? $request->confirmed_shift : '',
        ];
        if (PortalSettings::where('name', $name)->first() != null) {
            $is_done = PortalSettings::where('name', $name)->update(['settings' => json_encode($settings), 'updated_at' => date('Y-m-d H:i:s'), 'logo' => $request->logo]);
        }else{
            $is_done = PortalSettings::insert(['settings' => json_encode($settings), 'name' => $name, 'created_at' => date('Y-m-d H:i:s'), 'logo' => $request->logo]);

        }
        if ($is_done) {
            return response()->json(['message' => "Colors save successfully." ,  'code' => 200, 'success' => true]);
        }

        return response()->json(['message' => "Fail to save colors!" ,  'code' => 404, 'success' => false]);

    }

    public function getColors()
    {
        $getColors = PortalSettings::where('name', 'colors')->first();
        if($getColors){
            $colors = json_decode($getColors);
            return response()->json(['data' => $colors ,  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['data' => '' ,  'code' => 404, 'success' => false]);
        }      
    }

    function getPH(Request $request)
    {
        $getPublicHolidays = DB::table('public_holidays')->where('state', $request->state)->get();
        if ($getPublicHolidays) {
            return response()->json(['success' => true, 'data' => $getPublicHolidays]);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to Get Public Holiday!']);
        }
    }

    function addPH(Request $request)
    {
        // $states_array = array(
        //     'Victoria' => 'vic',
        //     'New South Wales' => 'nsw',
        //     'Queensland' => 'qld',
        //     'Tasmania' => 'tas',
        //     'Western Australia' => 'wa',
        //     'South Australia' => 'sa',
        //     'ACT' => 'act'
        // );
        $state = $request->state;
        $data = array(
            'holiday_name' => $request->holiday_name,
            'date' => date('Ymd', strtotime($request->date)),
            'information' => $request->holiday_information,
            'state' => $state
        );

        $addPublicHolidays = DB::table('public_holidays')->insert($data);
        if ($addPublicHolidays) {
            return response()->json(['success' => true, 'message' => 'Public Holiday add successfully.']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to add Public Holiday!']);
        }
    }

    function updatePH(Request $request)
    {
        $data = [
            'holiday_name' => $request->holiday_name,
            'date' => date('Ymd', strtotime($request->date)),
            'information' => $request->holiday_information,
        ];
        
        $updatedPublicHolidays = DB::table('public_holidays')
            ->where('id', $request->id)
            ->update($data);
        // if ($updatedPublicHolidays) {
            return response()->json(['success' => true, 'message' => 'Public Holiday update successfully.']);
        // }else{
        //     return response()->json(['success' => false, 'message' => 'Fail to update Public Holiday!']);
        // }
    }

    function deletePH(Request $request)
    {
    
        $deletePublicHolidays = DB::table('public_holidays')->where('id', $request->id)->delete();
        if ($deletePublicHolidays) {
            return response()->json(['success' => true, 'message' => 'Public Holiday delete successfully.']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to delete Public Holiday!']);
        }
    }
}

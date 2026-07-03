<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use App\Models\GuardWorkDetail;
use App\Models\Guard;
use Carbon\Carbon;
use \DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\GuardDocument;

class GuardImport implements ToModel, WithHeadingRow
{

    public function model(array $guard)
    {

        if($guard['first_name'] != null)
        {
            if(!empty($guard['expiry']))
            {
            if (strpos($guard['expiry'], '/') !== false) {
                $doc_expiry = DateTime::createFromFormat('d/m/Y', $guard['expiry']);
                
            } else {
                $set_expiry = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($guard['expiry']);
                $doc_expiry = new DateTime('@' . $set_expiry);
                $expiry = $doc_expiry->format('Y-m-d');
                if($expiry  == false)
                {
                $expiry = '1970-01-01';
                }
            }
            }
            if(!empty($guard['dob']))
            {
            if (strpos($guard['dob'], '/') !== false) {
                $guard_dob = DateTime::createFromFormat('d/m/Y', $guard['dob']);
                $dob = $guard_dob->format('Y-m-d');
            } else {
                $guard_dob = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($guard['dob']);
                $gdob = new DateTime('@' . $guard_dob);
                $dob = $gdob->format('Y-m-d');
            }
            }
           
        $imageUrl = $guard['profile_image'];
    
        if (!empty($imageUrl))
        {
        $imageContent = Http::get($imageUrl)->body();
        $destinationPath = 'guard';
        $filename = uniqid('image_') . '.jpg';
        $filename = str_replace("\0", '', $filename);
        Storage::disk('excels')->put($destinationPath . '/' . $filename, $imageContent, 'public');
        $imageUrl = Storage::disk('excels')->url('public/' . $destinationPath . '/' . $filename);
        }

        $customers = fetchCustoemrs();

        $cords = explode(",",$guard['coordinates']);
        if (isset($cords[1]))
        {
        $lat = $cords[0];
        $lng = $cords[1];
        }
        $createNewStaff = new Guard();
        $createNewStaff->first_name = $guard['first_name'];
        $createNewStaff->middle_name = $guard['middle_name'];
        $createNewStaff->last_name = $guard['last_name'];
        $createNewStaff->name = $guard['first_name'].' '. ($guard['middle_name'] ? $guard['middle_name'].' ' :'') .$guard['last_name'];
        $createNewStaff->email = $guard['email'];
        $createNewStaff->phone = $guard['phone_number'];
        $createNewStaff->password = Hash::make(123456);
        $createNewStaff->address = $guard['address'];
        if(!empty($imageUrl))
        {
        $createNewStaff->profile_image = $filename;
        }
        $createNewStaff->guard_type = $guard['employment_type'];
        $createNewStaff->staff_type = $guard['staff_type'];
        $createNewStaff->state = $guard['state'];
        if(!empty($dob))
        {
        $createNewStaff->dob = $dob;
        }
        $createNewStaff->gender =  $guard['gender'];
        $createNewStaff->suburb =  $guard['suburb'];
        $createNewStaff->city =  $guard['city'];
        $createNewStaff->coordinates = $guard['coordinates'];
        if(isset($lat))
        {
        $createNewStaff->latitude =  $lat;
        $createNewStaff->longitude =  $lng;
        }
        $createNewStaff->postal_code =  $guard['postal_code'];
        $createNewStaff->emergency_contact_name =  $guard['emergency_contact_name'];
        $createNewStaff->emergency_contact_relation = $guard['emergency_contact_relation'];
        $createNewStaff->emergency_contact_phone =  $guard['emergency_contact_phone'];
        $createNewStaff->emergency_contact_email = $guard['emergency_contact_email'];
        $createNewStaff->guard_status = 'new';
        $createNewStaff->customer_id = json_encode($customers);
        $createNewStaff->is_email_approved = 'yes';
        $createNewStaff->is_available = 'yes';
        $createNewStaff->admin_approval_status = 'inactive';
        $createNewStaff->annual_leave_hours = $guard['annual_leave_in_hours'];
        $createNewStaff->sick_leave_hours = $guard['sick_leave_in_hours'];
        $createNewStaff->save();

        $guard_documents = new GuardDocument();
        $guard_documents->guard_id = $createNewStaff->id;
        $guard_documents->document_category = 'student_visa';
        $guard_documents->document_type = 'security_license';
        $guard_documents->document_name = 'Security License';
        $guard_documents->document_no = $guard['security_licence'];
        if(!empty($expiry))
        {
        $guard_documents->document_expire = $expiry;
        }
        $guard_documents->is_deleteable = 0;
        $guard_documents->save();

        $guard_documents = new GuardDocument();
        $guard_documents->guard_id = $createNewStaff->id;
        $guard_documents->document_category = 'student_visa';
        $guard_documents->document_type = 'passport';
        $guard_documents->document_name = 'Passport';
        $guard_documents->document_no = '';
        if(!empty($expiry))
        {
        $guard_documents->document_expire = '';
        }
        $guard_documents->is_deleteable = 0;
        $guard_documents->save();

        $guard_documents = new GuardDocument();
        $guard_documents->guard_id = $createNewStaff->id;
        $guard_documents->document_category = 'student_visa';
        $guard_documents->document_type = 'visa';
        $guard_documents->document_name = 'Visa';
        $guard_documents->document_no = '';
        if(!empty($expiry))
        {
        $guard_documents->document_expire = '';
        }
        $guard_documents->is_deleteable = 0;
        $guard_documents->save();

        $guard_documents = new GuardDocument();
        $guard_documents->guard_id = $createNewStaff->id;
        $guard_documents->document_category = 'student_visa';
        $guard_documents->document_type = 'vaccination';
        $guard_documents->document_name = 'Vaccination';
        $guard_documents->document_no = '';
        if(!empty($expiry))
        {
        $guard_documents->document_expire = '';
        }
        $guard_documents->is_deleteable = 0;
        $guard_documents->save();

        $guard_documents = new GuardDocument();
        $guard_documents->guard_id = $createNewStaff->id;
        $guard_documents->document_category = 'student_visa';
        $guard_documents->document_type = 'driver_license_front';
        $guard_documents->document_name = 'Driver License Front';
        $guard_documents->document_no = '';
        if(!empty($expiry))
        {
        $guard_documents->document_expire = '';
        }
        $guard_documents->is_deleteable = 0;
        $guard_documents->save();

        $guard_documents = new GuardDocument();
        $guard_documents->guard_id = $createNewStaff->id;
        $guard_documents->document_category = 'student_visa';
        $guard_documents->document_type = 'driver_license_back';
        $guard_documents->document_name = 'Driver License Back';
        $guard_documents->document_no = '';
        if(!empty($expiry))
        {
        $guard_documents->document_expire = '';
        }
        $guard_documents->is_deleteable = 0;
        $guard_documents->save();

        $SaveDetails = new GuardWorkDetail();
        $SaveDetails->guard_id = $createNewStaff->id;
        $SaveDetails->guard_document_type = 'student_visa';
        $SaveDetails->save();
       

        }
     
    }

}
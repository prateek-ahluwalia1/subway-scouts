<?php

namespace Database\Seeders;

use Carbon\Traits\Timestamp;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('form_templates')->insert([
            'title' => 'Check List Details',
            'body' => json_encode('<div class="w-100 mt-3 pe-3 d-flex justify-content-end">
            <mat-icon role="button" (click)="close()">close</mat-icon></div>
        <div class = "wrap_form" #content>
            <div class = "form_border">
                <div class = "main">
                    <img src="assets/images/logo/scouts.png" style="height: 130px; width: 150px; margin-bottom: 60px;">
                    <div class = "row">
                        <u style="color: #00A37E;"><h2 class = "row_heading">Employment Pack Checklist</h2></u>
                    </div>
                    <div class = "row" style="margin-top: 25px;">
                        <div class = "col-3" style="text-align: right;">1.</div>
                        <div class = "col-5">
                            <div class = "row">
                                <label class="form-check-label" for="flexCheckDefault" style="font-weight: 400;">Employment Form Filled</label>
                            </div>
                        </div>
                        <div class = "col-4">
                            <div class = "row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class = "row" style="margin-top: 25px;">
                        <div class = "col-3" style="text-align: right;">2.</div>
                        <div class = "col-5">
                            <div class = "row">
                                <label class="form-check-label" for="flexCheckDefault" style="font-weight: 400;">TFN Form Filled</label>
                            </div>
                        </div>
                        <div class = "col-4">
                            <div class = "row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class = "row" style="margin-top: 25px;">
                        <div class = "col-3" style="text-align: right;">3.</div>
                        <div class = "col-5">
                            <div class = "row">
                                <label class="form-check-label" for="flexCheckDefault" style="font-weight: 400;">Super Form Filled</label>
                            </div>
                        </div>
                        <div class = "col-4">
                            <div class = "row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class = "row" style="margin-top: 25px;">
                        <div class = "col-3" style="text-align: right;">4.</div>
                        <div class = "col-5">
                            <div class = "row">
                                <label class="form-check-label" for="flexCheckDefault" style="font-weight: 400;">Copy of Passport or Birth Certificate</label>
                            </div>
                        </div>
                        <div class = "col-4">
                            <div class = "row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class = "row" style="margin-top: 25px;">
                        <div class = "col-3" style="text-align: right;">5.</div>
                        <div class = "col-5">
                            <div class = "row">
                                <label class="form-check-label" for="flexCheckDefault" style="font-weight: 400;">Copy of the Current Victoria Security License</label>
                            </div>
                        </div>
                        <div class = "col-4">
                            <div class = "row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class = "row" style="margin-top: 25px;">
                        <div class = "col-3" style="text-align: right;">6.</div>
                        <div class = "col-5">
                            <div class = "row">
                                <label class="form-check-label" for="flexCheckDefault" style="font-weight: 400;">Copy of the Security Certificate</label>
                            </div>
                        </div>
                        <div class = "col-4">
                            <div class = "row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class = "row" style="margin-top: 25px;">
                        <div class = "col-3" style="text-align: right;">7.</div>
                        <div class = "col-5">
                            <div class = "row">
                                <label class="form-check-label" for="flexCheckDefault" style="font-weight: 400;">Add a copy of the Visa(if applicable)</label>
                            </div>
                        </div>
                        <div class = "col-4">
                            <div class = "row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class = "row" style="margin-top: 25px;">
                        <div class = "col-3" style="text-align: right;">8.</div>
                        <div class = "col-5">
                            <div class = "row">
                                <label class="form-check-label" for="flexCheckDefault" style="font-weight: 400;">Copy of the Current First Aid & RSA Certificate</label>
                            </div>
                        </div>
                        <div class = "col-4">
                            <div class = "row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class = "row" style="margin-top: 25px;">
                        <div class = "col-3" style="text-align: right;">9.</div>
                        <div class = "col-5">
                            <div class = "row">
                                <label class="form-check-label" for="flexCheckDefault" style="font-weight: 400;">Copy of Most Recent CV/Resume</label>
                            </div>
                        </div>
                        <div class = "col-4">
                            <div class = "row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class = "row" style="margin-top: 25px;">
                        <div class = "col-3" style="text-align: right;">10.</div>
                        <div class = "col-5">
                            <div class = "row">
                                <label class="form-check-label" for="flexCheckDefault" style="font-weight: 400;">Copy of Driver License</label>
                            </div>
                        </div>
                        <div class = "col-4">
                            <div class = "row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                </div>
                            </div>
                        </div>
                    </div>
                  
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-success" (click)="makePdf()" >Save pdf</button>
        </div>'),
            'type' => 'Standard',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]);
    }
}

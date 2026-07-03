<div class="scroll" style="padding: 1rem;">
    

    <div>
        <h1 style="color: #00a37e; text-align: center; padding: 10px 0;">New Employment/ Contractor Details</h1>
    </div>

    <div style="background-color: #00a37e;">
         <h3 style="color: #fff; text-align: center; padding: 10px 0;">Contact Details</h3>
    </div>

    <div style="margin: 10px 0px 20px 0px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
       <!-- Left Column -->
       <div style="flex: 1; padding: 0 10px;">
          <div style="font-weight: bold; margin-bottom: 15px;">Surname: <span style="font-weight: normal;">{{ @$data['sr_name'] }}</span></div>
          <div style="font-weight: bold; margin-bottom: 15px;">Home Phone: <span style="font-weight: normal;">{{ @$data['home_phone'] }}</span></div>
       </div>

       <!-- Middle Column -->
       <div style="flex: 1; padding: 0 10px;">
          <div style="font-weight: bold; margin-bottom: 15px;">Given Name: <span style="font-weight: normal;">{{ @$data['name'] }}</span></div>
          <div style="font-weight: bold; margin-bottom: 15px;">Mobile: <span style="font-weight: normal;">{{ @$data['phone'] }}</span></div>
       </div>

       <!-- Right Column -->
       <div style="flex: 1; padding: 0 10px;">
          <div style="font-weight: bold; margin-bottom: 15px;">Dob: <span style="font-weight: normal;">{{ @$data['dob'] }}</span></div>
          <div style="font-weight: bold; margin-bottom: 15px;">Email Address: <span style="font-weight: normal;">{{ @$data['email'] }}</span></div>
       </div>
   </div>

   <div style="background-color: #00a37e;">
       <h3 style="color: #fff; text-align: center; padding: 10px 0;">Particulars</h3>
   </div>

   <div style="margin: 10px 0px 20px 0px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
     <!-- Left Column -->
     <div style="flex: 1; padding: 0 10px;">
        <div style="font-weight: bold; margin-bottom: 15px;">Tax File Number(TFN): <span style="font-weight: normal;">{{ @$data['tfn_file_no'] }}</span></div>
        <div style="font-weight: bold; margin-bottom: 15px;">GST Status: <span style="font-weight: normal;">{{ @$data['gst'] }}</span></div>
     </div>

     <!-- Middle Column -->
     <div style="flex: 1; padding: 0 10px;">
        <div style="font-weight: bold; margin-bottom: 15px;">Australian Business Number(ABN): <span style="font-weight: normal;">{{ @$data['abn_no'] }}</span></div>
        <div style="font-weight: bold; margin-bottom: 15px;">Day of Commencement: <span style="font-weight: normal;">{{ @$data['day_of_commencement'] }}</span></div>
     </div>

     <!-- Right Column -->
     <div style="flex: 1; padding: 0 10px;">
        <div style="font-weight: bold; margin-bottom: 15px;">ABN Type: <span style="font-weight: normal;">{{ @$data['abn_type'][0]->name }}</span></div>
        <div style="font-weight: bold; margin-bottom: 15px; color:#fff;">No Record:<span style="font-weight: normal;"></span></div>
     </div>
  </div>

  <div style="background-color: #00a37e;">
     <h3 style="color: #fff; text-align: center; padding: 10px 0;">Personal Address</h3>
  </div>

  <div style="margin: 10px 0px 20px 0px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
     <!-- Left Column -->
     <div style="flex: 1; padding: 0 10px;">
        <div style="font-weight: bold; margin-bottom: 15px;">Residential Address: <span style="font-weight: normal;">{{ @$data['residential_address'] }}</span></div>
            <div style="font-weight: bold; margin-bottom: 15px;">Residency Status: <span style="font-weight: normal;">{{ @$data['guard_document_type'] }}</span></div>
        <div style="font-weight: bold; margin-bottom: 15px;">Other Qualifications: <span style="font-weight: normal;">other_qualification</span></div>
        <div style="font-weight: bold; margin-bottom: 15px;">Availability days: <span style="font-weight: normal;">Monday, Tuesday, Wednesday</span></div>
     </div>

     <!-- Middle Column -->
     <div style="flex: 1; padding: 0 10px;">
        <div style="font-weight: bold; margin-bottom: 15px;">State: <span style="font-weight: normal;">{{ @$data['state'] }}</span></div>
        <div style="font-weight: bold; margin-bottom: 15px; margin-top: 37px;">Sec.Lic.No: <span style="font-weight: normal;">{{ @$data['sec_lic_no'] }}</span></div>
        <div style="font-weight: bold; margin-bottom: 15px;">Have Car?: <span style="font-weight: normal;">{{ !empty($data['car']) ? $data['car'] : 'No' }}</span></div>
     </div>

     <!-- Right Column -->
     <div style="flex: 1; padding: 0 10px;">
        <div style="font-weight: bold; margin-bottom: 15px;">Postal Code: <span style="font-weight: normal;">{{ @$data['postal_code'] }}</span></div>
        <div style="font-weight: bold; margin-bottom: 15px; margin-top: 37px;">Expiry Date:<span style="font-weight: normal;">{{ @$data['sec_lic_exp'] }}</span></div>
        <div style="font-weight: bold; margin-bottom: 15px;">Car Registration:<span style="font-weight: normal;">{{ @$data['car_reg'] }}</span></div>
        <div style="font-weight: bold; margin-bottom: 15px; color:#fff;">Have Car?: <span style="font-weight: normal;"></span></div>
     </div>
  </div>

   <div style="background-color: #00a37e;">
       <h3 style="color: #fff; text-align: center; padding: 10px 0;">Emergency Contact Details</h3>
   </div>

   <div style="margin: 10px 0px 20px 0px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
     <!-- Left Column -->
     <div style="flex: 1; padding: 0 10px;">
        <div style="font-weight: bold; margin-bottom: 15px;">Name: <span style="font-weight: normal;">{{ @$data['emergency_contact_name'] }}</span></div>
     </div>

     <!-- Middle Column -->
     <div style="flex: 1; padding: 0 10px;">
        <div style="font-weight: bold; margin-bottom: 15px;">Contact No: <span style="font-weight: normal;">{{ @$data['emergency_contact_phone'] }}</span></div>
     </div>

     <!-- Right Column -->
     <div style="flex: 1; padding: 0 10px;">
        <div style="font-weight: bold; margin-bottom: 15px;">Relationship: <span style="font-weight: normal;">{{ @$data['relationship'] }}</span></div>
     </div>
  </div>

  <div style="background-color: #00a37e;">
      <h3 style="color: #fff; text-align: center; padding: 10px 0;">Financial Institution Details</h3>
  </div>

  <div style="margin: 10px 0px 20px 0px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
    <!-- Left Column -->
    <div style="flex: 1; padding: 0 10px;">
       <div style="font-weight: bold; margin-bottom: 15px;">Bank Name: <span style="font-weight: normal;">{{ @$data['bank_name'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">Account No: <span style="font-weight: normal;">{{ @$data['account_no'] }}</span></div>
    </div>

    <!-- Middle Column -->
    <div style="flex: 1; padding: 0 10px;">
       <div style="font-weight: bold; margin-bottom: 15px;">Account Type: <span style="font-weight: normal;">{{ @$data['account_type'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">Super Fund Name: <span style="font-weight: normal;">{{ @$data['super_fund_name'] }}</span></div>
    </div>

    <!-- Right Column -->
    <div style="flex: 1; padding: 0 10px;">
       <div style="font-weight: bold; margin-bottom: 15px;">BSB: <span style="font-weight: normal;">{{ @$data['bsb'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">Superannuation Membership Number: <span style="font-weight: normal;">{{ @$data['superannuation_Membership'] }}</span></div>
    </div>
  </div>

  <div style="background-color: #00a37e;">
      <h3 style="color: #fff; text-align: center; padding: 10px 0;">Criminal History</h3>
  </div>

  <div style="margin: 10px 0px 20px 0px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
    <p style="padding-left: 10px;">If you answer YES to any of these questions, please discuss them before continuing with this form.
                        Have you ever been convicted of any criminal offence in Australia or overseas? This does not include
                        traffic /parking fines but does include drunk driving offences. YES/ NO
                        If YES, please describe the details: -</p>
        <!-- Left Column -->
    @if(isset($data['criminal_history']) && count($data['criminal_history']) > 0)
        @foreach($data['criminal_history'] as $criminalRecord)
            <!-- Left Column -->
            <div style="flex: 1; padding: 0 10px;">
                <div style="font-weight: bold; margin-bottom: 15px;">Offence: <span style="font-weight: normal;">{{ @$criminalRecord->offence }}</span></div>
            </div>

            <!-- Right Column -->
            <div style="flex: 1; padding: 0 10px;">
                <div style="font-weight: bold; margin-bottom: 15px;">Results: <span style="font-weight: normal;">{{ @$criminalRecord->result }}</span></div>
            </div>
        @endforeach
    @endif

  </div>

  <div style="background-color: #00a37e;">
     <h3 style="color: #fff; text-align: center; padding: 10px 0;">References Forms</h3>
  </div>

  <div style="margin: 10px 0px 20px 0px; display: flex; flex-wrap: wrap;">
    <p style="padding-left: 10px;">Do you have any pending charges or are you currently under investigation for any criminal offences
                        in Australia or overseas? Does not include traffic offences but does include drunk driving offences.
                        YES/NO. <br> 
                        <span> If YES, please describe: jdsbjksbjcbdshcbsbcjhsbcjhs </span><br>
                        <span style="font-size:20px; font-weight:600;">Health:</span><br>
                        <span>If you answer YES to any of the below questions, please provide details in the   blank space provided including dates of injuries and attention required.</span> 
    </p>

    <p style="padding-left: 10px;">
      1.Have you ever suffered from, and physical disability eq. slipped disc, hernia etc? <b>{{ @$data['phy_dis']}}</b> <br>
      2.Do you suffer from epilepsy or any nervous system disorder? <b>{{ @$data['ner_dis'] }}</b> <br>
      3.Have you ever suffered from bronchitis, asthma, or diabetes? <b>{{ @$data['bron_dis']  }}</b> <br>
      4.Have you ever been hospitalised for a medical condition? <b>{{ @$data['med_cond'] }}</b> <br>
      5.Have you ever claimed compensation for any work-related injury or illness? <b>{{ @$data['work_inj'] }}</b> <br>
      6.Do you smoke? <b>{{ @$data['smoke'] }}</b>
    </p>
  </div>

  <div style="background-color: #00a37e;">
      <h3 style="color: #fff; text-align: center; padding: 10px 0;">References Details</h3>
  </div>

  <div style="margin: 10px 0px 20px 0px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
    <p style="padding-left: 10px;">A company requirement and insurance condition require that satisfactory work references be obtained for up to
                    a minimum of 5 years. You must supply 2 business and 1 personal reference with all contact details. Family and
                    relatives are not acceptable as work history references.<br>
                    <span style="font-weight: 600; font-size:20px;">Business / Work History References:</span>
    </p>
    <!-- Left Column -->
    <div style="flex: 1; padding: 0 10px;">
       <div style="font-weight: bold; margin-bottom: 15px;">Employer Company Name: <span style="font-weight: normal;">{{ @$data['comp_name_one'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">From: <span style="font-weight: normal;">{{ @$data['first_comp_joining_date'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">Duties: <span style="font-weight: normal;">{{ @$data['duties_one'] }}</span></div>

       <div style="font-weight: bold; margin-bottom: 15px;">Employer Company Name: <span style="font-weight: normal;">{{ @$data['comp_name_two'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">From: <span style="font-weight: normal;">{{ @$data['second_comp_joining_date'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">Duties: <span style="font-weight: normal;">{{ @$data['duties_two'] }}</span></div>
    </div>

    <!-- Middle Column -->
    <div style="flex: 1; padding: 0 10px;">
       <div style="font-weight: bold; margin-bottom: 15px;">Contact Person:<span style="font-weight: normal;">{{ @$data['contact_per_one'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">To:<span style="font-weight: normal;">{{ @$data['first_comp_ending_date'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px; color: #fff">hsshb<span style="font-weight: normal;"></span></div>

       <div style="font-weight: bold; margin-bottom: 15px;">Contact Person:<span style="font-weight: normal;">{{ @$data['contact_per_two'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">To:<span style="font-weight: normal;">{{ @$data['second_comp_ending_date'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px; color: #fff">hsshb<span style="font-weight: normal;"></span></div>

    </div>

    <!-- Right Column -->
    <div style="flex: 1; padding: 0 10px;">
       <div style="font-weight: bold; margin-bottom: 15px;">Phone: <span style="font-weight: normal;">{{ @$data['first_comp_phone'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">Position: <span style="font-weight: normal;">{{ @$data['pos_in_first_comp'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px; color: #fff">sbxhsbh<span style="font-weight: normal;"></span></div>

       <div style="font-weight: bold; margin-bottom: 15px;">Phone: <span style="font-weight: normal;">{{ @$data['second_comp_phone'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">Position: <span style="font-weight: normal;">{{ @$data['pos_in_second_comp'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px; color: #fff">sbxhsbh<span style="font-weight: normal;"></span></div>
    </div>
  </div>


   <div style="background-color: #00a37e;">
      <h3 style="color: #fff; text-align: center; padding: 10px 0;">Personal References Form</h3>
  </div>

  <div style="margin: 10px 0px 20px 0px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
    <!-- Left Column -->
    <div style="flex: 1; padding: 0 10px;">
       <div style="font-weight: bold; margin-bottom: 15px;">Name: <span style="font-weight: normal;">{{ @$data['ref_name'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">Print Full Name: <span style="font-weight: normal;">{{ @$data['ref_fullname'] }}</span></div>
    </div>

    <!-- Middle Column -->
    <div style="flex: 1; padding: 0 10px;">
       <div style="font-weight: bold; margin-bottom: 15px;">Relationship: <span style="font-weight: normal;">{{ @$data['ref_relationship'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px;">Checked and Interviewed by: <span style="font-weight: normal;">{{ @$data['interview_by'] }}</span></div>
    </div>

    <!-- Right Column -->
    <div style="flex: 1; padding: 0 10px;">
       <div style="font-weight: bold; margin-bottom: 15px;">Contact Number: <span style="font-weight: normal;">{{ @$data['pr_cont_ref_no'] }}</span></div>
       <div style="font-weight: bold; margin-bottom: 15px; color: #fff;">Superannuation Membership Number: <span style="font-weight: normal;"></span></div>
    </div>
  </div>
  
    <div style="background-color: #00a37e;">
      <h3 style="color: #fff; text-align: center; padding: 10px 0;">Before Submission</h3>
    </div>

    <div style="margin: 10px 0px 20px 0px; display: flex; justify-content: center; align-items: center; flex-wrap: wrap;">
        <label class="mt-2">Applicant’s signature:</label>
        @php
            $image = !empty($data['sign']) ? str_replace('https://apis.thescouts.com.au/', '', $data['sign']) : null;
        @endphp

        @if (!empty($image))
            <img height="100" src="{{ 'data:image/jpg;base64,' . base64_encode(file_get_contents(asset($image))) }}">
        @else
            <p>No sign.</p>
        @endif
    </div>

 
</div>
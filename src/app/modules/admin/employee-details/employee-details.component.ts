import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
import { NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormControl, Validators } from '@angular/forms';
import { GlobalVariable } from 'app/shared/global';
import { FormBuildService } from 'app/services/form-build.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { SignaturePad } from 'angular2-signaturepad';
import * as html2pdf from 'html2pdf.js';
import { ActivatedRoute } from '@angular/router';
import { FormsPopupMsgComponent } from '../models/forms-popup-msg/forms-popup-msg.component';
import { TDocumentDefinitions } from "pdfmake/interfaces";
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
pdfMake.vfs = pdfFonts.pdfMake.vfs;

@Component({
  selector: 'app-employee-details',
  templateUrl: './employee-details.component.html',
  styleUrls: ['./employee-details.component.scss']
})
export class EmployeeDetailsComponent implements OnInit {

  @ViewChild('modalContent') content: ElementRef;
  @Input() fromParent;
  @ViewChild('dateInput') dateInput: ElementRef;
  private currentModal: NgbModalRef;

  signatureImg: string;
  @ViewChild(SignaturePad) signaturePad: SignaturePad;
  signaturePadOptions: Object = {
    'minWidth': 2,
    'canvasWidth': 920,
    'canvasHeight': 300,
  };

  AddDetails
  selectedindex: number
  selectedindexr: number
  active: number;
  selectedAbn: any[] = [];
  selectedQualification: any[] = [];
  selectedDay: any[] = [];
  staffId;
  businessId;

  constructor(private fb: FormBuilder, private formservice: FormBuildService, private toast: ToastServiceService,
    public global: GlobalVariable, private modalService: NgbModal, private route: ActivatedRoute) { }

  ngOnInit(): void {

    // const businessDataString = localStorage.getItem('business');
    // const businessData = JSON.parse(businessDataString);
    // this.businessId = businessData.id;
    // console.log("business data", this.businessId)

    console.log("fromParent", this.fromParent)

    this.route.queryParams.subscribe(params => {
      console.log("params", params);
      this.staffId = params['guard_id'];
      this.businessId = params['busi_id'];
      console.log("Staff Id from url", this.staffId, this.businessId);

    });

    if (this.staffId) {
      let data = {
        guard_id: this.staffId,
        form_id: "employee-details",
      }
      // this.formservice.StoreEmpForm(data, this.businessId).subscribe(res => {
      //   if (res.success) {
      //     this.toast.toastNotification('Form submitted successfully:', 'Form Stored!');
      //     this.close();
      //   }
      // })
      this.formservice.GetEmpClicks(data, this.businessId).subscribe(res => {
        if (res.success === true) {
          this.getEmployeData(this.staffId);
        }
        else {
          console.log("Form already submitted")
          this.currentModal = this.modalService.open(FormsPopupMsgComponent, { centered: true, backdrop: 'static' });
        }
      })
    }
    else {
      this.getEmployeData(this.fromParent);
    }

    this.AddDetails = this.fb.group({
      surname: new FormControl('', Validators.required),
      given_name: new FormControl('', Validators.required),
      date_of_birth: new FormControl('', Validators.required),
      home_phone: new FormControl('', Validators.required),
      mobile: new FormControl('', Validators.required),
      email: new FormControl('', Validators.required),
      TFN: new FormControl('', Validators.required),
      ABN: new FormControl('', Validators.required),
      Abn_type: new FormControl('', Validators.required),
      gst_status: new FormControl('', Validators.required),
      commencement_of_work: new FormControl('', Validators.required),
      address: new FormControl('', Validators.required),
      state: new FormControl('', Validators.required),
      pcode: new FormControl('', Validators.required),
      res_status: new FormControl('', Validators.required),
      other: new FormControl('', Validators.required),
      sec_lic: new FormControl(''),
      exp_date: new FormControl(''),
      other_qua: new FormControl('', Validators.required),
      car: new FormControl('', Validators.required),
      car_reg: new FormControl('', Validators.required),
      days: new FormControl('', Validators.required),
      issue_date: new FormControl('', Validators.required),
      return_date: new FormControl('', Validators.required),
      note: new FormControl('', Validators.required),
      emergency_name: new FormControl('', Validators.required),
      emergency_contact: new FormControl('', Validators.required),
      relation: new FormControl('', Validators.required),
      bank_name: new FormControl('', Validators.required),
      accountType: new FormControl('', Validators.required),
      bsb: new FormControl('', Validators.required),
      acc_no: new FormControl('', Validators.required),
      fund_name: new FormControl('', Validators.required),
      superannuation: new FormControl('', Validators.required),
      offence_one: new FormControl('', Validators.required),
      result_one: new FormControl('', Validators.required),
      offence_two: new FormControl('', Validators.required),
      result_two: new FormControl('', Validators.required),
      comp_name_one: new FormControl('', Validators.required),
      contact_per_one: new FormControl('', Validators.required),
      phone_one: new FormControl('', Validators.required),
      from_date_one: new FormControl('', Validators.required),
      to_date_one: new FormControl('', Validators.required),
      position_one: new FormControl('', Validators.required),
      duties_one: new FormControl('', Validators.required),
      comp_name_two: new FormControl('', Validators.required),
      contact_per_two: new FormControl('', Validators.required),
      phone_two: new FormControl('', Validators.required),
      from_date_two: new FormControl('', Validators.required),
      to_date_two: new FormControl('', Validators.required),
      position_two: new FormControl('', Validators.required),
      duties_two: new FormControl('', Validators.required),
      ref_name: new FormControl('', Validators.required),
      relationship: new FormControl('', Validators.required),
      contact: new FormControl('', Validators.required),
      ref_fullname: new FormControl('', Validators.required),
      ref_date: new FormControl('', Validators.required),
      checkedBy: new FormControl('', Validators.required),
      sign: new FormControl('', Validators.required),
    })

  }

  ngAfterViewInit() {
    this.AddDetails.controls.date_of_birth.valueChanges.subscribe((newDate: string) => {
      if (newDate) {
        const dateParts = newDate.split('-');
        const formattedDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;

        console.log("Date of birth", formattedDate)
        this.dateInput.nativeElement.value = formattedDate;
      }
    });
  }

  statuses = [{ name: "Student Visa", value: "student_visa" },
  { name: "Bridging Visa", value: "bridging_visa" },
  { name: "Citizen", value: "citizen" },
  { name: "Permanent Residence", value: "permanent_resident" },
  { name: "Visa Subclass 485", value: "visa_subclass_485" },
  { name: "Other", value: "other" },];

  registrations = [{ "name": "Register" }, { "name": "Not Register" }];
  abns = [
    { id: 1, name: "Sole Trader" },
    { id: 2, name: "Partnership" },
    { id: 3, name: "Trust" },
    { id: 4, name: "Company" },
  ];

  otherQualifications = [
    { id: 11, name: "First Aid" },
    { id: 12, name: "Rsa" },
    { id: 13, name: "Fire Arms" },
    { id: 14, name: "Crowd Control" },
    { id: 15, name: "Alarm Installation" },
  ];
  days = [
    { id: 16, name: "Monday" },
    { id: 17, name: "Tuesday" },
    { id: 18, name: "Wednesday" },
    { id: 19, name: "Thursday" },
    { id: 20, name: "Friday" },
    { id: 21, name: "Saturday" },
    { id: 22, name: "Sunday" },
  ];
  cars = [
    { name: "Yes", value: "yes" },
    { name: "No", value: "no" },
  ]


  seti(index: any) {
    this.selectedindex = index;
    const selectedStatus = this.statuses[index].value;
    this.AddDetails.get('res_status')?.setValue(selectedStatus);
  }

  setir(index: any) {
    this.selectedindexr = index;
    const selectedGSTStatus = this.registrations[index].name;
    this.AddDetails.get('gst_status')?.setValue(selectedGSTStatus);
  }

  selectDiv(item: any) {
    const div = document.getElementById(item.id);

    if (div.classList.contains('selected')) {
      div.classList.remove('selected');

      if (this.selectedAbn.length > 0) {
        const index = this.selectedAbn.findIndex((element) => {
          if (element.name === item.name) {
            return true;
          }
        });
        if (index !== -1) {
          this.selectedAbn.splice(index, 1);
        }
      }
    }

    else {
      div.classList.add('selected');
      this.selectedAbn.push(item);
    }
    this.AddDetails.get('Abn_type')?.setValue(this.selectedAbn);
    // this.AddDetails.get('Abn_type')?.setValue(this.selectedAbn.map((abn) => abn.name).join(', '));
  }

  isSelected(abn: any): boolean {
    return this.selectedAbn.some((selected) => selected.name === abn.name);
  }

  selectedDiv(item: any) {

    const div = document.getElementById(item.id);

    if (div.classList.contains('selected')) {
      div.classList.remove('selected');

      if (this.selectedQualification.length > 0) {
        const index = this.selectedQualification.findIndex((element) => {

          if (element.name === item.name) {

            return true;
          }

        });
        if (index !== -1) {
          this.selectedQualification.splice(index, 1);
        }

      }
    }

    else {
      div.classList.add('selected');
      this.selectedQualification.push(item)
    }
    this.AddDetails.get('other_qua')?.setValue(this.selectedQualification);
    // this.AddDetails.get('other_qua')?.setValue(this.selectedQualification.map((qua) => qua.name).join(', '));
  }

  quaSelected(qua: any): boolean {
    return this.selectedQualification.some((selected) => selected.name === qua.name);
  }

  setcar(index: any) {
    this.active = index;
    const selectedCar = this.cars[index].value;
    this.AddDetails.get('car')?.setValue(selectedCar);
  }

  selectedday(item: any) {

    const div = document.getElementById(item.id);

    if (div.classList.contains('selected')) {
      div.classList.remove('selected');

      if (this.selectedDay.length > 0) {
        const index = this.selectedDay.findIndex((element) => {


          if (element.name === item.name) {

            return true;
          }

        });
        if (index !== -1) {
          this.selectedDay.splice(index, 1);
        }

      }
    }

    else {
      div.classList.add('selected');
      this.selectedDay.push(item);
    }

    this.AddDetails.get('days')?.setValue(this.selectedDay);
    // this.AddDetails.get('days')?.setValue(this.selectedDay.map((day) => day.name).join(', '));

  }

  daySelected(day: any): boolean {
    return this.selectedDay.some((selected) => selected.name === day.name);
  }

  uniformTypes = [
    { name: "Dress Shirt", size: '', Qty: '', status: false },
    { name: "Slacks", size: '', Qty: '', status: false },
    { name: "Security Tag", size: '', Qty: '', status: false },
    { name: "Vest", size: '', Qty: '', status: false },
    { name: "T.Shirt", size: '', Qty: '', status: false },
    { name: "Polo", size: '', Qty: '', status: false },
    { name: "Scrub", size: '', Qty: '', status: false },
    { name: "Jacket", size: '', Qty: '', status: false },
    { name: "Tie", size: '', Qty: '', status: false },
  ];

  accTypes = [
    { name: "Savings" },
    { name: "Cheque" },
    { name: "Credit Union" },
  ]

  selectedindexacc: number;
  setacc(index: any) {
    this.selectedindexacc = index;
    this.AddDetails.get('accountType')?.setValue(this.accTypes[index].name);
  }

  options1 = [{ name: "Yes", value: "yes" }, { name: "No", value: "no" },]
  options2 = [{ name: "Yes", value: "yes" }, { name: "No", value: "no" },]
  options3 = [{ name: "Yes", value: "yes" }, { name: "No", value: "no" },]
  options4 = [{ name: "Yes", value: "yes" }, { name: "No", value: "no" },]
  options5 = [{ name: "Yes", value: "yes" }, { name: "No", value: "no" },]
  options6 = [{ name: "Yes", value: "yes" }, { name: "No", value: "no" },]

  active1: number;
  active2: number;
  active3: number;
  active4: number;
  active5: number;
  active6: number;

  option1
  option2
  option3
  option4
  option5
  option6

  setcar1(index: any) {
    this.active1 = index;
    this.option1 = this.options1[index].value;
  }

  setcar2(index: any) {
    this.active2 = index;
    this.option2 = this.options2[index].value;
  }

  setcar3(index: any) {
    this.active3 = index;
    this.option3 = this.options3[index].value;
  }

  setcar4(index: any) {
    this.active4 = index;
    this.option4 = this.options4[index].value;
  }

  setcar5(index: any) {
    this.active5 = index;
    this.option5 = this.options5[index].value;
  }

  setcar6(index: any) {
    this.active6 = index;
    this.option6 = this.options6[index].value;
  }

  describeFormControl: FormControl = new FormControl('');

  element
  options
  createPdf() {
    // const content: HTMLElement = this.content.nativeElement;

    // html2canvas(content).then((canvas) => {
    //   const imgData = canvas.toDataURL('image/png');
    //   const pdf = new jsPDF({
    //     orientation: 'portrait',
    //     unit: 'mm',
    //     format: 'a4',
    //     compress: true
    //   });

    //   const imgProps = pdf.getImageProperties(imgData);
    //   const pdfWidth = pdf.internal.pageSize.getWidth();
    //   const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
    //   pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
    //   pdf.save('download.pdf');
    // });

    this.element = document.getElementById('employment-pdf');
    this.options = {
      filename: 'Employment.pdf',
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2 },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().from(this.element).set(this.options).save();
  }


  // close() {
  //   this.modalService.dismiss();
  // }

  Submit() {
    let data = {
      id: this.staffId,
      form_id: "employee-details",
      sr_name: this.AddDetails.get('surname')?.value,
      name: this.AddDetails.get('given_name')?.value,
      dob: this.AddDetails.get('date_of_birth')?.value,
      home_phone: this.AddDetails.get('home_phone')?.value,
      phone: this.AddDetails.get('mobile')?.value,
      email: this.AddDetails.get('email')?.value,
      tfn_file_no: this.AddDetails.get('TFN')?.value,
      abn_no: this.AddDetails.get('ABN')?.value,
      abn_type: this.AddDetails.get('Abn_type')?.value,
      gst: this.AddDetails.get('gst_status')?.value,
      day_of_commencement: this.AddDetails.get('commencement_of_work')?.value,
      residential_address: this.AddDetails.get('address')?.value,
      state: this.AddDetails.get('state')?.value,
      postal_code: this.AddDetails.get('pcode')?.value,
      guard_document_type: this.AddDetails.get('res_status')?.value,
      other_document: this.AddDetails.get('other')?.value,
      sec_lic_no: this.AddDetails.get('sec_lic')?.value,
      sec_lic_exp: this.AddDetails.get('exp_date')?.value,
      other_qualification: this.AddDetails.get('other_qua')?.value,
      car: this.AddDetails.get('car')?.value,
      car_reg: this.AddDetails.get('car_reg')?.value,
      days: this.AddDetails.get('days')?.value,
      date_of_issue: this.AddDetails.get('issue_date')?.value,
      date_of_return: this.AddDetails.get('return_date')?.value,
      // uniform_type: this.uniformTypes,
      notes: this.AddDetails.get('note')?.value,
      emergency_contact_name: this.AddDetails.get('emergency_name')?.value,
      emergency_contact_phone: this.AddDetails.get('emergency_contact')?.value,
      relationship: this.AddDetails.get('relation')?.value,
      bank_name: this.AddDetails.get('bank_name')?.value,
      account_type: this.AddDetails.get('accountType')?.value,
      bsb: this.AddDetails.get('bsb')?.value,
      bank_account_no: this.AddDetails.get('acc_no')?.value,
      super_fund_name: this.AddDetails.get('fund_name')?.value,
      superannuation_Membership: this.AddDetails.get('superannuation')?.value,
      criminal_history: [
        {
          offence: this.AddDetails.get('offence_one')?.value,
          result: this.AddDetails.get('result_one')?.value,
        },
        {
          offence: this.AddDetails.get('offence_two')?.value,
          result: this.AddDetails.get('result_two')?.value,
        },
      ],
      description: this.describeFormControl.value,
      phy_dis: this.option1,
      ner_dis: this.option2,
      bron_dis: this.option3,
      med_cond: this.option4,
      work_inj: this.option5,
      smoke: this.option6,
      comp_name_one: this.AddDetails.get('comp_name_one')?.value,
      contact_per_one: this.AddDetails.get('contact_per_one')?.value,
      first_comp_phone: this.AddDetails.get('phone_one')?.value,
      first_comp_joining_date: this.AddDetails.get('from_date_one')?.value,
      first_comp_ending_date: this.AddDetails.get('to_date_one')?.value,
      pos_in_first_comp: this.AddDetails.get('position_one')?.value,
      duties_one: this.AddDetails.get('duties_one')?.value,
      comp_name_two: this.AddDetails.get('comp_name_two')?.value,
      contact_per_two: this.AddDetails.get('contact_per_two')?.value,
      second_comp_phone: this.AddDetails.get('phone_two')?.value,
      second_comp_joining_date: this.AddDetails.get('from_date_two')?.value,
      second_comp_ending_date: this.AddDetails.get('to_date_two')?.value,
      pos_in_second_comp: this.AddDetails.get('position_two')?.value,
      duties_two: this.AddDetails.get('duties_two')?.value,
      ref_name: this.AddDetails.get('ref_name')?.value,
      ref_relationship: this.AddDetails.get('relationship')?.value,
      pr_cont_ref_no: this.AddDetails.get('contact')?.value,
      sign: this.AddDetails.get('sign')?.value,
      ref_fullname: this.AddDetails.get('ref_fullname')?.value,
      ref_date: this.AddDetails.get('ref_date')?.value,
      interview_by: this.AddDetails.get('checkedBy')?.value,
    }
    // if (this.AddDetails.valid) {
    console.log("Form data", data);
    this.formservice.StoreEmpForm(data, this.businessId).subscribe(res => {
      if (res.success) {
        this.toast.toastNotification('Form submitted successfully:', 'Form Stored!');
        this.close();
      }
    })
    // }
    // else{
    //   this.toast.toastNotification1('Please Fill All the Required Data', 'Incomplete Fields!');
    // }
  }

  getSign
  data
  empData
  getEmployeData(id) {
    if (this.fromParent) {
      this.data = {
        id: id,
        type: 'portal'
      }
    }
    else {
      this.data = {
        id: id,
        business_id: this.businessId,
        type: 'url'
      }
    }
    this.formservice.GetEmpForm(this.data).subscribe(({ success, data }) => {
      this.empData = data;
      console.log("Employee Data", this.empData);
      if (success) {
        this.AddDetails.get('surname').setValue(data.name)
        this.AddDetails.get('given_name').setValue(data.name)
        if (data.dob) {
          const dateParts = data.dob.split('-');
          const formattedDate = `${dateParts[2]}-${dateParts[0]}-${dateParts[1]}`;
          // const formattedDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
          this.AddDetails.get('date_of_birth').setValue(formattedDate);
        } else {
          this.AddDetails.get('date_of_birth').setValue(null);
        }
        this.AddDetails.get('home_phone').setValue(data.home_phone)
        this.AddDetails.get('mobile').setValue(data.phone)
        this.AddDetails.get('email').setValue(data.email)
        this.AddDetails.get('TFN').setValue(data.tfn_file_no)
        this.AddDetails.get('ABN').setValue(data.abn_no)
        this.AddDetails.get('Abn_type').setValue(data.abn_type)
        this.AddDetails.get('gst_status').setValue(data.gst)
        this.AddDetails.get('commencement_of_work').setValue(data.day_of_commencement)
        this.AddDetails.get('address').setValue(data.address)
        this.AddDetails.get('state').setValue(data.state)
        this.AddDetails.get('pcode').setValue(data.postal_code)

        const selectedIndex = this.statuses.findIndex(
          (status) => status.value === data.guard_document_type
        );
        if (selectedIndex !== -1) {
          this.selectedindex = selectedIndex;
        }
        this.AddDetails.get('sec_lic').setValue(data.sec_lic_no)
        if (data.sec_lic_exp) {
          const dateParts = data.sec_lic_exp.split('-');
          const formattedDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
          this.AddDetails.get('exp_date').setValue(formattedDate);
        } else {
          this.AddDetails.get('exp_date').setValue(null);
        }
        this.AddDetails.get('other_qua').setValue(data.other_qualification)

        const selectedcarIndex = this.cars.findIndex(
          (carstatus) => carstatus.value === data.car
        );
        if (selectedcarIndex !== -1) {
          this.active = selectedcarIndex;
        }
        this.AddDetails.get('car_reg').setValue(data.car_reg)
        this.AddDetails.get('days').setValue(data.days)
        this.AddDetails.get('issue_date').setValue(data.date_of_issue)
        this.AddDetails.get('return_date').setValue(data.date_of_return)
        this.AddDetails.get('note').setValue(data.note)
        this.AddDetails.get('emergency_name').setValue(data.emergency_contact_name)
        this.AddDetails.get('emergency_contact').setValue(data.emergency_contact_phone)
        this.AddDetails.get('relation').setValue(data.relationship)
        this.AddDetails.get('bank_name').setValue(data.bank_name)
        this.AddDetails.get('accountType').setValue(data.account_type)
        this.AddDetails.get('bsb').setValue(data.bsb)
        this.AddDetails.get('acc_no').setValue(data.account_no)
        this.AddDetails.get('fund_name').setValue(data.super_fund_name)
        this.AddDetails.get('superannuation').setValue(data.superannuation_Membership)
        if (data.criminal_history && data.criminal_history.length >= 1) {
          this.AddDetails.get('offence_one').setValue(data.criminal_history[0].offence);
          this.AddDetails.get('result_one').setValue(data.criminal_history[0].result);
        }
        if (data.criminal_history && data.criminal_history.length >= 2) {
          this.AddDetails.get('offence_two').setValue(data.criminal_history[1].offence);
          this.AddDetails.get('result_two').setValue(data.criminal_history[1].result);
        }
        this.describeFormControl.setValue(data.description);
        const selectedPhy = this.options1.findIndex(
          (option) => option.value === data.phy_dis
        );
        if (selectedPhy !== -1) {
          this.active1 = selectedPhy;
        }
        const selectedNer = this.options2.findIndex(
          (option2) => option2.value === data.ner_dis
        );
        if (selectedNer !== -1) {
          this.active2 = selectedNer;
        }
        const selectedBron = this.options3.findIndex(
          (option3) => option3.value === data.bron_dis
        );
        if (selectedBron !== -1) {
          this.active3 = selectedBron;
        }
        const selectedMed = this.options4.findIndex(
          (option4) => option4.value === data.med_cond
        );
        if (selectedMed !== -1) {
          this.active4 = selectedMed;
        }
        const selectedWork = this.options5.findIndex(
          (option5) => option5.value === data.work_inj
        );
        if (selectedWork !== -1) {
          this.active5 = selectedWork;
        }
        const selectedSmoke = this.options6.findIndex(
          (option6) => option6.value === data.smoke
        );
        if (selectedSmoke !== -1) {
          this.active6 = selectedSmoke;
        }

        this.AddDetails.get('comp_name_one').setValue(data.comp_name_one)
        this.AddDetails.get('contact_per_one').setValue(data.contact_per_one)
        this.AddDetails.get('phone_one').setValue(data.first_comp_phone)
        this.AddDetails.get('from_date_one').setValue(data.first_comp_joining_date)
        this.AddDetails.get('to_date_one').setValue(data.first_comp_ending_date)
        this.AddDetails.get('position_one').setValue(data.pos_in_first_comp)
        this.AddDetails.get('duties_one').setValue(data.duties_one)
        this.AddDetails.get('comp_name_two').setValue(data.comp_name_two)
        this.AddDetails.get('contact_per_two').setValue(data.contact_per_two)
        this.AddDetails.get('phone_two').setValue(data.second_comp_phone)
        this.AddDetails.get('from_date_two').setValue(data.second_comp_joining_date)
        this.AddDetails.get('to_date_two').setValue(data.second_comp_ending_date)
        this.AddDetails.get('position_two').setValue(data.pos_in_second_comp)
        this.AddDetails.get('duties_two').setValue(data.duties_two)

        this.AddDetails.get('ref_name').setValue(data.ref_name)
        this.AddDetails.get('relationship').setValue(data.ref_relationship)
        this.AddDetails.get('contact').setValue(data.pr_cont_ref_no)
        this.AddDetails.get('ref_fullname').setValue(data.ref_fullname)
        this.AddDetails.get('ref_date').setValue(data.ref_date);
        this.AddDetails.get('checkedBy').setValue(data.interview_by)
        this.getSign = data.sign

      }

      const selectedAbnTypes = data.abn_type;
      this.selectedAbn = this.abns.filter((abn) =>
        selectedAbnTypes.some((selectedAbnType) => selectedAbnType.name === abn.name)
      );

      const selectedQua = data.other_qualification;
      this.selectedQualification = this.otherQualifications.filter((qua) =>
        selectedQua.some((selectedQuali) => selectedQuali.name === qua.name)
      );

      const selectedDay = data.days;
      this.selectedDay = this.days.filter((day) =>
        selectedDay.some((selectedDay) => selectedDay.name === day.name)
      );
    })
  }

  close() {
    this.modalService.dismissAll();
  }

  onCheckboxChange(index: number) {
    this.uniformTypes[index].status = !this.uniformTypes[index].status;
  }

  drawComplete() {
    console.log(this.signaturePad.toDataURL());
  }

  drawStart() {
    console.log('begin drawing');
  }

  clearSignature() {
    this.signaturePad.clear();
  }

  savePad() {
    const base64Data = this.signaturePad.toDataURL();
    this.signatureImg = base64Data;
    console.log("Save image", this.signatureImg);

    this.formservice.saveSign(base64Data, 'staff_sign').subscribe(res => {
      if (res.success) {
        this.AddDetails.get('sign').setValue(res.url)
      }
    });
  }




  Date;
  fillColor;
  lineColor;
  fillColorRegister;
  lineColorRegister;
  textColorRegister;
  fromDateone;
  toDateone;
  fromDatetwo;
  toDatetwo;
  refDate;
  imageFormat
  signDataURL;

  getImageFormat(base64String: string): string {
    // Check if the base64 string contains JPEG content type declaration
    if (base64String.includes('data:image/jpeg')) {
      return 'jpeg';
    }
    // Check if the base64 string contains PNG content type declaration
    else if (base64String.includes('data:image/png')) {
      return 'png';
    }
    // Add more checks for other image formats if needed
    else {
      // Default to JPEG if the format cannot be determined
      return 'jpeg';
    }
  }

  async makePdf() {

    if (this.empData.dob) {
      const dateParts = this.empData.dob.split('-');
      this.Date = `${dateParts[1]}-${dateParts[0]}-${dateParts[2]}`;
    } else {
      this.Date = 'null';
    }

    if (this.empData.first_comp_joining_date) {
      const dateParts = this.empData.first_comp_joining_date.split('-');
      this.fromDateone = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
    } else {
      this.fromDateone = 'null';
    }

    if (this.empData.first_comp_ending_date) {
      const dateParts = this.empData.first_comp_ending_date.split('-');
      this.toDateone = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
    } else {
      this.toDateone = 'null';
    }

    if (this.empData.second_comp_joining_date) {
      const dateParts = this.empData.second_comp_joining_date.split('-');
      this.fromDatetwo = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
    } else {
      this.fromDatetwo = 'null';
    }

    if (this.empData.second_comp_ending_date) {
      const dateParts = this.empData.second_comp_ending_date.split('-');
      this.toDatetwo = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
    } else {
      this.toDatetwo = 'null';
    }

    if (this.empData.ref_date) {
      const dateParts = this.empData.ref_date.split('-');
      this.refDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
    } else {
      this.refDate = 'null';
    }

    if (this.empData.abn_type) {

      // for (let i = 0; i < this.abnType.length; i++) {
      //   let name = this.abnType[i].name;
      //   console.log("Abn name", name)
      //   if(name === 'Sole Traders'){
      //     this.fillColor = '#00A37E';
      //     this.lineColor = '#00A37E';
      //   }
      //   else if(name === 'Partnership'){
      //     this.fillColor = '#00A37E';
      //     this.lineColor = '#00A37E';
      //   }
      //   else if(name === 'Trust' ){
      //     this.fillColor = '#00A37E';
      //     this.lineColor = '#00A37E';
      //   }
      //   else if(name === 'Company'){
      //     this.fillColor = '#00A37E';
      //     this.lineColor = '#00A37E';
      //   }
      //   else{
      //     this.fillColor = '#FFFFFF';
      //     this.lineColor = '#000000';
      //   }
      // }
    }

    const getBase64Image = (imgPath: string): Promise<string> => {
      return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = "Anonymous";

        img.onload = () => {
          const canvas = document.createElement("canvas");
          canvas.width = img.width;
          canvas.height = img.height;

          const ctx = canvas.getContext("2d");
          ctx.drawImage(img, 0, 0);

          const dataURL = canvas.toDataURL("image/png");
          resolve(dataURL);
        };

        img.onerror = () => {
          reject(new Error("Error loading image"));
        };

        img.src = imgPath;
      });
    };


    const logoDataURL = await getBase64Image("/assets/images/logo/scouts.png");
    if (this.empData.base64_sign) {
      this.imageFormat = this.getImageFormat(this.empData.base64_sign);
    }
    else {
      this.signDataURL = "Signature not found."
    }

    let docDefinition: TDocumentDefinitions = {
      pageSize: "A4",
      footer: (currentPage: number, pageCount: number) => {
        return {
          columns: [
            {
              text: `Page ${currentPage}/${pageCount}`,
              alignment: "left",
              margin: [30, 0],
              fontSize: 8,
              color: "#555",
            },
          ],
          margin: [40, 10],
        };
      },

      content: [
        {
          columns: [
            {
              width: "auto",
              stack: [
                {
                  image: logoDataURL,
                  width: 100,
                  height: 80,
                },
              ],
            },
          ]
        },

        {
          columns: [
            {
              width: "*",
              text: "NEW EMPLOYMENT/CONTRACTOR DETAILS",
              alignment: "center",
              color: '#01A37E',
              bold: true,
              fontSize: 22,
            },
          ]
        },

        {
          canvas: [
            { type: "line", x1: 0, y1: 10, x2: 516, y2: 10, lineWidth: 3, lineColor: '#01A37E' },
          ],
          margin: [0, 0, 0, 5],
        },

        //Contact Details
        {
          table: {
            widths: ['*'],
            body: [
              [
                {
                  text: 'Contact Details',
                  fontSize: 17,
                  alignment: 'center',
                  style: {
                    bold: true,
                    fillColor: '#01A37E',
                    color: '#FFFFFF'
                  }
                },
              ],
            ],
          },
          layout: 'noBorders',
          margin: [0, 0, 0, 5],
        },

        {
          columns: [
            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "25%",
                      stack: [
                        {
                          text: "Surname:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 5, 0, 0]
                        },
                        {
                          text: "Date Of Birth:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        },
                        {
                          text: "Mobile:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        }
                      ]
                    },

                    {
                      width: "30%",
                      stack: [
                        {
                          text: this.empData.name,
                          fontSize: 9
                        },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        },

                        {
                          text: this.Date,
                          fontSize: 9,
                          margin: [0, 5, 0, 0]
                        },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        },

                        {
                          text: this.empData.phone,
                          fontSize: 9,
                          margin: [0, 5, 0, 0]
                        },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        }
                      ]
                    }
                  ]
                }
              ]
            },

            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "Given Name:",
                          fontSize: 9,
                          margin: [0, 5, 0, 0],
                          bold: true
                        },
                        {
                          text: "Home Phone:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        },
                        {
                          text: "Email Address:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        }
                      ]
                    },

                    {
                      width: "50%",
                      stack: [
                        {
                          text: this.empData.name,
                          fontSize: 9
                        },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                        this.empData.home_phone
                          ? {
                            text: this.empData.home_phone,
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          }
                          : {
                            text: "null",
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                        {
                          text: this.empData.email,
                          fontSize: 9,
                          margin: [0, 5, 0, 0]
                        },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        }
                      ]
                    }
                  ]
                }
              ],
              margin: [15, 0, 0, 0]
            },
          ],
          margin: [0, 5, 0, 5],
        },

        //End Contact Details

        //Particulars
        {
          table: {
            widths: ['*'],
            body: [
              [
                {
                  text: 'Particulars',
                  fontSize: 17,
                  alignment: 'center',
                  style: {
                    bold: true,
                    fillColor: '#01A37E',
                    color: '#FFFFFF'
                  }
                },
              ],
            ],
          },
          layout: 'noBorders',
          margin: [0, 10, 0, 5],
        },

        {
          columns: [
            {
              width: "30%",
              stack: [
                {
                  text: "Tax File Number (TFN):",
                  fontSize: 9,
                  bold: true,
                },

                {
                  text: "Australian Business Number (ABN):",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 15, 0, 0]
                },

                {
                  text: "ABN Type:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 18, 0, 0]
                },

                {
                  text: "GST Status:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 20, 0, 0]
                },

                {
                  text: "Day of Commencement of work:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 20, 0, 0]
                },
              ]
            },

            {
              width: "55%",
              stack: [
                this.empData.tfn_file_no
                  ? {
                    text: this.empData.tfn_file_no,
                    fontSize: 9,
                  }
                  : {
                    text: "null",
                    fontSize: 9,
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 275, y2: 5, lineWidth: 0.5 }]
                },

                this.empData.abn_no
                  ? {
                    text: this.empData.abn_no,
                    fontSize: 9,
                    margin: [0, 10, 0, 0]
                  }
                  : {
                    text: "null",
                    fontSize: 9,
                    margin: [0, 10, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 275, y2: 5, lineWidth: 0.5 }]
                },

                {
                  columns: [
                    {
                      stack: [
                        {
                          canvas: [
                            {
                              type: 'rect',
                              x: 0,
                              y: 0,
                              w: 55,
                              h: 20,
                              lineWidth: 1,
                              lineColor: this.lineColor,
                              color: this.fillColor,
                            }
                          ],
                          margin: [0, 10, 0, 0],
                        },

                        {
                          text: 'Sole Trader',
                          fontSize: 9,
                          alignment: 'center',
                          relativePosition: { x: -9, y: -15 },
                          // color: '#ffffff'
                        }
                      ],
                    },

                    {
                      stack: [
                        {
                          canvas: [
                            {
                              type: 'rect',
                              x: 0,
                              y: 0,
                              w: 55,
                              h: 20,
                              lineWidth: 1,
                              lineColor: this.lineColor,
                              color: this.fillColor,
                            }
                          ],
                          margin: [0, 10, 0, 0],
                        },

                        {
                          text: 'Partnership',
                          fontSize: 9,
                          alignment: 'center',
                          relativePosition: { x: -9, y: -15 },
                          // color: '#ffffff'
                        }
                      ]
                    },

                    {
                      stack: [
                        {
                          canvas: [
                            {
                              type: 'rect',
                              x: 0,
                              y: 0,
                              w: 50,
                              h: 20,
                              lineWidth: 1,
                              lineColor: this.lineColor,
                              color: this.fillColor,
                            }
                          ],
                          margin: [0, 10, 0, 0],
                        },

                        {
                          text: 'Trust',
                          fontSize: 9,
                          alignment: 'center',
                          relativePosition: { x: -9, y: -15 },
                          // color: '#ffffff'
                        }
                      ]
                    },

                    {
                      stack: [
                        {
                          canvas: [
                            {
                              type: 'rect',
                              x: 0,
                              y: 0,
                              w: 50,
                              h: 20,
                              lineWidth: 1,
                              lineColor: this.lineColor,
                              color: this.fillColor,
                            }
                          ],
                          margin: [0, 10, 0, 0],
                        },

                        {
                          text: 'Company',
                          fontSize: 9,
                          alignment: 'center',
                          relativePosition: { x: -9, y: -15 },
                          // color: '#ffffff'
                        }
                      ]
                    },
                  ],
                },
                {
                  columns: [
                    {
                      stack: [
                        {
                          canvas: [
                            {
                              type: 'rect',
                              x: 0,
                              y: 0,
                              w: 60,
                              h: 20,
                              lineWidth: 1,
                              lineColor: this.empData.gst === "Register" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                              color: this.empData.gst === "Register" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                            }
                          ],
                          margin: [0, 10, 0, 0],
                        },

                        {
                          text: 'Register',
                          fontSize: 9,
                          alignment: 'center',
                          relativePosition: { x: -39, y: -15 },
                          color: this.empData.gst === "Register" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                        }
                      ]
                    },

                    {
                      stack: [
                        {
                          canvas: [
                            {
                              type: 'rect',
                              x: 0,
                              y: 0,
                              w: 60,
                              h: 20,
                              lineWidth: 1,
                              lineColor: this.empData.gst === "Not Register" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                              color: this.empData.gst === "Not Register" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                            }
                          ],
                          margin: [0, 10, 0, 0],
                        },

                        {
                          text: 'Not Register',
                          fontSize: 9,
                          alignment: 'center',
                          relativePosition: { x: -40, y: -15 },
                          color: this.empData.gst === "Not Register" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                        }
                      ]
                    },
                  ]
                },
                this.empData.day_of_commencement
                  ? {
                    text: this.empData.day_of_commencement,
                    fontSize: 9,
                    margin: [0, 10, 0, 0]
                  }
                  : {
                    text: "null",
                    fontSize: 9,
                    margin: [0, 10, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 350, y2: 5, lineWidth: 0.5 }]
                }
              ],

            },

            {
              width: "15%",
              stack: [
                {
                  text: "(Must be 9 digits)",
                  fontSize: 9
                },

                {
                  text: "(Must be 9 digits)",
                  fontSize: 9,
                  margin: [0, 15, 0, 0]
                },
              ]
            },
          ],
          margin: [0, 5, 0, 5],
        },
        // end Particulars

        // start Personal Address
        {
          table: {
            widths: ['*'],
            body: [
              [
                {
                  text: 'Personal Address',
                  fontSize: 17,
                  alignment: 'center',
                  style: {
                    bold: true,
                    fillColor: '#01A37E',
                    color: '#FFFFFF'
                  }
                },
              ],
            ],
          },
          layout: 'noBorders',
          margin: [0, 10, 0, 5],
        },

        {
          columns: [
            {
              width: "20%",
              stack: [
                {
                  text: "Residential Address:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 10, 0, 0]
                },
              ]
            },
            {
              width: "80%",
              stack: [
                {
                  text: this.empData.address,
                  fontSize: 9,
                  margin: [0, 7, 0, 0]
                },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                }
              ]
            }
          ]
        },

        {
          columns: [
            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "State:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 8, 0, 0]
                        },
                      ]
                    },

                    {
                      width: "40%",
                      stack: [
                        this.empData.state
                          ? {
                            text: this.empData.state,
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 170, y2: 5, lineWidth: 0.5 }]
                        },
                      ]
                    }
                  ]
                }
              ]
            },

            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "p/Code:",
                          fontSize: 9,
                          margin: [0, 8, 0, 0],
                          bold: true
                        },
                      ]
                    },

                    {
                      width: "40%",
                      stack: [
                        this.empData.postal_code
                          ? {
                            text: this.empData.postal_code,
                            fontSize: 9,
                            margin: [0, 5, 0, 0],
                          }
                          : {
                            text: "null",
                            fontSize: 9,
                            margin: [0, 5, 0, 0],
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                      ]
                    },

                  ]
                }
              ],
              margin: [15, 0, 0, 0]
            },
          ],
          margin: [0, 5, 0, 5],
        },

        {
          columns: [
            {
              width: "15%",
              stack: [
                {
                  text: "Residency Status:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 15, 0, 0]
                },
              ]
            },
            {
              columns: [
                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 60,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.empData.guard_document_type === "student_visa" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                          color: this.empData.guard_document_type === "student_visa" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Student Visa',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -7, y: -15 },
                      color: this.empData.guard_document_type === "student_visa" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ],
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 60,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.empData.guard_document_type === "bridging_visa" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                          color: this.empData.guard_document_type === "bridging_visa" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Bridging Visa',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -7, y: -15 },
                      color: this.empData.guard_document_type === "bridging_visa" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 50,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.empData.guard_document_type === "citizen" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                          color: this.empData.guard_document_type === "citizen" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Citizen',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -9, y: -15 },
                      color: this.empData.guard_document_type === "citizen" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 60,
                          h: 30,
                          lineWidth: 1,
                          lineColor: this.empData.guard_document_type === "permanent_resident" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                          color: this.empData.guard_document_type === "permanent_resident" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Permanent Residence',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -8, y: -25 },
                      color: this.empData.guard_document_type === "permanent_resident" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    },
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 60,
                          h: 30,
                          lineWidth: 1,
                          lineColor: this.empData.guard_document_type === "visa_subclass_485" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                          color: this.empData.guard_document_type === "visa_subclass_485" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Visa Subclass 485',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -8, y: -25 },
                      color: this.empData.guard_document_type === "visa_subclass_485" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    },
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 50,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.empData.guard_document_type === "other" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                          color: this.empData.guard_document_type === "other" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Other',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -9, y: -15 },
                      color: this.empData.guard_document_type === "other" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    },
                  ]
                },
              ],
            },
          ]
        },

        {
          columns: [
            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "Sec.Lic.NO:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 8, 0, 0]
                        },
                      ]
                    },

                    {
                      width: "40%",
                      stack: [
                        this.empData.sec_lic_no
                          ? {
                            text: this.empData.sec_lic_no,
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          }
                          : {
                            text: "null",
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 170, y2: 5, lineWidth: 0.5 }]
                        },
                      ]
                    }
                  ]
                }
              ]
            },

            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "Expiry Date:",
                          fontSize: 9,
                          margin: [0, 8, 0, 0],
                          bold: true
                        },
                      ]
                    },

                    {
                      width: "40%",
                      stack: [
                        this.empData.sec_lic_exp
                          ? {
                            text: this.empData.sec_lic_exp,
                            fontSize: 9,
                            margin: [0, 5, 0, 0],
                          }
                          : {
                            text: "null",
                            fontSize: 9,
                            margin: [0, 5, 0, 0],
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                      ]
                    },

                  ]
                }
              ],
              margin: [15, 0, 0, 0]
            },
          ],
          margin: [0, 5, 0, 5],
        },

        {
          columns: [
            {
              width: "15%",
              stack: [
                {
                  text: "Other Qualifications:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 12, 0, 0]
                },
              ]
            },
            {
              columns: [
                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 50,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'First Aid',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -18, y: -15 },
                      // color: this.empData.guard_document_type === "student_visa" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ],
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 50,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                          // lineColor: this.empData.guard_document_type === "bridging_visa" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                          // color: this.empData.guard_document_type === "bridging_visa" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Rsa',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -17, y: -15 },
                      // color: this.empData.guard_document_type === "bridging_visa" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 50,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                          // lineColor: this.empData.guard_document_type === "citizen" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                          // color: this.empData.guard_document_type === "citizen" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Fire Arms',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -17, y: -15 },
                      // color: this.empData.guard_document_type === "citizen" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 65,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                          // lineColor: this.empData.guard_document_type === "permanent_resident" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                          // color: this.empData.guard_document_type === "permanent_resident" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Crowd Control',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -11, y: -15 },
                      // color: this.empData.guard_document_type === "permanent_resident" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    },
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 77,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                          // lineColor: this.empData.guard_document_type === "visa_subclass_485" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                          // color: this.empData.guard_document_type === "visa_subclass_485" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Alarm Installation',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -6, y: -15 },
                      // color: this.empData.guard_document_type === "visa_subclass_485" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    },
                  ]
                },
              ],
            },
          ]
        },

        {
          columns: [
            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "Do You Have Car?",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 14, 0, 0]
                        },
                      ]
                    },

                    {
                      width: "40%",
                      stack: [
                        {
                          canvas: [
                            {
                              type: 'rect',
                              x: 0,
                              y: 0,
                              w: 40,
                              h: 20,
                              lineWidth: 1,
                              lineColor: this.lineColor,
                              color: this.fillColor,
                              // lineColor: this.empData.guard_document_type === "permanent_resident" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                              // color: this.empData.guard_document_type === "permanent_resident" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                            }
                          ],
                          margin: [0, 10, 0, 0],
                        },

                        {
                          text: 'Yes',
                          fontSize: 9,
                          alignment: 'center',
                          relativePosition: { x: -30, y: -15 },
                          // color: this.empData.guard_document_type === "permanent_resident" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                        },
                      ]
                    },

                    {
                      stack: [
                        {
                          canvas: [
                            {
                              type: 'rect',
                              x: 0,
                              y: 0,
                              w: 40,
                              h: 20,
                              lineWidth: 1,
                              lineColor: this.lineColor,
                              color: this.fillColor,
                              // lineColor: this.empData.guard_document_type === "visa_subclass_485" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                              // color: this.empData.guard_document_type === "visa_subclass_485" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                            }
                          ],
                          margin: [0, 10, 0, 0],
                        },

                        {
                          text: 'No',
                          fontSize: 9,
                          alignment: 'center',
                          relativePosition: { x: -20, y: -15 },
                          // color: this.empData.guard_document_type === "visa_subclass_485" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                        },
                      ]
                    },
                  ]
                }
              ]
            },

            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "Car Registration:",
                          fontSize: 9,
                          margin: [0, 13, 0, 0],
                          bold: true
                        },
                      ]
                    },

                    {
                      width: "40%",
                      stack: [
                        this.empData.car_reg
                          ? {
                            text: this.empData.car_reg,
                            fontSize: 9,
                            margin: [0, 10, 0, 0],
                          }
                          : {
                            text: "null",
                            fontSize: 9,
                            margin: [0, 10, 0, 0],
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                      ]
                    },
                  ]
                }
              ],
              margin: [15, 0, 0, 0]
            },
          ],
          margin: [0, 5, 0, 5],
        },

        {
          columns: [
            {
              width: "15%",
              stack: [
                {
                  text: "Availability - Days:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 12, 0, 0]
                },
              ]
            },
            {
              columns: [
                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 45,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Monday',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -8, y: -15 },
                      // color: this.empData.guard_document_type === "student_visa" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ],
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 45,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                          // lineColor: this.empData.guard_document_type === "bridging_visa" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                          // color: this.empData.guard_document_type === "bridging_visa" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Tuesday',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -8, y: -15 },
                      // color: this.empData.guard_document_type === "bridging_visa" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 55,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                          // lineColor: this.empData.guard_document_type === "citizen" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                          // color: this.empData.guard_document_type === "citizen" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Wednesday',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -6, y: -15 },
                      // color: this.empData.guard_document_type === "citizen" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 50,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                          // lineColor: this.empData.guard_document_type === "permanent_resident" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                          // color: this.empData.guard_document_type === "permanent_resident" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Thursday',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -8, y: -15 },
                      // color: this.empData.guard_document_type === "permanent_resident" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    },
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 45,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                          // lineColor: this.empData.guard_document_type === "visa_subclass_485" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                          // color: this.empData.guard_document_type === "visa_subclass_485" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Friday',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -7, y: -15 },
                      // color: this.empData.guard_document_type === "visa_subclass_485" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    },
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 50,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                          // lineColor: this.empData.guard_document_type === "visa_subclass_485" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                          // color: this.empData.guard_document_type === "visa_subclass_485" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Saturday',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -6, y: -15 },
                      // color: this.empData.guard_document_type === "visa_subclass_485" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    },
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 50,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                          // lineColor: this.empData.guard_document_type === "visa_subclass_485" ? this.lineColorRegister = '#00A37E' : this.lineColorRegister = '#000000',
                          // color: this.empData.guard_document_type === "visa_subclass_485" ? this.fillColorRegister = '#00A37E': this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 12, 0, 0],
                    },

                    {
                      text: 'Sunday',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -6, y: -15 },
                      // color: this.empData.guard_document_type === "visa_subclass_485" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    },
                  ]
                },
              ],
            },
          ]
        },

        //end of personal detail

        // Start of emergency contact details
        {
          table: {
            widths: ['*'],
            body: [
              [
                {
                  text: 'Emergency Contact Details',
                  fontSize: 17,
                  alignment: 'center',
                  style: {
                    bold: true,
                    fillColor: '#01A37E',
                    color: '#FFFFFF'
                  }
                },
              ],
            ],
          },
          layout: 'noBorders',
          margin: [0, 10, 0, 5],
        },

        {
          columns: [
            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "25%",
                      stack: [
                        {
                          text: "Name:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 5, 0, 0]
                        },
                        {
                          text: "Relationship:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        },
                      ]
                    },

                    {
                      width: "30%",
                      stack: [
                        this.empData.emergency_contact_name
                          ? {
                            text: this.empData.emergency_contact_name,
                            fontSize: 9
                          }
                          : {
                            text: "null",
                            fontSize: 9
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        },

                        this.empData.relationship
                          ? {
                            text: this.empData.relationship,
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        }
                      ]
                    }
                  ]
                }
              ]
            },

            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "Contact No:",
                          fontSize: 9,
                          margin: [0, 5, 0, 0],
                          bold: true
                        },
                      ]
                    },

                    {
                      width: "50%",
                      stack: [
                        this.empData.emergency_contact_phone
                          ? {
                            text: this.empData.emergency_contact_phone,
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          }
                          : {
                            text: "null",
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                      ]
                    }
                  ]
                }
              ],
              margin: [15, 0, 0, 0]
            },
          ],
          margin: [0, 5, 0, 25],
        },
        // end of emergency

        //Start of financial institution details
        {
          table: {
            widths: ['*'],
            body: [
              [
                {
                  text: 'Financial Institution Details',
                  fontSize: 17,
                  alignment: 'center',
                  style: {
                    bold: true,
                    fillColor: '#01A37E',
                    color: '#FFFFFF'
                  }
                },
              ],
            ],
          },
          layout: 'noBorders',
          margin: [0, 10, 0, 5],
        },

        {
          columns: [
            {
              width: "15%",
              stack: [
                {
                  text: "Bank Name:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 10, 0, 0]
                },
              ]
            },
            {
              width: "85%",
              stack: [
                this.empData.bank_name
                  ? {
                    text: this.empData.bank_name,
                    fontSize: 9,
                    margin: [0, 7, 0, 0]
                  }
                  : {
                    text: 'null',
                    fontSize: 9,
                    margin: [0, 7, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                }
              ]
            }
          ]
        },

        {
          columns: [
            {
              width: "15%",
              stack: [
                {
                  text: "Account Type:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 15, 0, 0]
                },
              ]
            },
            {
              columns: [
                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 50,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.empData.account_type === "Savings" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                          color: this.empData.account_type === "Savings" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Savings',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -50, y: -15 },
                      color: this.empData.account_type === "Savings" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ],
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 50,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.empData.account_type === "Cheque" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                          color: this.empData.account_type === "Cheque" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Cheque',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -50, y: -15 },
                      color: this.empData.account_type === "Cheque" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 60,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.empData.account_type === "Credit Union" ? this.lineColorRegister = '#01A37E' : this.lineColorRegister = '#000000',
                          color: this.empData.account_type === "Credit Union" ? this.fillColorRegister = '#01A37E' : this.fillColorRegister = '#FFFFFF',
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Credit Union',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -43, y: -15 },
                      color: this.empData.account_type === "Credit Union" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },


              ],
            },
          ]
        },

        {
          columns: [
            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "BSB:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        },

                      ]
                    },

                    {
                      width: "30%",
                      stack: [
                        this.empData.bsb
                          ? {
                            text: this.empData.bsb,
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          }
                          : {
                            text: "null",
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        },
                      ]
                    }
                  ]
                }
              ]
            },

            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "Account No:",
                          fontSize: 9,
                          margin: [0, 10, 0, 0],
                          bold: true
                        },
                      ]
                    },

                    {
                      width: "50%",
                      stack: [
                        this.empData.account_no
                          ? {
                            text: this.empData.account_no,
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          }
                          : {
                            text: "null",
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                      ]
                    }
                  ]
                }
              ],
              margin: [15, 0, 0, 0]
            },
          ],
          margin: [0, 5, 0, 5],
        },

        {
          columns: [
            {
              width: "15%",
              stack: [
                {
                  text: "Super Fund Name:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 7, 0, 0]
                },
              ]
            },
            {
              width: "85%",
              stack: [
                this.empData.super_fund_name
                  ? {
                    text: this.empData.super_fund_name,
                    fontSize: 9,
                    margin: [0, 7, 0, 0]
                  }
                  : {
                    text: 'null',
                    fontSize: 9,
                    margin: [0, 7, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                }
              ]
            }
          ]
        },

        {
          columns: [
            {
              width: "15%",
              stack: [
                {
                  text: "Superannuation Membership Number:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 7, 0, 0]
                },
              ]
            },
            {
              width: "85%",
              stack: [
                this.empData.superannuation_Membership
                  ? {
                    text: this.empData.superannuation_Membership,
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  }
                  : {
                    text: 'null',
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                }
              ]
            }
          ]
        },

        //end of financial institution details

        //Start of criminal history
        {
          table: {
            widths: ['*'],
            body: [
              [
                {
                  text: 'Criminal History',
                  fontSize: 17,
                  alignment: 'center',
                  style: {
                    bold: true,
                    fillColor: '#01A37E',
                    color: '#FFFFFF'
                  }
                },
              ],
            ],
          },
          layout: 'noBorders',
          margin: [0, 10, 0, 5],
        },

        {
          columns: [
            {
              width: "100%",
              stack: [
                {
                  text: "If you answer YES to any of these questions, please discuss them before continuing with this form. Have you ever been convicted of any criminal offence in Australia or overseas? This does not include traffic /parking fines but does include drunk driving offences. YES/ NO. If YES, please describe the details: -",
                  fontSize: 11,
                  margin: [0, 7, 0, 0]
                },
              ]
            },
          ]
        },

        {
          columns: [
            {
              width: "15%",
              stack: [
                {
                  text: "Offence:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 15, 0, 0]
                },

                {
                  text: "Results:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 16, 0, 0]
                },

                {
                  text: "Offence:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 18, 0, 0]
                },

                {
                  text: "Results:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 20, 0, 0]
                },
              ]
            },
            {
              width: "85%",
              stack: [
                this.empData.criminal_history
                  ? {
                    text: this.empData.criminal_history,
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  }
                  : {
                    text: 'null',
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                },

                this.empData.criminal_history
                  ? {
                    text: this.empData.criminal_history,
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  }
                  : {
                    text: 'null',
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                },

                this.empData.criminal_history
                  ? {
                    text: this.empData.criminal_history,
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  }
                  : {
                    text: 'null',
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                },

                this.empData.criminal_history
                  ? {
                    text: this.empData.criminal_history,
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  }
                  : {
                    text: 'null',
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                }
              ]
            }
          ]
        },
        // end of criminal history

        //Start of refrences form
        {
          table: {
            widths: ['*'],
            body: [
              [
                {
                  text: 'References Form',
                  fontSize: 17,
                  alignment: 'center',
                  style: {
                    bold: true,
                    fillColor: '#01A37E',
                    color: '#FFFFFF'
                  }
                },
              ],
            ],
          },
          layout: 'noBorders',
          margin: [0, 10, 0, 5],
        },

        {
          columns: [
            {
              width: "100%",
              stack: [
                {
                  text: "Do you have any pending charges or are you currently under investigation for any criminal offences in Australia or overseas? Does not include traffic offences but does include drunk driving offences. YES/NO",
                  fontSize: 11,
                  margin: [0, 7, 0, 0]
                },
              ]
            },
          ]
        },

        {
          columns: [
            {
              width: "20%",
              stack: [
                {
                  text: "If YES, please describe: ",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 15, 0, 0]
                },
              ]
            },
            {
              width: "80%",
              stack: [
                this.empData.description
                  ? {
                    text: this.empData.description,
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  }
                  : {
                    text: 'null',
                    fontSize: 9,
                    margin: [0, 13, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                }
              ]
            }
          ]
        },

        {
          columns: [
            {
              width: "100%",
              stack: [
                {
                  text: "Health",
                  fontSize: 12,
                  bold: true,
                  italics: true,
                  margin: [0, 7, 0, 0]
                },
              ]
            },
          ]
        },

        {
          columns: [
            {
              width: "100%",
              stack: [
                {
                  text: "If you answer YES to any of the below questions, please provide details in the blank space provided including dates of injuries and attention required.",
                  fontSize: 11,
                  margin: [0, 7, 0, 0]
                },
              ]
            },
          ]
        },

        {
          columns: [
            {
              width: "80%",
              stack: [
                {
                  text: "1.  Have you ever suffered from, and physical disability eq. slipped disc, hernia etc?",
                  fontSize: 9,
                  margin: [10, 15, 0, 0]
                },
              ]
            },

            {
              columns: [
                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Yes',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Savings" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ],
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'No',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Cheque" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

              ],
            },
          ]
        },

        {
          columns: [
            {
              width: "80%",
              stack: [
                {
                  text: "2.  Do you suffer from epilepsy or any nervous system disorder?",
                  fontSize: 9,
                  margin: [10, 15, 0, 0]
                },
              ]
            },

            {
              columns: [
                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Yes',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Savings" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ],
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'No',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Cheque" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

              ],
            },
          ]
        },

        {
          columns: [
            {
              width: "80%",
              stack: [
                {
                  text: "3.  Have you ever suffered from bronchitis, asthma, or diabetes?",
                  fontSize: 9,
                  margin: [10, 15, 0, 0]
                },
              ]
            },

            {
              columns: [
                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Yes',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Savings" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ],
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'No',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Cheque" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

              ],
            },
          ]
        },

        {
          columns: [
            {
              width: "80%",
              stack: [
                {
                  text: "4.  Have you ever been hospitalised for a medical condition?",
                  fontSize: 9,
                  margin: [10, 15, 0, 0]
                },
              ]
            },

            {
              columns: [
                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Yes',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Savings" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ],
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'No',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Cheque" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

              ],
            },
          ]
        },

        {
          columns: [
            {
              width: "80%",
              stack: [
                {
                  text: "5.  Have you ever claimed compensation for any work-related injury or illness?",
                  fontSize: 9,
                  margin: [10, 15, 0, 0]
                },
              ]
            },

            {
              columns: [
                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Yes',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Savings" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ],
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'No',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Cheque" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },

              ],
            },
          ]
        },

        {
          columns: [
            {
              width: "80%",
              stack: [
                {
                  text: "6.  Do you smoke? ",
                  fontSize: 9,
                  margin: [10, 15, 0, 0]
                },
              ]
            },

            {
              columns: [
                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'Yes',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Savings" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ],
                },

                {
                  stack: [
                    {
                      canvas: [
                        {
                          type: 'rect',
                          x: 0,
                          y: 0,
                          w: 30,
                          h: 20,
                          lineWidth: 1,
                          lineColor: this.lineColor,
                          color: this.fillColor,
                        }
                      ],
                      margin: [0, 10, 0, 0],
                    },

                    {
                      text: 'No',
                      fontSize: 9,
                      alignment: 'center',
                      relativePosition: { x: -12, y: -15 },
                      // color: this.empData.account_type === "Cheque" ? this.textColorRegister = '#FFFFFF' : this.textColorRegister = '#000000',
                    }
                  ]
                },
              ],
            },
          ],
          margin: [0, 0, 0, 25],
        },
        // end of references form

        //Start of references detail
        {
          table: {
            widths: ['*'],
            body: [
              [
                {
                  text: 'References Details',
                  fontSize: 17,
                  alignment: 'center',
                  style: {
                    bold: true,
                    fillColor: '#01A37E',
                    color: '#FFFFFF'
                  }
                },
              ],
            ],
          },
          layout: 'noBorders',
          margin: [0, 10, 0, 5],
        },

        {
          columns: [
            {
              width: "100%",
              stack: [
                {
                  text: "A company requirement and insurance condition require that satisfactory work references be obtained for up to a minimum of 5 years. You must supply 2 business and 1 personal reference with all contact details. Family and relatives are not acceptable as work history references.",
                  fontSize: 11,
                  margin: [0, 7, 0, 0]
                },
              ]
            },
          ]
        },

        {
          columns: [
            {
              width: "100%",
              stack: [
                {
                  text: "Business / Work History References:",
                  fontSize: 12,
                  bold: true,
                  italics: true,
                  margin: [0, 7, 0, 0]
                },
              ]
            },
          ]
        },

        {
          columns: [
            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "25%",
                      stack: [
                        {
                          text: "Employer Company Name:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 5, 0, 0]
                        },
                        {
                          text: "Phone:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        },
                        {
                          text: "From:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 12, 0, 0]
                        }
                      ]
                    },

                    {
                      width: "30%",
                      stack: [
                        this.empData.comp_name_one
                          ? {
                            text: this.empData.comp_name_one,
                            fontSize: 9,
                            margin: [0, 13, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                            margin: [0, 13, 0, 0]
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        },

                        this.empData.first_comp_phone
                          ? {
                            text: this.empData.first_comp_phone,
                            fontSize: 9,
                            margin: [0, 13, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                            margin: [0, 15, 0, 0]
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        },

                        {
                          text: this.fromDateone,
                          fontSize: 9,
                          margin: [0, 8, 0, 0]
                        },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        }
                      ]
                    }
                  ]
                }
              ]
            },

            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "Contact Person:",
                          fontSize: 9,
                          margin: [0, 19, 0, 0],
                          bold: true
                        },
                        {
                          text: "Position:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 17, 0, 0]
                        },
                        {
                          text: "To:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 15, 0, 0]
                        }
                      ]
                    },

                    {
                      width: "50%",
                      stack: [
                        this.empData.contact_per_one
                          ? {
                            text: this.empData.contact_per_one,
                            fontSize: 9,
                            margin: [0, 15, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                            margin: [0, 15, 0, 0]
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                        this.empData.pos_in_first_comp
                          ? {
                            text: this.empData.pos_in_first_comp,
                            fontSize: 9,
                            margin: [0, 10, 0, 0]
                          }
                          : {
                            text: "null",
                            fontSize: 9,
                            margin: [0, 10, 0, 0]
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                        {
                          text: this.toDateone,
                          fontSize: 9,
                          margin: [0, 10, 0, 0]
                        },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        }
                      ]
                    }
                  ]
                }
              ],
              margin: [15, 0, 0, 0]
            },
          ],
          margin: [0, 5, 0, 5],
        },

        {
          columns: [
            {
              width: "12%",
              stack: [
                {
                  text: "Duties:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 10, 0, 0]
                },
              ]
            },
            {
              width: "88%",
              stack: [
                this.empData.duties_one
                  ? {
                    text: this.empData.duties_one,
                    fontSize: 9,
                    margin: [0, 7, 0, 0]
                  }
                  : {
                    text: 'null',
                    fontSize: 9,
                    margin: [0, 7, 0, 0]
                  },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                }
              ]
            }
          ]
        },

        {
          columns: [
            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "25%",
                      stack: [
                        {
                          text: "Employer Company Name:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 5, 0, 0]
                        },
                        {
                          text: "Phone:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        },
                        {
                          text: "From:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 12, 0, 0]
                        }
                      ]
                    },

                    {
                      width: "30%",
                      stack: [
                        this.empData.comp_name_two
                          ? {
                            text: this.empData.comp_name_two,
                            fontSize: 9,
                            margin: [0, 13, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                            margin: [0, 13, 0, 0]
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        },

                        this.empData.second_comp_phone
                          ? {
                            text: this.empData.second_comp_phone,
                            fontSize: 9,
                            margin: [0, 13, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                            margin: [0, 15, 0, 0]
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        },

                        {
                          text: this.fromDatetwo,
                          fontSize: 9,
                          margin: [0, 8, 0, 0]
                        },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        }
                      ]
                    }
                  ]
                }
              ]
            },

            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "Contact Person:",
                          fontSize: 9,
                          margin: [0, 19, 0, 0],
                          bold: true
                        },
                        {
                          text: "Position:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 17, 0, 0]
                        },
                        {
                          text: "To:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 15, 0, 0]
                        }
                      ]
                    },

                    {
                      width: "50%",
                      stack: [
                        this.empData.contact_per_two
                          ? {
                            text: this.empData.contact_per_two,
                            fontSize: 9,
                            margin: [0, 15, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                            margin: [0, 17, 0, 0]
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                        this.empData.pos_in_second_comp
                          ? {
                            text: this.empData.pos_in_second_comp,
                            fontSize: 9,
                            margin: [0, 10, 0, 0]
                          }
                          : {
                            text: "null",
                            fontSize: 9,
                            margin: [0, 10, 0, 0]
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                        {
                          text: this.toDatetwo,
                          fontSize: 9,
                          margin: [0, 10, 0, 0]
                        },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        }
                      ]
                    }
                  ]
                }
              ],
              margin: [15, 0, 0, 0]
            },
          ],
          margin: [0, 5, 0, 5],
        },

        {
          columns: [
            {
              width: "12%",
              stack: [
                {
                  text: "Duties:",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 10, 0, 0]
                },
              ]
            },
            {
              width: "88%",
              stack: [
                {
                  text: this.empData.duties_two,
                  fontSize: 9,
                  margin: [0, 7, 0, 0]
                },
                {
                  canvas: [{ type: 'line', x1: 0, y1: 5, x2: 405, y2: 5, lineWidth: 0.5 }]
                }
              ]
            }
          ]
        },
        // end of refernce details

        // Start of personal references form
        {
          table: {
            widths: ['*'],
            body: [
              [
                {
                  text: 'Personal Refrences Form',
                  fontSize: 17,
                  alignment: 'center',
                  style: {
                    bold: true,
                    fillColor: '#01A37E',
                    color: '#FFFFFF'
                  }
                },
              ],
            ],
          },
          layout: 'noBorders',
          margin: [0, 10, 0, 5],
        },

        {
          columns: [
            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "25%",
                      stack: [
                        {
                          text: "Name:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 5, 0, 0]
                        },
                        {
                          text: "Contact No:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        },
                        {
                          text: "Print Full Name:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        }
                      ]
                    },

                    {
                      width: "30%",
                      stack: [
                        this.empData.ref_name
                          ? {
                            text: this.empData.ref_name,
                            fontSize: 9
                          }
                          : {
                            text: 'null',
                            fontSize: 9
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        },

                        this.empData.pr_cont_ref_no
                          ? {
                            text: this.empData.pr_cont_ref_no,
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                            margin: [0, 5, 0, 0]
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        },

                        this.empData.ref_fullname
                          ? {
                            text: this.empData.ref_fullname,
                            fontSize: 9,
                            margin: [0, 10, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                            margin: [0, 10, 0, 0]
                          },

                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 180, y2: 5, lineWidth: 0.5 }]
                        }
                      ]
                    }
                  ]
                }
              ]
            },

            {
              width: "50%",
              stack: [
                {
                  columns: [
                    {
                      width: "30%",
                      stack: [
                        {
                          text: "Relationship:",
                          fontSize: 9,
                          margin: [0, 5, 0, 0],
                          bold: true
                        },
                        {
                          text: "Date:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        },
                        {
                          text: "Checked & Interviewed by:",
                          fontSize: 9,
                          bold: true,
                          margin: [0, 10, 0, 0]
                        }
                      ]
                    },

                    {
                      width: "50%",
                      stack: [
                        this.empData.ref_relationship
                          ? {
                            text: this.empData.ref_relationship,
                            fontSize: 9
                          }
                          : {
                            text: 'null',
                            fontSize: 9
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                        {
                          text: this.refDate,
                          fontSize: 9,
                          margin: [0, 5, 0, 0]
                        },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        },
                        this.empData.interview_by
                          ? {
                            text: this.empData.interview_by,
                            fontSize: 9,
                            margin: [0, 10, 0, 0]
                          }
                          : {
                            text: 'null',
                            fontSize: 9,
                            margin: [0, 10, 0, 0]
                          },
                        {
                          canvas: [{ type: 'line', x1: 0, y1: 5, x2: 165, y2: 5, lineWidth: 0.5 }]
                        }
                      ]
                    }
                  ]
                }
              ],
              margin: [15, 0, 0, 0]
            },
          ],
          margin: [0, 5, 0, 5],
        },

        {
          columns: [
            {
              width: "100%",
              stack: [
                {
                  text: "Please send your compliance documents to 'app.thescouts.com.au'",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 7, 0, 0]
                },
              ]
            },
          ]
        },

        {
          columns: [
            {
              width: "50%",
              stack: [
                {
                  text: "Security Licence",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 5, 0, 0]
                },

                {
                  text: "Driver Licence • First aid Cert",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 5, 0, 0]
                },

                {
                  text: "Covid Vaccination Cert",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 5, 0, 0]
                },
              ]
            },

            {
              width: "50%",
              stack: [
                {
                  text: "Proof of citizenship",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 5, 0, 0]
                },

                {
                  text: "WWCC / White Card",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 5, 0, 0]
                },

                {
                  text: "Visa Copy OR - Security Certs",
                  fontSize: 9,
                  bold: true,
                  margin: [0, 5, 0, 0]
                },
              ]
            },
          ]
        },

        // end of personal reference

        //Start of Before Submission
        {
          table: {
            widths: ['*'],
            body: [
              [
                {
                  text: 'Before Submission',
                  fontSize: 17,
                  alignment: 'center',
                  style: {
                    bold: true,
                    fillColor: '#01A37E',
                    color: '#FFFFFF'
                  }
                },
              ],
            ],
          },
          layout: 'noBorders',
          margin: [0, 10, 0, 5],
        },

        {
          columns: [
            {
              width: "auto",
              stack: [
                this.empData.base64_sign
                  ? {
                    image: `data:image/${this.imageFormat};base64,${this.empData.base64_sign}`,
                    width: 80,
                    height: 80,
                  }
                  : {
                    text: this.signDataURL,
                    fontSize: 9,
                  },
              ],
            },
          ]
        },


      ]
    }
    pdfMake.createPdf(docDefinition).download("EmployeeDetails.pdf");
  }


}

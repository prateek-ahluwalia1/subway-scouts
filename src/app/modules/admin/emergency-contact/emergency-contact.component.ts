import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
// import jsPDF from "jspdf";
import html2canvas from 'html2canvas';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormControl} from '@angular/forms';
import { GlobalVariable } from 'app/shared/global';
import { FormBuildService } from 'app/services/form-build.service';
import { ToastServiceService } from 'app/services/toast-service.service';
@Component({
  selector: 'app-emergency-contact',
  templateUrl: './emergency-contact.component.html',
  styleUrls: ['./emergency-contact.component.scss']
})
export class EmergencyContactComponent implements OnInit {

  @ViewChild("content") content: ElementRef;
  @Input() fromParent;

  AddEmergContact
  selectedindexacc:number;
  guardsIds

  accTypes=[
    {name:"Savings"},
    {name:"Cheque"},
    {name:"Credit Union"},
  ]

  constructor(private fb: FormBuilder, public global: GlobalVariable,  private formservice: FormBuildService, 
    private toast: ToastServiceService, private modalService: NgbModal) { 

      this.guardsIds = localStorage.getItem('guardsIds');

    }

  ngOnInit(): void {

    this.getEmergencyCon(this.fromParent);

    this.AddEmergContact = this.fb.group({
      name: new FormControl(''),
      contact: new FormControl(''),
      relation: new FormControl(''),
      bank_name: new FormControl(''),
      acc_name: new FormControl(''),
      accountType: new FormControl(''),
      bsb: new FormControl(''),
      acc_no: new FormControl(''),
      tax_file: new FormControl(''),
      fund_name: new FormControl(''),
      superannuation: new FormControl(''),
      offence_one: new FormControl(''),
      result_one: new FormControl(''),
      offence_two: new FormControl(''),
      result_two: new FormControl(''),
    })

  }
  setacc(index:any)
  {
    this.selectedindexacc=index;
    this.AddEmergContact.get('accountType')?.setValue(this.accTypes[index].name);
  }

  makePdf() {
    // const data = this.content.nativeElement;
    // html2canvas(data).then(canvas => {let fileWidth = 208;
    //   let fileHeight = (canvas.height * fileWidth) / canvas.width;
    //   const FILEURI = canvas.toDataURL('image/png');
    //   let PDF = new jsPDF('p', 'mm', 'a4');
    //   let position = 0;
    //   PDF.addImage(FILEURI, 'PNG', 0, position, fileWidth, fileHeight);
    //   PDF.save('angular-demo.pdf');
    // });
  }

  close(){
    this.modalService.dismissAll();
  }

  submit(){
    let data = {
      guard_id : this.guardsIds,
      emergency_contact_name: this.AddEmergContact.get('name')?.value,
      emergency_contact_phone: this.AddEmergContact.get('contact')?.value,
      relationship: this.AddEmergContact.get('relation')?.value,
      bank_name: this.AddEmergContact.get('bank_name')?.value,
      account_name: this.AddEmergContact.get('acc_name')?.value,
      account_type: this.AddEmergContact.get('accountType')?.value,
      bank_state_branch: this.AddEmergContact.get('bsb')?.value,
      account_no: this.AddEmergContact.get('acc_no')?.value,
      tax_file: this.AddEmergContact.get('tax_file')?.value,
      super_fund_name: this.AddEmergContact.get('fund_name')?.value,
      superannuation_Membership: this.AddEmergContact.get('superannuation')?.value,
      criminal_history: [
        {
          offence: this.AddEmergContact.get('offence_one')?.value,
          result: this.AddEmergContact.get('result_one')?.value,
        },
        {
          offence: this.AddEmergContact.get('offence_two')?.value,
          result: this.AddEmergContact.get('result_two')?.value,
        },
      ],
    }
    this.formservice.StoreEmergencyContact(data).subscribe(res => {
      if (res.success) {
        this.toast.toastNotification('Form submitted successfully:', 'Form Stored!')
      }
    })
    // console.log("Form data", data)
  }

  getEmergencyCon(id){
    let data = {
      guard_id: id,
    }
    this.formservice.GetEmergencyContact(data).subscribe(({ success, data}) => {
      if (success) {
        this.AddEmergContact.get('name').setValue(data.emergency_contact_name)
        this.AddEmergContact.get('contact').setValue(data.emergency_contact_phone)
        this.AddEmergContact.get('relation').setValue(data.relationship)
        this.AddEmergContact.get('bank_name').setValue(data.bank_name)
        this.AddEmergContact.get('acc_name').setValue(data.account_name)
        this.AddEmergContact.get('accountType').setValue(data.account_type)
        this.AddEmergContact.get('bsb').setValue(data.bank_state_branch)
        this.AddEmergContact.get('acc_no').setValue(data.account_no)
        this.AddEmergContact.get('tax_file').setValue(data.tax_file)
        this.AddEmergContact.get('fund_name').setValue(data.super_fund_name)
        this.AddEmergContact.get('superannuation').setValue(data.superannuation_no)
        if (data.criminal_history && data.criminal_history.length >= 1) {
          this.AddEmergContact.get('offence_one').setValue(data.criminal_history[0].offence);
          this.AddEmergContact.get('result_one').setValue(data.criminal_history[0].result);
        }
        if (data.criminal_history && data.criminal_history.length >= 2) {
          this.AddEmergContact.get('offence_two').setValue(data.criminal_history[1].offence);
          this.AddEmergContact.get('result_two').setValue(data.criminal_history[1].result);
        }
      }
    })
  }
  
}

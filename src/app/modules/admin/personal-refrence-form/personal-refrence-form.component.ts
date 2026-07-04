import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
// import jsPDF from "jspdf";
import html2canvas from 'html2canvas';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormControl } from '@angular/forms';
import { GlobalVariable } from 'app/shared/global';
import { FormBuildService } from 'app/services/form-build.service';
import { ToastServiceService } from 'app/services/toast-service.service';
@Component({
  selector: 'app-personal-refrence-form',
  templateUrl: './personal-refrence-form.component.html',
  styleUrls: ['./personal-refrence-form.component.scss']
})
export class PersonalRefrenceFormComponent implements OnInit {

  @ViewChild("content") content: ElementRef;
  @Input() fromParent;

  AddPersonalRef
  guardsIds

  constructor(private fb: FormBuilder, public global: GlobalVariable, private formservice: FormBuildService, 
    private toast: ToastServiceService, private modalService: NgbModal) {

    this.guardsIds = localStorage.getItem('guardsIds');

  }

  ngOnInit(): void {

    this.getPerRefrence(this.fromParent);

    this.AddPersonalRef = this.fb.group({
      name: new FormControl(''),
      relationship: new FormControl(''),
      contact: new FormControl(''),
      sign: new FormControl(''),
      fullname: new FormControl(''),
      date: new FormControl(''),
      checkedBy: new FormControl(''),
    })
  }

  makePdf() {
    // const data = this.content.nativeElement;
    // html2canvas(data).then(canvas => {
    //   let fileWidth = 208;
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
      name: this.AddPersonalRef.get('name')?.value,
      relationship: this.AddPersonalRef.get('relationship')?.value,
      contact_no: this.AddPersonalRef.get('contact')?.value,
      applicant_signature: this.AddPersonalRef.get('sign')?.value,
      print_full_name: this.AddPersonalRef.get('fullname')?.value,
      current_date: this.AddPersonalRef.get('date')?.value,
      check_and_interviewed_by: this.AddPersonalRef.get('checkedBy')?.value,
    }
    this.formservice.StorePersonalRef(data).subscribe(res => {
      if (res.success) {
        this.toast.toastNotification('Form submitted successfully:', 'Form Stored!')
      }
    })
    // console.log("Form data", data)
  }

  getPerRefrence(id){
    let data = {
      guard_id: id,
    }
    this.formservice.GetReferenceform(data).subscribe(({ success, data}) => {
      if (success) {
        this.AddPersonalRef.get('name').setValue(data.name)
        this.AddPersonalRef.get('relationship').setValue(data.relationship)
        this.AddPersonalRef.get('contact').setValue(data.contact_no)
        this.AddPersonalRef.get('sign').setValue(data.applicant_signature)
        this.AddPersonalRef.get('fullname').setValue(data.print_full_name)
        this.AddPersonalRef.get('date').setValue(data.current_date)
        this.AddPersonalRef.get('checkedBy').setValue(data.check_and_interviewed_by)
      }
    })
  }
}

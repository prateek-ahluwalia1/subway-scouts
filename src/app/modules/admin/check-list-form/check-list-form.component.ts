import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
// import jsPDF from "jspdf";
import html2canvas from 'html2canvas';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { FormBuildService } from 'app/services/form-build.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { FormsModule } from '@angular/forms';


@Component({
  selector: 'app-check-list-form',
  templateUrl: './check-list-form.component.html',
  styleUrls: ['./check-list-form.component.scss']
})
export class CheckListFormComponent implements OnInit {

  guardsIds
  FormsIds
  @Input() fromParent;

  @ViewChild("content") content: ElementRef;
  checklist = [
    { key: 'emp_form_filled', label: 'Employment Form Filled', checked: false },
    { key: 'tfn_form_filled', label: 'TFN Form Filled', checked: false },
    { key: 'super_form_filled', label: 'Super Form Filled', checked: false },
    { key: 'copy_of_passport_dob', label: 'Copy of Passport or Birth Certificate', checked: false },
    { key: 'copy_current_victoria_security_license', label: 'Copy of the Current Victoria Security License', checked: false },
    { key: 'copy_security_certificate', label: 'Copy of the Security Certificate', checked: false },
    { key: 'copy_of_visa', label: 'Add a copy of the Visa (if applicable)', checked: false },
    { key: 'copy_current_firstaid_rsa', label: 'Copy of the Current First Aid & RSA Certificate', checked: false },
    { key: 'copy_recent_cv', label: 'Copy of Most Recent CV/Resume', checked: false },
    { key: 'copy_driver_license', label: 'Copy of Driver\'s License', checked: false },
  ];

  constructor(private formservice: FormBuildService, private toast: ToastServiceService, public global: GlobalVariable,
    private modalService: NgbModal) {
    this.guardsIds = localStorage.getItem('guardsIds');
  }

  ngOnInit(): void {
    this.getChecklistForm(this.fromParent);
  }

  onCheckboxChange(index: number) {
    this.checklist[index].checked = !this.checklist[index].checked;
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


  submitForm() {
    const selectedItems = this.checklist.filter(item => item.checked).map(item => item.label);
    console.log(selectedItems);

  }
  close() {
    this.modalService.dismissAll();
  }

  save() {
    let data = {
      guard_id: this.guardsIds,
    }

    this.checklist.forEach(item => {
      data[item.key] = item.checked;
    });

    this.formservice.StoreChecklistform(data).subscribe(res => {
      if (res.success) {
        this.toast.toastNotification('Form submitted successfully:', 'Form Stored!')
      }
    })
    // console.log(data);
  }

  getChecklistForm(id){
    let data = {
      guard_id: id,
    }
    this.formservice.GetChecklistform(data).subscribe(({ success, data}) => {
      if (success) {
        this.checklist.forEach(item => {
          item.checked = data[item.key]=== 1 || data[item.key]=== true;
        });
      }
    })
  }

}

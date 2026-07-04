import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
import html2canvas from 'html2canvas';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormControl } from '@angular/forms';
import { GlobalVariable } from 'app/shared/global';
import { FormBuildService } from 'app/services/form-build.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { PermissionsService } from 'app/services/permissions.service';

@Component({
  selector: 'app-uniform-form',
  templateUrl: './uniform-form.component.html',
  styleUrls: ['./uniform-form.component.scss']
})
export class UniformFormComponent implements OnInit {

  @ViewChild("content") content: ElementRef;
  @Input() fromParent;
  @ViewChild('dateInput') dateInput: ElementRef;

  AddUniDetail
  guardsIds

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
  stafffPermissions: any;

  constructor(private fb: FormBuilder, public global: GlobalVariable, private formservice: FormBuildService,
    private toast: ToastServiceService, private modalService: NgbActiveModal, private permissionService: PermissionsService) {
    const per = this.permissionService.getPermissionsByTitle('Onboarding');
    this.stafffPermissions = per?.childPage?.find(item => item.title === 'Current Staff');
    console.log(this.stafffPermissions);
    this.guardsIds = localStorage.getItem('guardsIds');

  }

  ngOnInit(): void {

    this.getUniformForm(this.fromParent);

    this.AddUniDetail = this.fb.group({
      name: new FormControl(''),
      email: new FormControl(''),
      address: new FormControl(''),
      phone: new FormControl(''),
      issue_date: new FormControl(''),
      return_date: new FormControl(''),
      note: new FormControl(''),
      sign: new FormControl(''),
      date: new FormControl(''),
    })

  }

  onCheckboxChange(index: number) {
    this.uniformTypes[index].status = !this.uniformTypes[index].status;
  }


  makePdf() {
    // const data = this.content.nativeElement;
    // html2canvas(data).then(canvas => {
    //   let fileWidth = 208;
    //   let fileHeight = (canvas.height * fileWidth) / canvas.width;
    //   const FILEURI = canvas.toDataURL('image/png');
    //   // let PDF = new jsPDF('p', 'mm', 'a4');
    //   let position = 0;
    //   PDF.addImage(FILEURI, 'PNG', 0, position, fileWidth, fileHeight);
    //   PDF.save('angular-demo.pdf');
    // });
  }
  close() {
    this.modalService.dismiss();
  }

  submit() {
    let data = {
      guard_id: this.guardsIds,
      guard_name: this.AddUniDetail.get('name')?.value,
      email: this.AddUniDetail.get('email')?.value,
      address: this.AddUniDetail.get('address')?.value,
      phone: this.AddUniDetail.get('phone')?.value,
      date_of_issue: this.AddUniDetail.get('issue_date')?.value,
      date_of_return: this.AddUniDetail.get('return_date')?.value,
      note: this.AddUniDetail.get('note')?.value,
      signature: this.AddUniDetail.get('sign')?.value,
      form_fill_date: this.AddUniDetail.get('date')?.value,
      uni_type: this.uniformTypes
    }

    this.formservice.StoreUniformDetail(data).subscribe(res => {
      if (res.success) {
        this.toast.toastNotification('Form submitted successfully:', 'Form Stored!')
        // this.close();
      }
    })

    // console.log("Form data", data)
  }
  getUniformForm(id) {
    let data = {
      guard_id: id,
    }
    this.formservice.GetUniformDetail(data).subscribe(({ success, data }) => {
      if (success) {
        this.AddUniDetail.get('name').setValue(data.guard_name)
        this.AddUniDetail.get('email').setValue(data.email)
        this.AddUniDetail.get('address').setValue(data.address)
        this.AddUniDetail.get('phone').setValue(data.phone)
        this.AddUniDetail.get('issue_date').setValue(data.date_of_issue)
        this.AddUniDetail.get('return_date').setValue(data.date_of_return)
        this.AddUniDetail.get('note').setValue(data.note)
        this.AddUniDetail.get('sign').setValue(data.signature)
        this.AddUniDetail.get('date').setValue(data.form_fill_date)

        const retrievedUniformTypes = data.uni_type;
        for (let i = 0; i < this.uniformTypes.length; i++) {
          const foundUniformType = retrievedUniformTypes.find(item => item.name === this.uniformTypes[i].name);
          if (foundUniformType) {
            this.uniformTypes[i].status = foundUniformType.status;
            this.uniformTypes[i].size = foundUniformType.size;
            this.uniformTypes[i].Qty = foundUniformType.quantity;
          } else {
            this.uniformTypes[i].status = false;
            this.uniformTypes[i].size = '';
            this.uniformTypes[i].Qty = '';
          }
        }
      }
    })
  }
}

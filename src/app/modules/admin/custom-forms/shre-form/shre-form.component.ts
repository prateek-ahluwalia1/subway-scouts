import { Component, Input, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { CustomerService } from 'app/services/customer.service';
import { FormBuildService } from 'app/services/form-build.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
export class Customers {
  id: number;
  name: string;
}

@Component({
  selector: 'app-shre-form',
  templateUrl: './shre-form.component.html',
  styleUrls: ['./shre-form.component.scss']
})
export class ShreFormComponent implements OnInit {
  @Input() form;
  selectedState = 'Victoria';
  states: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia'];

  customers: Customers[] = [];
  sitesList: any = [];
  guardList: any = [];

  customersIds: any[] = [];
  fromMultiCustomer: any = {};
  data: any;
  sitesIds;
  guardsIds;
  fromMultiGuard;
  shareFormType: any;
  title
  constructor(private _serviceModel: NgbModal,
    private toast: ToastServiceService, private router: Router, private formService: FormBuildService,
    public userService: StaffService, private cus: CustomerService, private global: GlobalVariable,
  ) { }

  ngOnInit(): void {
    console.log(this.form);
    this.title = this.form?.title
    this.cus.getCust().subscribe(res => {
      if (res.success == true) {
        this.customers = res.data
      }
    }, (error => {

    }))
  }

  dismiss() {
    this._serviceModel.dismissAll()
  }


  getState(value) {
    this.selectedState = value;
  }


  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
    if (this.fromMultiCustomer) {
      this.userService.getCusSite(this.customersIds).subscribe(res => {
        if (res.success) {
          this.sitesList = res.data
        }
      })
    }
  }


  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.map(item => item.id);
    if (data) {
      this.formService.getSites(this.sitesIds, this.selectedState).subscribe(res => {
        if (res.success) {
          if (res.data) {
            res.data.forEach(element => {
              element.name = element.first_name + (element.middle_name ? ' ' + element.middle_name : '') + (element.last_name ? ' ' + element.last_name : '');
            });
          }

          this.guardList = res.data;
        }
      })
    }
  }



  receiveDataFromGuards(data: any) {
    this.fromMultiGuard = data;
    console.log("Guards data", this.fromMultiGuard);
    this.guardsIds = this.fromMultiGuard.value.map(item => item.id);
    localStorage.setItem('guardsIds', this.guardsIds);
    console.log("Guards Ids", this.guardsIds)
  }


  SendEmail() {
    this.shareFormType = 'custom'
    let data = {
      type: this.shareFormType,
      guard_id: this.guardsIds,
      url: 'form',
      form_id: this.form?.id,
    }
    this.formService.getguards(data).subscribe(res => {
      if (res.success) {
        this.dismiss()
        this.toast.toastNotification('Form has been successfully sent', 'Form Sent!')
      }
    })
  }
}

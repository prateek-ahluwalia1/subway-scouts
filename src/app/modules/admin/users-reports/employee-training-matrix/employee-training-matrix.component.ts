import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { CustomerService } from 'app/services/customer.service';
import { ReportsService } from 'app/services/reports.service';
import { SiteService } from 'app/services/site.service';
import { StaffService } from 'app/services/staff.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-employee-training-matrix',
  templateUrl: './employee-training-matrix.component.html',
  styleUrls: ['../users-reports.component.scss']
})
export class EmployeeTrainingMatrixComponent implements OnInit {

  customers: any[] = [];
  fromMultiCustomer;
  customersId: any[] = [];
  singleSites
  GuardData: any[] = []

  constructor(private userService: StaffService, private cdr: ChangeDetectorRef, private cus: CustomerService,
    public globals: GlobalVariable, private reportService: ReportsService,
  ) { }

  ngOnInit(): void {

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
        this.globals.selectedCustomers = data
        this.cdr.markForCheck()
      }
    })

  }

  selectedCus(data: string) {
    this.fromMultiCustomer = data;
    // this.customersId = this.fromMultiCustomer.value.id;
    // const array = [this.customersId];
    this.customersId = this.fromMultiCustomer.value.map(customer => customer.id);
    // console.log("selected customer ids", this.customersId)
    if (this.fromMultiCustomer) {
      this.userService.getCusSite(this.customersId).subscribe(res => {
        if (res.success) {
          this.singleSites = res.data
          this.cdr.markForCheck()
        }
      })
    }
  }

  receiveDataFromChildSi(data: string) {
    this.fromMultiCustomer = data;
  }

  getReports(){
    let data = {
      customer_id: this.customersId,
      site_id: this.fromMultiCustomer.map((site: any) => site.id)
    }
    // console.log("Submit data", data)

    this.reportService.trainingMatrix(data).subscribe((res) => {
      if (res.success) {
        this.GuardData = res.data
      }
    });
  }

}

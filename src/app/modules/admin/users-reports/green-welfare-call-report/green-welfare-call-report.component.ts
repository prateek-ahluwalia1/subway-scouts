import { ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { FormControl, FormGroup } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { CustomerService } from 'app/services/customer.service';
import { ReportsService } from 'app/services/reports.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
export class Customers {
  id: number;
  name: string;
}
export class Sites {
  id: number;
  site_name: string;
}

@Component({
  selector: 'app-green-welfare-call-report',
  templateUrl: './green-welfare-call-report.component.html',
  styleUrls: ['../users-reports.component.scss'],
  styles:[
    `
    table th,table td{
      padding:10px;
    }
    `
  ]

})
export class GreenWelfareCallReportComponent implements OnInit, OnDestroy {


  guards = [];
  toppings = new FormControl('');
  placeholder = "";
  adminId = new FormControl(null)

  states
  slectedAll: boolean = false

  previewData: any[] = []
  processedData: any[] = [];
  dateRange: any;
  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  constructor(public globals: GlobalVariable, private cus: CustomerService,
    private toast: ToastServiceService, public dateAdapter: DateAdapter<Date>,
    private userService: StaffService, private resportService: ReportsService,
    private cdr: ChangeDetectorRef) {
    this.dateAdapter.setLocale('en-AU');

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }

  }
  showPublishColumn: boolean = false;

  customers: Customers[] = [];

  ngOnInit(): void {

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
        this.globals.selectedCustomers = data
        this.cdr.markForCheck()
      }
    })
  }

  ngOnDestroy() {
    // localStorage.removeItem('routerId');

    // this.trackAdmin.storeActivity('Exit Reports Page', 'Exit Reports Page', this.routeId).subscribe(() => {
    // })
  }

  activity() {
    // this.trackAdmin.storeActivity('Reports Page', 'Enter in Reports Page').subscribe(({ success, id }) => {
    //   if (success) {
    //     localStorage.setItem('routerId', id)
    //     this.routeId = id
    //   }
    // })
  }

  ////send this is id in filter
  customersIds
  fromMultiCustomer
  sitesList: any;
  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
    if (this.fromMultiCustomer) {
      this.userService.getCusSite(this.customersIds).subscribe(({ success, data }) => {
        if (success) {
          this.sitesList = data
          this.cdr.markForCheck()
        }
      })
    }
  }

  // selected sites 
  sitesIds;
  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.map(item => item.id);
  }



  guardsIds;
  fromMultiGuard;
  receiveDataFromChildGuards(data: any) {
    this.fromMultiGuard = data;
    this.guardsIds = this.fromMultiGuard.value.map((item) => item.id);
  }


  downloadExcelFile(fileUrl: string, fileName: string) {
    const link = document.createElement('a');
    link.href = fileUrl;
    link.target = '_blank';
    link.download = fileName;
    link.click();
  }


  status: string[] = []
  onSelectionChange(value) {
    this.status = []
    this.status.push(value.value)
    this.getAllStaff()

  }
  callType: string = 'Both'
  onSelectionChangeCalltype(value) {
    this.callType = value.value;
  }

  length: number = 0;
  pageSize: number = 100;
  pageIndex: number = 0;

  getAllStaff() {
    let data = {
      guard_status: this.status,
      pageIndex: 0,
      pageSize: 1000,
      length: 0,
    }
    this.userService.getStaff(data).subscribe(
      (res) => {
        if (res.success) {
          res.data.forEach((element) => {
            element.name = [
              element.first_name,
              element.middle_name,
              element.last_name,
            ]
              .filter(Boolean)
              .join(" ");
          });
        }
        this.guards = res.data
        this.cdr.markForCheck()

      },
      error => {
        console.log(error);
      }
    );
  }


  guarddata
  getGuardsData() {

    const { start, end } = this.dateRange

    if (
      start && end
    ) {
      const startDate = moment(start).format('MM-DD-YYYY');
      const endDate = moment(end).format('MM-DD-YYYY');
      this.guarddata = {
        date: startDate + ' - ' + endDate,
        guard_ids: this.guardsIds,
        state: this.adminId.value,
        pageIndex: 0,
        pageSize: 10000,
        guard_status: this.status
      }
    }
    else {
      this.guarddata = {
        date: '',
        guard_ids: this.guardsIds,
        state: this.adminId.value,
        pageIndex: 0,
        pageSize: 10000,
        guard_status: this.status
      }
    }
    this.resportService.getGuardReportsData(this.guarddata).subscribe(({ success, message }) => {
      if (success) {

        this.cdr.markForCheck()

      } else {
        this.toast.toastNotification1(message, 'Guard Report!');
      }
    });
  }

  export(type) {
    const startDate = moment(this.dateRange.start).format('MM-DD-YYYY');
    const endDate = moment(this.dateRange.end).format('MM-DD-YYYY');
    let requestData = {
      date: startDate + ' - ' + endDate,
      customer_id: this.customersIds,
      sites: this.sitesIds,
      type: this.callType,
      response: this.status,
      guard_id: this.guardsIds,
      file_type: type
    };

   if( this.callType==="Both" && type==='excel'){
    this.resportService.generateMultiCallReport(requestData).subscribe(({ success, data, path }) => {
      if (success && data) {
        this.previewData = data;
        this.processedData = this.groupData(data);
      } else {
        window.open(path, "_blank");
      }
    },
      (error) => {
        console.log("Error while Downloading", error);
      });
   }
   else{
    this.resportService.generateGreenCallReport(requestData).subscribe(({ success, data, path }) => {
      if (success && data) {
        this.previewData = data;
        this.processedData = this.groupData(data);
      } else {
        window.open(path, "_blank");
      }
    },
      (error) => {
        console.log("Error while Downloading", error);
      });
   }
  }

  groupData(data: any[]): any[] {
    return data.reduce((acc, obj) => {
      const rosterId = obj.roster_id;
      let existingEntry = acc.find(entry => entry.roster_id === rosterId);

      if (!existingEntry) {
        existingEntry = {
          roster_id: rosterId,
          customerName: obj.customer_name,
          date: obj.date,
          siteName: obj.site_name,
          guardName: obj.first_name + " " + obj.middle_name + " " + obj.last_name,
          gcCount: 0,
          wcCount: 0,
          calls: [],
        };
        acc.push(existingEntry);
      }

      // Increment gcCount or wcCount based on call type
      if (obj.type === 'GC') {
        existingEntry.gcCount++;
        if (existingEntry.gcCount <= 2) {
          existingEntry.calls.push({
            send_time: obj.send_time,
            response: obj.response,
            response_time: obj.response_time,
            note: obj.note,
          });
        }
      } else if (obj.type === 'WC' && existingEntry.wcCount < 20) {
        existingEntry.wcCount++;
        existingEntry.calls.push({
          send_time: obj.send_time,
          response: obj.response,
          response_time: obj.response_time,
          note: obj.note,
        });
      }
      return acc;
    }, []);
  }




  formatSendTime(timestamp: number): string {
    if (!timestamp && timestamp == null) {
      return 'N/A';
    }
    const milliseconds = timestamp * 1000;
    const formattedDateTime = moment(milliseconds).format('DD-MM-YYYY HH:mm');
    return formattedDateTime;
  }


  hasWCType(): boolean {
    return this.previewData && this.previewData.some(item => item.type == 'WC');
  }

  hasGCType(): boolean {
    return this.previewData && this.previewData.some(item => item.type == 'GC');
  }

  counter(i: number): any[] {
    return Array(i).fill(0).map((x, i) => i + 1);
  }

  getStartEnd(event: any) {
    this.dateRange = event
  }

}

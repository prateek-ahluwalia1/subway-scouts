import { Component, OnDestroy, OnInit, ViewChild } from '@angular/core';
import moment from 'moment';
import { GlobalVariable } from 'app/shared/global';
import { PortalSettingService } from 'app/services/portal-setting.service';
import { Router } from '@angular/router';
import { FormControl } from '@angular/forms';
import { DateAdapter, MatOption } from '@angular/material/core';
import { PageEvent } from '@angular/material/paginator';
import { MatSelect } from '@angular/material/select';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
  selector: 'app-activity-log',
  templateUrl: './activity-log.component.html',
  styleUrls: ['./activity-log.component.scss']
})
export class ActivityLogComponent implements OnInit, OnDestroy {

  records = [
    {
      id: 1,
      name: "Charge Rates"
    },
    // {
    //   id: 2,
    //   value: "sms_history",
    //   name: "SMS History"
    // },
    {
      id: 3,
      value: "contractors",
      name: "Contractors"
    },
    // {
    //   id: 4,
    //   value: "contractor_documents",
    //   name: "Contractor Documents"
    // },
    // {
    //   id: 5,
    //   value: "contractor_more_contacts",
    //   name: "Contractor More Contacts"
    // },
    {
      id: 6,
      value: "customers",
      name: "Customers"
    },
    // {
    //   id: 7,
    //   value: "customer_documents",
    //   name: "Customer Documents"
    // },
    // {
    //   id: 8,
    //   value: "customer_more_contacts",
    //   name: "Customer More Contacts"
    // },
    // {
    //   id: 9,
    //   value: "emails",
    //   name: "Emails"
    // },
    // {
    //   id: 10,
    //   value: "email_signatures",
    //   name: "Email Signatures"
    // },
    // {
    //   id: 11,
    //   value: "email_templates",
    //   name: "Email Templates"
    // },
    // {
    //   id: 12,
    //   value: "form_templates",
    //   name: "Form Templates"
    // },
    {
      id: 13,
      value: "Staff",
      name: "Staff"
    },
    {
      id: 14,
      value: "staff_documents",
      name: "Staff Documents"
    },
    {
      id: 15,
      value: "staff_work_details",
      name: "Staff Work Details"
    },
    // {
    //   id: 16,
    //   value: "staff_feedbacks",
    //   name: "Staff Feedbacks"
    // },
    {
      id: 17,
      value: "staff_internal_and_external_ids",
      name: "Staff Internal and External IDs"
    },
    {
      id: 18,
      value: "new_job_roster",
      name: "New Job Roster"
    },
    {
      id: 19,
      value: "job_roster",
      name: "Job Roster"
    },
    {
      id: 20,
      value: "job_roster_tasks",
      name: "Job Roster Tasks"
    },
    {
      id: 21,
      value: "payrates",
      name: "Pay Rates"
    },
    {
      id: 22,
      value: "locations",
      name: "Locations"
    },
    // {
    //   id: 23,
    //   value: "sms_templates",
    //   name: "SMS Templates"
    // },
    {
      id: 24,
      value: "staff_uniforms",
      name: "Staff Uniforms"
    },
    {
      id: 25,
      value: "users",
      name: "Users"
    },
    // {
    //   id: 26,
    //   value: "crm_customers",
    //   name: "CRM Customers"
    // },
    // {
    //   id: 27,
    //   value: "scrumboards",
    //   name: "Scrumboards"
    // },
    // {
    //   id: 28,
    //   value: "stages",
    //   name: "Stages"
    // },
    // {
    //   id: 29,
    //   value: "stage_cards",
    //   name: "Stage Cards"
    // },
    // {
    //   id: 30,
    //   value: "card_comments",
    //   name: "Card Comments"
    // },
    // {
    //   id: 31,
    //   value: "customer_comments",
    //   name: "Customer Comments"
    // }
  ];


  isChecked: boolean = false
  start: Date;
  end: Date;
  selectedOption: string;
  searchTerm: any;
  username: any;
  getAdminId: any
  getAdminDatas: any;
  login_user: any
  selectedIndex: any;
  userTypeFilters = [
    { value: 'update_shift_time', label: 'Update Shift Time' },
    { value: 'update_shift', label: 'Update Shift' },
    { value: 'update_shift_tasks', label: 'Update Shift Task' },
    { value: 'add_shift', label: 'Add Shift' },
    { value: 'add_shift_tasks', label: 'Add Shift Task' },
    { value: 'drop_shift', label: 'Drop Shift' },
    { value: 'copy_shift', label: 'Copy Shift' },
    { value: 'delete_shift', label: 'Delete Shift' }
  ];

  adminId = new FormControl(null)

  allSelected = false;
  @ViewChild('selects') selects: MatSelect;

  showAllItemsFlag: boolean = false;
  displayedGuardSummary: any[] = [];

  id;
  type;
  data;
  routeId
  actionOn: any;
  constructor(public global: GlobalVariable, private portalSetting: PortalSettingService, public router: Router,
    public dateAdapter: DateAdapter<Date>, private trackAdmin: TrackAdminActivityService,
    private toast: ToastServiceService) {
    this.dateAdapter.setLocale('en-AU');
    const currentMoment = moment();
    this.start = currentMoment.startOf('week').toDate();
    this.end = currentMoment.endOf('week').toDate();


    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
    this.username = JSON.parse(localStorage.getItem('admin'))
    this.portalSetting.getAdminsByLogin().subscribe(({ success, data }) => {
      if (success) {
        this.getAdminDatas = data
      }
    })

    this.global.selectedStaff = 19;
    this.actionOn = 'job_roster'
    this.global.updateSelectedOption(this.global.selectedStaff);
  }

  toggleAllSelection() {
    if (this.allSelected) {
      this.selects.options.forEach((item: MatOption) => item.select());
    } else {
      this.selects.options.forEach((item: MatOption) => item.deselect());
    }
    this.getData()
  }

  optionClick() {
    let newStatus = true;
    this.selects.options.forEach((item: MatOption) => {
      if (!item.selected) {
        newStatus = false;
      }
    });
    this.allSelected = newStatus;
    this.getData();
  }
  //////////////calender Next and Previous///////////


  select(i, obj) {
    this.selectedIndex = i
    this.login_user = obj
    this.getData();
  }
  searchAll() {
    this.getData()
  }
  getData() {
    const startDate = moment(this.start).format('MM-DD-YYYY');
    const endDate = moment(this.end).format('MM-DD-YYYY');
    let data = {
      login_user_id: this.login_user?.id,
      start: startDate,
      end: endDate,
      action_type: this.adminId.value,
      action_on: this.actionOn
    }
    this.portalSetting.getAdminActivity(data).subscribe(({ success, data }) => {
      if (success && data) {
        this.isChecked = false
        this.displayedGuardSummary = data
        this.trackAdmin.storeActivity('Activity Log', `Checked ${this.login_user?.name ?? ''} log history`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
        })
      }
      else {
        this.toast.toastNotification('Data Not Found', 'Invalid Request!')
      }
    })
  }


  receiveDataFromChildGuards(data: any) {
    if (data) {
      this.actionOn = data.value.value;
      this.isChecked = false;
      this.getData()
    }
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity('Activity Log', 'Exit Activity Log Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Activity Log', `Enter in Activity Log`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }

  onDateChange() {
    if (this.start && this.end && this.login_user) {
      this.getData()
    }
  }
}

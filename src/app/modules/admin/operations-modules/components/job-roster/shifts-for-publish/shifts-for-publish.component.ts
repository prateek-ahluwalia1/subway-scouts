import { Component, inject, Input, OnInit } from '@angular/core';
import { MatTableDataSource } from '@angular/material/table';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { SelectionModel } from '@angular/cdk/collections';
import { GlobalVariable } from 'app/shared/global';
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { RosterServiceService } from '../roster-service.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { RunsheetrosterService } from 'app/services/runsheetroster.service';
import { JobRoster1Service } from 'app/services/job-roster1.service';

interface ResourceItem {
  id: number;
  title: string;
  selected: boolean;
}

interface PublishPerson {
  name: string;
  id: any;
  notifyEmail: boolean;
  notifyMobile: boolean;
  selected: boolean;
}

@Component({
  selector: 'app-shifts-for-publish',
  templateUrl: './shifts-for-publish.component.html',
  styles: [`
    .red-row {
      background-color: #FFDADA !important;
      color: var(--color-white) !important;
    }

    .wrapper {
      max-width: 800px;
      margin: auto;
      padding: 2rem;
      font-family: 'Inter', sans-serif;
      background-color: #f7f9fc;
    }

    .progress-bar {
      margin-bottom: 1.5rem;
    }

    .progress-bar span {
      font-weight: 500;
      display: block;
      margin-bottom: 0.3rem;
    }

    .bar {
      width: 100%;
      height: 8px;
      background: #e5e7eb;
      border-radius: 999px;
      overflow: hidden;
    }

    .fill {
      height: 100%;
      background: linear-gradient(90deg, #3b82f6, #06b6d4);
      transition: width 0.3s ease-in-out;
    }

    .fill.step-2 {
      background: linear-gradient(90deg, #10b981, #2dd36f);
    }

    .card {
      display: none;
      background: white;
      border-radius: 1.2rem;
      padding: 1.5rem;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
      transition: background-color 0.3s ease; 
    }

    .card.active {
      display: block; 
    }

    .card.active {
      background-color: #F2F2F2;
      color: black;
    }

    .card.active h3 {
      color: black;
    }

    .days-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
      gap: 0.8rem;
      margin-bottom: 10px;
    }

    .day-card {
      background: white;
      border-radius: 999px;
      padding: 0.5rem;
      text-align: center;
      cursor: pointer;
      transition: 0.3s;
      font-weight: 500;
      border: 1px solid rgb(109, 110, 110)
    }

    .day-card.active {
      background: linear-gradient(135deg, #3b82f6, #06b6d4);
      color: white;
    }

    .location-list {
      display: flex;
      flex-direction: column;
    }

    .location-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1rem;
    }

    .small-actions {
      margin-top: 0.8rem;
      font-size: 0.9rem;
    }

    .small-actions a {
      color: #2563eb;
      cursor: pointer;
    }

    .footer {
      display: flex;
      justify-content: space-between;
      margin-top: 2rem;
    }

    .publish-method {
      display: flex;
      justify-content: space-around;
      margin: 20px 0;
    }

    .publish-button {
      padding: 10px 20px;
      font-size: 16px;
      background-color: #f0f0f0;
      border: 1px solid #ccc;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .publish-button.active {
      background-color: #007bff;
      color: white;
      border-color: #007bff;
    }

    .publish-button:hover {
      background-color: #e7e7e7;
    }

    .publish-button.active:hover {
      background-color: #0056b3;
    }

    .heading-underline {
      text-decoration: underline;
      text-underline-offset: 4px; 
      margin-bottom: 1rem;
    }

    .toggle-wrapper {
      margin-top: 1rem;
    }

    .toggle-label {
      display: flex;
      align-items: center;
      font-weight: 500;
      gap: 10px;
      cursor: pointer;
    }

    .toggle-label input[type='checkbox'] {
      appearance: none;
      width: 40px;
      height: 20px;
      background: #ccc;
      border-radius: 999px;
      position: relative;
      outline: none;
      transition: 0.3s;
    }

    .toggle-label input[type='checkbox']::before {
      content: '';
      position: absolute;
      width: 18px;
      height: 18px;
      background: white;
      border-radius: 50%;
      top: 1px;
      left: 1px;
      transition: 0.3s;
    }

    .toggle-label input[type='checkbox']:checked {
      background: #10b981;
    }

    .toggle-label input[type='checkbox']:checked::before {
      transform: translateX(20px);
    }

    .publish-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
      font-family: Arial, sans-serif;
    }

    .publish-table th,
    .publish-table td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
      background: #f9f9f9
    }

    .publish-table th {
      background-color:rgb(177, 220, 236);
      font-weight: bold;
    }

  `]
})

export class ShiftsForPublishComponent implements OnInit {

  @Input() fromParent;
  // @Input() rosterType;
  // leftPanelDataSource: MatTableDataSource<any> = new MatTableDataSource<any>();
  // // columnsToDisplay = [
  // //   'select', 'mobile', 'sms', 'email', 'site_name', 'guard_name', 'start', 'end'
  // // ];
  // columnsToDisplay = [
  //   'select', 'mobile', 'email', 'site_name', 'guard_name', 'start', 'end'
  // ];

  // selectedIds = {
  //   // sms: [],
  //   mobile: [],
  //   email: [],
  // };

  // expandedElement: null;

  // userSSelection = new SelectionModel<any>(true, []);
  // mobileSelection = new SelectionModel<any>(true, []);
  // smsSelection = new SelectionModel<any>(true, []);
  // emailSelection = new SelectionModel<any>(true, []);

  days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  selectedDays: string[] = [...this.days];
  publishBy: string = '';
  activeStep: number = 0;
  lastStep = 1;
  resources: ResourceItem[] = [];
  publishList: PublishPerson[] = [];
  emailNotificationSelected = true;
  mobileNotificationSelected = true;

  constructor(private modalService: NgbModal, public global: GlobalVariable, private rosterService: RosterServiceService,
    private toast: ToastServiceService, private roster: RunsheetrosterService, public activeModal: NgbActiveModal) { }

  ngOnInit(): void {

    console.log("From Parent", this.fromParent);
    // this.leftPanelDataSource = new MatTableDataSource(this.fromParent);

  }

  // isAllUserSSelected() {
  //   const numSelected = this.userSSelection.selected.length;
  //   const numRows = this.leftPanelDataSource.data.length;
  //   return numSelected == numRows;
  // }

  // isAllMobileSelected() {
  //   const mobSelected = this.mobileSelection.selected.length;
  //   const mobRows = this.leftPanelDataSource.data.length;
  //   return mobSelected == mobRows;
  // }

  // isAllEmailSelected() {
  //   const emSelected = this.emailSelection.selected.length;
  //   const emRows = this.leftPanelDataSource.data.length;
  //   return emSelected == emRows;
  // }

  // isAllSmsSelected() {
  //   const smsSelected = this.smsSelection.selected.length;
  //   const smsRows = this.leftPanelDataSource.data.length;
  //   return smsSelected == smsRows;
  // }

  // onUserSCheckboxChange(row: any, event: any) {
  //   if (!event.checked) {
  //     this.userSSelection.deselect(row);
  //     this.emailSelection.deselect(row);
  //     this.mobileSelection.deselect(row);
  //     this.smsSelection.deselect(row);
  //   }
  //   else {
  //     this.userSSelection.select(row);
  //     this.emailSelection.select(row);
  //     this.mobileSelection.select(row);
  //     this.smsSelection.select(row);
  //   }
  // }

  // UserSMasterToggle(type?) {
  //   console.log(type);

  //   // if (type == 'id') {
  //   if (this.isAllUserSSelected()) {
  //     this.userSSelection.clear();
  //     this.mobileSelection.clear();
  //     this.smsSelection.clear();
  //     this.emailSelection.clear();
  //   } 
  //   else {
  //     this.leftPanelDataSource.data.forEach(row => {
  //       this.userSSelection.select(row);
  //       this.mobileSelection.select(row);
  //       this.smsSelection.select(row);
  //       this.emailSelection.select(row);
  //     });
  //   }
  // }

  // updateSelectedIds(selectionModel: SelectionModel<any>, type: string): void {
  //   this.selectedIds[type] = selectionModel.selected.map(item => item.id);
  // }

  // // Checkbox change function
  // onCheckboxChange(row: any, selectionModel: SelectionModel<any>, event: any, type: string): void {
  //   this.updateSelectedIds(selectionModel, type);
  //   if (!event.checked) {
  //     selectionModel.deselect(row);
  //   } else {
  //     selectionModel.select(row);
  //   }
  // }

  close(data?) {
    // this.modalService.dismissAll(data);
    this.activeModal.close(data);
  }

  // publishedShifts() {
  //   const phone = this.mobileSelection.selected.map(item => item.id);
  //   const email = this.emailSelection.selected.map(item => item.id);
  //   const sms = this.smsSelection.selected.map(item => item.id);
  //   const id = this.userSSelection.selected.map(item => item.id);
  //   const filteredUsers = this.userSSelection.selected.filter(item => item.doc_conf != null && item.conflict != null);

  //   let params = {
  //     start: this.global.start,
  //     end: this.global.end,
  //     phone: phone,
  //     email: email,
  //     sms: sms,
  //     id: id,
  //     admin_id: this.global.admin.admin_id
  //   }

  //   if (filteredUsers.length > 0) {
  //     this.removeTextArea('conflict', params)
  //   }
  //   else {
  //     this.removeTextArea('no', params)
  //   }
  // }

  // removeTextArea(conf, params) {
  //   const newConfirmBox = new ConfirmBoxInitializer();
  //   newConfirmBox.setTitle('Confirm Action');
  //   console.log(conf);

  //   if (conf == 'conflict') {
  //     newConfirmBox.setMessage('Some of Selected Shifts has conflicts are you sure you want to confirm this action?');
  //   }
  //   else {
  //     newConfirmBox.setMessage('Are you sure you want to confirm this action?');
  //   }

  //   // Choose layout color type
  //   newConfirmBox.setConfig({
  //     layoutType: DialogLayoutDisplay.SUCCESS, // SUCCESS | INFO | NONE | DANGER | WARNING
  //     animationIn: AppearanceAnimation.ZOOM_IN, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
  //     animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
  //     allowHtmlMessage: true,
  //     buttonPosition: 'right', // optional 
  //   });

  //   newConfirmBox.setButtonLabels('Confirm', 'Decline');

  //   // Simply open the popup and observe button click
  //   newConfirmBox.openConfirmBox$().subscribe(resp => {
  //     if (resp.success) {
  //       if(this.rosterType == 'run_sheet'){
  //         this.roster.publishedShifts(params).subscribe(({ message, success }) => {
  //           if (success) {
  //             this.roster.publishShiftData(params);
  //             this.close()
  //             this.toast.toastNotification(message, 'Shift Published!')
  //           }
  //         })
  //       }
  //       else{
  //         this.rosterService.publishedShifts(params).subscribe(({ message, success }) => {
  //           if (success) {
  //             // this.sendFilterData()
  //             this.close()
  //             this.toast.toastNotification(message, 'Shift Published!')
  //           }
  //         })
  //       }
  //     }
  //   });
  // }

  // isEmpty(value: any): boolean {
  //   let response = true;
  //   if (value != null && value != "null" && value != "undefined" && value != "") {
  //     response = false;
  //   }
  //   return response;
  // }





  //new design data start from here

  setPublishMethod(method: string) {
    this.publishBy = method;

    const requestData = {
      type: method,
      calendarStart: this.formatDate(this.fromParent.start),
      calendarEnd: this.formatDate(this.fromParent.end),
      customer_ids: this.fromParent.customer_id,
      state: this.fromParent.state,
      roster_id: this.fromParent.roster_id
    };
  
    this.fetchDataByType(requestData);
  }

  formatDate(dateStr: string): string {
    const [month, day, year] = dateStr.split('-');
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
  }

  fetchDataByType(data: any) {
    this.rosterService.getpublishShifts(data).subscribe(
      (res: any) => {
        if (res.success) {
          this.resources = res.data.map((item: any): ResourceItem => ({
            ...item,
            selected: true
          }));
        }
        // console.log('Fetched data:', res);
      },
      (error: any) => {
        this.toast.toastNotification1('Error fetching resources', 'Error');
      }
    );
  }

  toggleDay(day: string) {
    const i = this.selectedDays.indexOf(day);
    if (i > -1) {
      this.selectedDays.splice(i, 1); 
    } else {
      this.selectedDays.push(day); 
    }
  }

  selectAll() {
    this.resources.forEach(item => item.selected = true);
  }
  
  clearAll() {
    this.resources.forEach(item => item.selected = false);
  }

  goNext() {
    if (this.canProceed()) {
      const selectedResources = this.resources.filter(item => item.selected).map(item => ({
        name: 'selected_list',
        value: item.id
      }));

      const requestData = {
        type: this.publishBy,
        calendarStart: this.formatDate(this.fromParent.start),
        calendarEnd: this.formatDate(this.fromParent.end),
        selected_list: selectedResources
      };

      this.rosterService.getSelectedPublishShifts(requestData).subscribe(
        (res: any) => {
          if (res.success) {
            this.publishList = res.data.map(item => ({
              ...item,
              selected: true,
            }));
          }
          // console.log('Data:', res);
        },
        (error: any) => {
          this.toast.toastNotification1('Error fetching resources', 'Error');
        }
      );
      this.activeStep = 1;
    }
  }

  goBack() {
    this.activeStep = 0;
  }

  canProceed() {
    return this.selectedDays.length > 0 && this.resources.some(item => item.selected);
  }

  get allSelected(): boolean {
    return this.publishList.every(person => person.selected);
  }

  toggleAll(event: any) {
    const isChecked = event.target.checked;
    this.publishList.forEach(person => {
      person.selected = isChecked;
    });
  }

  publish(){

    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');

    newConfirmBox.setMessage('Are you sure you want to confirm this action?');

    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.SUCCESS, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.ZOOM_IN, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'right',
    });
    
    newConfirmBox.setButtonLabels('Confirm', 'Decline');
    
    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.success) {
        const publishData = {
          type: this.publishBy,
          calendarStart: this.formatDate(this.fromParent.start),
          calendarEnd: this.formatDate(this.fromParent.end),
          customer_ids: this.fromParent.customer_id,
          state: this.fromParent.state,
          selected_days: [],
          publish_selected_ids: [],
          notification: [],
        }
    
        publishData.selected_days = this.selectedDays.map(day => ({
          name: 'days_selected',
          value: day
        }));
    
        publishData.publish_selected_ids = this.publishList.filter(item => item.selected).map((item) => ({
          name: 'publish_selected_ids',
          value: item.id
        }));
    
        if (this.emailNotificationSelected) {
          publishData.notification.push({
            name: 'notification',
            value: 'email'
          });
        }

        if (this.mobileNotificationSelected) {
          publishData.notification.push({
            name: 'notification',
            value: 'push'
          });
        }
    
        // console.log("Submit data", publishData)
        this.rosterService.PublishShifts(publishData).subscribe(
          (res: any) => {
            if (res.success) {
              this.toast.toastNotification('Shifts are published successfully.', 'Success');
              this.close()
            }
          },
          (error: any) => {
            this.toast.toastNotification1('Error in Publish Shifts.', 'Error');
            this.close()
          }
        );
      }
    });
  }

  publishForAllCustomers: boolean = false;

  onToggleChange() {
    console.log('Toggle switched:', this.publishForAllCustomers);
    if (this.publishForAllCustomers) {
      console.log('Publish shifts for all customers enabled!');
    } else {
      console.log('Publish shifts for all customers disabled.');
    }
  }

}

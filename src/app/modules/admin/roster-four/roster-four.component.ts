import { DialogboxComponent } from './../dialogbox/dialogbox.component';
import { GlobalVariable } from 'app/shared/global';
import { Component, OnInit } from '@angular/core';
import moment from 'moment';
import { AbstractControl, FormControl, FormGroup } from '@angular/forms';

import {
  faCalendar,
  faClock,
  faUser,
  faTrash,
  faCreditCard,
  faPencil,
  faXmark,
} from "@fortawesome/free-solid-svg-icons";
import { TooltipPosition } from "@angular/material/tooltip";
import { NgbModal } from "@ng-bootstrap/ng-bootstrap";
import { MatDialog } from '@angular/material/dialog';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { ServiceService } from 'app/services/service.service';
import { AddCustomTemplateComponent } from '../models/add-custom-template/add-custom-template.component';

@Component({
  selector: 'app-roster-four',
  templateUrl: './roster-four.component.html',
  styleUrls: ['./roster-four.component.scss']
})
export class RosterFourComponent implements OnInit {

  selectedLocation
  locationArray=['lahore','Islamabad','karachi'];
  selectedOption
  selectedCustomers
  selectedStates
  sites = new FormControl('');
  job_id_match: any;
  site_id_match: any;
  edit_shift_side: boolean = false;
  positionOptions: TooltipPosition[] = ["below", "above", "left", "right"];
  position = new FormControl(this.positionOptions[2]);
  labelPosition: "before" | "after" = "after";

  isShowCustomRates:boolean=false;
  isShowbreak:boolean=false;
  isShowOthersDetail:boolean=false;
  isShowCustomRatesField:boolean=true;
  isShowCustomerOption:boolean=false;
  selectedRatesStatus:string;

  addShiftBasicForm: FormGroup;
  addShiftAdvanceForm: FormGroup;
  clickedboxDate;
  placeholder = "";
  selectedView;
  selectedStatus;
  

  popover = false;

  /**font awesome icon */
  faXmark = faXmark;
  faPencil = faPencil;
  faCreditCard = faCreditCard;
  faTrash = faTrash;
  calenderIcon = faCalendar;
  faClock = faClock;
  faUser = faUser;


sitesList: string[] = ['site 1', 'site 2', 'site 3', 'site 4',];

  public customers = ['Naveed','Usman','Rameez', 'Faizan', 'Zahid',];

  shifts=[
    {
      id:1,
      count:1,
      time:'20:00 - 07:00',
      date: 'Sun Jan 15',
    },
    {
      id:2,
      count:1,
      time:'10:00 -12:00',
      date: 'Fri Jan 13',
    },
    {
      id:3,
      count:1,
      time:'07:00 - 09:00',
      date: 'Wed Jan 11',
    },
    {
      id:4,
      count:1,
      time:'14:00 - 23:59',
      date: 'Tue Jan 10',
    },
    {
      id:5,
      count:1,
      time:'20:00 - 07:00',
      date: 'Sat Jan 14',
    },
    {
      id:6,
      count:1,
      time:'10:00 -12:00',
      date: 'Mon Jan 09',
    },
    {
      id:7,
      count:1,
      time:'07:00 - 09:00',
      date: 'Wed Jan 18',
    },
    {
      id:8,
      count:1,
      time:'20:00 - 23:59',
      date: 'Thu Jan 12',
    },
    

  ];

  buit_in_template = [
    {
      shift_name: "1 Shift",
      time: "09:00 - 12:00",
      // status: "completed",
      // path: "https://mdbootstrap.com/img/new/avatars/8.jpg",
    },
    {
      shift_name: "2 Shifts",
      time: "12:00 - 15:00",
      // status: "completed",
      // path: "https://mdbootstrap.com/img/new/avatars/8.jpg",
    },
    {
      shift_name: "2 Shifts",
      time: "18:00 - 23:00",
      // status: "missed",
      // path: "https://mdbootstrap.com/img/new/avatars/8.jpg",
    },
  ];

  public filteredCustomerList = this.customers.slice();


  constructor(public globals:GlobalVariable,
    public dialog: MatDialog,
    // public activeModal: NgbActiveModal,
    public server: ServiceService,
    private modalService: NgbModal,
    
    ) { 
    this.globals.rosterFour_SelectedWeek = [];
    var currentDate = moment();
    var weekStart = currentDate.clone().startOf('isoWeek');
    console.log('week start day ',weekStart);
    
    var weekEnd = currentDate.clone().endOf('isoWeek');
    this.globals.selectedDate = `${weekStart.format('DD MMM')} - ${weekEnd.format('DD MMM')}`;
    for (var i = 0; i <= 6; i++) {
      // this.globals.getWeekDays.push(moment(weekStart).add(i, 'days').format("ddd , DD/MM"));
      this.globals.rosterFour_SelectedWeek.push(moment(weekStart).add(i, 'days').format("ddd MMM DD"));

    };
console.log('current ' ,this.globals.rosterFour_SelectedWeek);

    
  }

  ngOnInit(): void {
  }

  openVerticallyCentered(content, p) {
    this.modalService.open(content, { centered: true, size: 'lg', windowClass: 'modelZ-index', animation: true });
    this.popover = true
  }


  /*Next week*/
  displayNextWeek() {
    this.getDays(1);

  }
   /*previous week*/
  displayPrevWeek() {
    this.getDays(-1);

  }
  timeTracker = moment();
  getDays(e) {
    this.globals.rosterFour_SelectedWeek = [];
    // console.log(this.days);

    if (e == 0) {
      this.timeTracker = moment();
    } else {
      this.timeTracker.add(e, 'weeks');
    }
    // Find start and end of week
    var startOfWeek = this.timeTracker.clone().startOf('isoWeek');
    var endOfWeek = this.timeTracker.clone().endOf('isoWeek');
    this.globals.rosterFourSelectedDate = `${startOfWeek.format('DD MMM')} - ${endOfWeek.format('DD MMM')}`;
    var day = startOfWeek;
    while (day.isSameOrBefore(endOfWeek)) {
      this.globals.rosterFour_SelectedWeek.push(moment(day).format("ddd MMM DD"));
      day = day.add(1, 'days');
    }
    console.log('after selection ',this.globals.rosterFour_SelectedWeek);
    
    return this.globals.rosterFour_SelectedWeek;
  }

  /*open modal to add custom template*/
  costomTemplateModal() {
    // this.modalService.open(AddCustomTemplateComponent, {
    //   windowClass: "custom-class",
    //   animation: true,
    // });
    // this.popover = true;
  }

  /*Add Custom Shift to array*/
  addCustomShift(custom_template: any, day: any, site_id: any) {
    console.log(custom_template, "Day", day, "resource id", site_id);
    custom_template.date = day;
    custom_template.site_id = site_id;
    custom_template.id = this.shifts.length + 1;
    this.shifts.push(custom_template);
    console.log(this.shifts);
  }

  /*Show the add Custome Template popOver*/
  showShiftTemplate(day: any, ) {
    this.job_id_match = day;
    
    console.log(this.job_id_match,);
    this.edit_shift_side = !this.edit_shift_side;
  }

  toggleCustomRates(){
    this.isShowCustomerOption=!this.isShowCustomerOption;
    //this.isShowCustomRates=!this.isShowCustomRates;
  }
  toggleBreaks(){
    this.isShowbreak=!this.isShowbreak;
  }
  toggleOthers(){
    this.isShowOthersDetail=!this.isShowOthersDetail;
  }

  get start_time(): AbstractControl {
    console.log("time ",this.addShiftAdvanceForm.get("start_time")!);
    
    return this.addShiftAdvanceForm.get("start_time")!;

  }

   /*Get the date and site name of current clicked box */
   getBoxDate(day: any, site: any) {
    console.log("day is ", day);
    console.log("site is ", site);

    //this.addShiftBasicForm.get("site_name").setValue(site.name);
    //this.addShiftBasicForm.get("site_id").setValue(site.site_id);
    this.clickedboxDate = moment(day, "ddd MMM DD").format("ddd MMM DD");
    console.log("clicked box data ",this.clickedboxDate);
  }

  /*Closing the open modals and popover and sidebar */
  close() {
    this.edit_shift_side = !this.edit_shift_side; //It closes successfully
  }

  showEditOption(job: any) {
    this.job_id_match = job.id;
    console.log(this.job_id_match);
    this.edit_shift_side = !this.edit_shift_side;
  }

  drop_data;
  drop(event: CdkDragDrop<string[]>) {
    console.log("event ",event);
    this.openDialog();
    
    this.shifts.forEach((element, index) => {
      console.log("event id ",event.item.data.id);
      
      if (event.item.data.id == element.id) {
        this.drop_data = element
        this.shifts.splice(index, 1)
      }
    });
    event.item.data.date = event.container.id
    this.shifts.push(event.item.data);
  }
  openDialog() {
    this.dialog.open(DialogboxComponent, { width: '300px',
    data: {name: 'shifts-dialog', message: ''},

   }).afterClosed()
  .subscribe(response => {
    console.log(" dialog is returned ",response);
  });

  }
}

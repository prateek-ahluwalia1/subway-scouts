import { Component, OnInit } from "@angular/core";
import {
  FormControl,
  FormGroup,
  Validators,
  AbstractControl,
} from "@angular/forms";
import { ServiceService } from "app/services/service.service";
import { GlobalVariable } from "app/shared/global";
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
import moment from "moment";
import { NgbModal } from "@ng-bootstrap/ng-bootstrap";

import { AddCustomTemplateComponent } from "../models/add-custom-template/add-custom-template.component";
import { MatDialog } from '@angular/material/dialog';
//import { UpdateTimeComponent } from '../../models/update-time/update-time.component';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
@Component({
  selector: "app-ento-design",
  templateUrl: "./ento-design.component.html",
  styleUrls: ["./ento-design.component.scss"],
})
export class EntoDesignComponent implements OnInit {
  public lat;
  public lng;
  isShowCustomRates:boolean=false;
  isShowbreak:boolean=false;
  isShowOthersDetail:boolean=false;
  isShowCustomRatesField:boolean=true;
  isShowCustomerOption:boolean=false;
  selectedRatesStatus:string;
  
  seasons: string[] = ['enable', 'disable',];


  job_id_match: any;
  site_id_match: any;
  edit_shift_side: boolean = false;
  getWeekDays:any=[]
  userType = "sites";
  toppings = new FormControl("");
  customer = new FormControl("");
  formFieldHelpers: string[] = [""];
  positionOptions: TooltipPosition[] = ["below", "above", "left", "right"];
  position = new FormControl(this.positionOptions[2]);
  labelPosition: "before" | "after" = "after";

  addShiftBasicForm: FormGroup;
  addShiftAdvanceForm: FormGroup;
  clickedboxDate;
  placeholder = "";
  selectedView;
  selectedStatus;
  selectedCustomers = [];

  popover = false;

  /**font awesome icon */
  faXmark = faXmark;
  faPencil = faPencil;
  faCreditCard = faCreditCard;
  faTrash = faTrash;
  calenderIcon = faCalendar;
  faClock = faClock;
  faUser = faUser;

  toppingList: string[] = [
    "Victoria",
    "New South Wales",
    "Tasmania",
    "Queensland",
    "Western Australia",
    "South Australia",
  ];
  customersList: string[] = [
    "Naveed",
    "Rameez",
    "Usman",
    "Faizan",
    "Bhatti",
    "Raees",
  ];

  buit_in_template = [
    {
      shift_name: "Naveed Qadir",
      time: "09:00 - 12:00",
      status: "completed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",
    },
    {
      shift_name: "Usman Bhatti",
      time: "12:00 - 15:00",
      status: "completed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",
    },
    {
      shift_name: "Rameez Raja",
      time: "18:00 - 23:00",
      status: "missed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",
    },
  ];

  allfoods = [
    { value: "lahore" },
    { value: "karachi" },
    { value: "Bahawalpur" },
    { value: "Multan" },
  ];
  selectedSites = [];
  // job_new_roster = [
  //   // {
  //   //   id: 1,
  //   //   site_id:1,
  //   //   date: 'Tue , 29/11',
  //   //   guard_name: 'Usman Bhatti',
  //   //   time: '17:00 - 23:00',
  //   //   status: 'completed',
  //   // },
  //   // {
  //   //   id: 2,
  //   //   site_id:1,
  //   //   date: 'Mon , 14/11',
  //   //   guard_name: 'Usman Bhatti',
  //   //   time: '17:00 - 23:00',
  //   //   status: 'completed',
  //   // },
  //   // {
  //   //   id: 3,
  //   //   site_id:1,
  //   //   date: 'Mon , 14/11',
  //   //   guard_name: 'Usman Bhatti',
  //   //   time: '17:00 - 23:00',
  //   //   status: 'completed',
  //   // },
  //   // {
  //   //   id: 4,
  //   //   site_id:1,
  //   //   date: 'Thu , 17/11',
  //   //   guard_name: 'Naveed Qadir',
  //   //   time: '17:00 - 23:00',
  //   //   status: 'missed',
  //   // },
  // ];


  job_new_roster = [
    {
      id: 1,
      site_id: 1,
      date: 'Mon  09',
      guard_name: 'Usman Bhatti',
      shift_name: "Naveed Qadir",
      time: "09:00 - 12:00",
      status: "completed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },
    {
      id: 2,
      site_id: 1,
      date: 'Tue  10',
      guard_name: 'Usman Bhatti',
      shift_name: "Usman Bhatti",
      time: "12:00 - 15:00",
      status: "completed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },
    {
      id: 3,
      site_id: 1,
      date: 'Wed  11',
      guard_name: 'Usman Bhatti',
      shift_name: "Naveed Qadir",
      time: "09:00 - 12:00",
      status: "completed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },
    {
      id: 4,
      site_id: 1,
      date: 'Thu  12',
      guard_name: 'Naveed Qadir',
      shift_name: "Usman Bhatti",
      time: "12:00 - 15:00",
      status: "completed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },
    {
      id: 5,
      site_id: 1,
      date: 'Fri  13',
      guard_name: 'Naveed Qadir',
      shift_name: "Rameez Raja",
      time: "18:00 - 23:00",
      status: "missed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },
    {
      id: 6,
      site_id: 1,
      date: 'Sat  14',
      guard_name: 'Naveed Qadir',
      shift_name: "Rameez Raja",
      time: "18:00 - 23:00",
      status: 'missed',
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },
    {
      id: 6,
      site_id: 1,
      date: 'Sun  15 ',
      shift_name: "Naveed Qadir",
      time: "09:00 - 12:00",
      status: "completed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },
    {
      id: 7,
      site_id: 1,
      date: 'Mon  16',
      guard_name: 'Naveed Qadir',
      shift_name: "Rameez Raja",
      time: "18:00 - 23:00",
      status: "missed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },
    {
      id: 8,
      site_id: 1,
      date: 'Tue  10',
      guard_name: 'Rameez',
      shift_name: "Rameez Raja",
      time: "18:00 - 23:00",
      status: "missed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },
    {
      id: 9,
      site_id: 1,
      date: 'Tue  18',
      guard_name: 'Rameez',
      shift_name: "Rameez Raja",
      time: "18:00 - 23:00",
      status: "missed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },
    {
      id: 10,
      site_id: 1,
      date: 'Thu  12',
      guard_name: 'Naveed Qadir',
      shift_name: "Rameez Raja",
      time: "18:00 - 23:00",
      status: "missed",
      path: "https://mdbootstrap.com/img/new/avatars/8.jpg",

    },

  ];

  city = [
    // {
    //   cityName:'Lahore',
    //   shift_schedule:[
    {
      time: "123 hour",
      id: 1,
    },
    {
      time: " 345 hour",
      id: 2,
    },
    {
      time: "121 hour",
      id: 3,
    },
    {
      time: "100 hour",
      id: 4,
    },
    {
      time: "200 hour",
      id: 5,
    },
    {
      time: "234 hour",
      id: 6,
    },
    {
      time: "261 hour",
      id: 7,
    },

    //   ]

    // }
  ];

  sites = [
    {
      site_id: 1,
      // name: 'Usman Bhatti',
      name: "Security Guard",
      phone: 123123133,
    },
    {
      site_id: 2,
      // name: 'Naveed Qadir',
      name: "Chef",

      phone: 923123123,
    },
    {
      site_id: 3,
      // name: 'Rameez',
      name: "Cleaner",
      phone: 923123456,
    },
    // {
    //   site_id:4,
    //   name:'Leave'
    // }
  ];

  //constructor
  constructor(
    public globals: GlobalVariable,
    public server: ServiceService,
    private modalService: NgbModal,
    public dialog: MatDialog,
    // public activeModal: NgbActiveModal
  ) {
    this.globals.entoSelectedWeek = [];
    var currentDate = moment();
    var weekStart = currentDate.clone().startOf("isoWeek");
    var weekEnd = currentDate.clone().endOf("isoWeek");
    // console.log("start week ",weekStart);
    // console.log("end week ",weekEnd);

    
    this.globals.entoSelectedDate = `${weekStart.format(
      "DD MMM"
    )} - ${weekEnd.format("DD MMM")}`;

    this.globals.currentWeekend = `${weekStart.format(
      "DD/MM/YYYY"
    )} - ${weekEnd.format("DD/MM/YYYY")}`;

    console.log("weekend ",this.globals.entoSelectedDate);
    
    for (var i = 0; i <= 6; i++) {
      // this.globals.entoSelectedWeek.push(moment(weekStart).add(i, 'days').format("ddd , DD/MM"));
      this.globals.entoSelectedWeek.push(
        moment(weekStart).add(i, "days").format("ddd  DD")
      );
    }

    console.log(this.globals.entoSelectedWeek);
  }

  ngOnInit(): void {
    // this.getLocation();
    //this.takCityName();

    /*Submit shift without advance option*/
    this.addShiftBasicForm = new FormGroup({
      site_name: new FormControl(""),
      start_time: new FormControl(""),
      end_time: new FormControl(""),
      select_guard: new FormControl(""),
      site_id: new FormControl(""),
    });

    /*Submit Shift advance Form */
    this.addShiftAdvanceForm = new FormGroup({
      site_name: new FormControl(""),
      start_time: new FormControl("", {
        validators: Validators.required,
        updateOn: "submit",
      }),
      end_time: new FormControl(""),
      select_guard: new FormControl(""),
      site_id: new FormControl(""),
      shift_payable: new FormControl(""),
      shift_chargeable: new FormControl(""),
      travel_time: new FormControl(""),
      overtime_value: new FormControl(""),
      payrate_level: new FormControl(""),
      chargerate_lavel: new FormControl(""),
      payrate: new FormControl(""),
      chargerate: new FormControl(""),
      public_holiday: new FormControl(""),
      covid_marshal: new FormControl(""),
      training: new FormControl(""),
      continuation: new FormControl(""),
      overtime_enabled: new FormControl(""),
      traveltime_enabled: new FormControl(""),
      unpublished_shift: new FormControl(""),
    });
  }

  // user Type

  getUserType(userType: string) {
    this.userType = userType;
  }

  reloadCurrentPage() {
    window.location.reload();
  }

  getLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          if (position) {
            console.log("position ", position);

            console.log(
              "Latitude: " +
                position.coords.latitude +
                "Longitude: " +
                position.coords.longitude
            );
            this.lat = position.coords.latitude;
            this.lng = position.coords.longitude;
            console.log(this.lat);
            console.log(this.lat);

            this.takCity(this.lat, this.lng);
          }
        },
        (error) => console.log(error)
      );
    } else {
      alert("Geolocation is not supported by this browser.");
    }
  }

  takCity(lat, lng) {
    // this.server.getCity(lat, lng).subscribe((res) => {
    //   console.log(res);
    // });
  }

  takCityName() {
    // let city = "lahore";
    // this.server.searchCity(city).subscribe((res) => {
    //   console.log(res);
    // });
  }

  /*Get the date and site name of current clicked box */
  getBoxDate(day: any, site: any) {
    console.log("day is ", day);
    console.log("site is ", site);

    this.addShiftBasicForm.get("site_name").setValue(site.name);
    this.addShiftBasicForm.get("site_id").setValue(site.site_id);
    this.clickedboxDate = moment(day, "ddd , DD/MM").format("DD/MM/YYYY");
    console.log(this.clickedboxDate);
  }

  onSubmit() {
    console.warn(this.addShiftBasicForm.value);
  }

  /*open modal to add custom template*/
  costomTemplateModal() {
    this.modalService.open(AddCustomTemplateComponent, {
      windowClass: "custom-class",
      animation: true,
    });
    this.popover = true;
  }

  /*Add Custom Shift to array*/
  addCustomShift(custom_template: any, day: any, site_id: any) {
    console.log(custom_template, "Day", day, "resource id", site_id);
    custom_template.date = day;
    custom_template.site_id = site_id;
    custom_template.id = this.job_new_roster.length + 1;
    this.job_new_roster.push(custom_template);
    console.log(this.job_new_roster);
  }

  /*Show the add Custome Template popOver*/
  showShiftTemplate(day: any, resource_id: any) {
    this.job_id_match = day;
    this.site_id_match = resource_id;
    console.log(this.job_id_match, this.site_id_match);
    this.edit_shift_side = !this.edit_shift_side;
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

  openVerticallyCentered(entoDetailContent, p) {
    this.modalService.open(entoDetailContent, {
      scrollable:true,
      // centered: true,
      // size: "lg",
      //fullscreen:true,
      windowClass: "entoDetailContent-class",
     // animation: true,
    });
    this.popover = true;
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
    return this.addShiftAdvanceForm.get("start_time")!;
  }

  closeModel(){
    console.log('dismiss');
    
    // this.activeModal.dismiss();
  }

  /*Submit the advance shift form Model*/

  submitShftAdvanceForm() {
    console.warn(this.addShiftAdvanceForm.value);
    this.addShiftAdvanceForm.markAllAsTouched();
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
    this.globals.entoSelectedWeek = [];
    // console.log(this.days);

    if (e == 0) {
      this.timeTracker = moment();
    } else {
      this.timeTracker.add(e, 'weeks');
    }
    // Find start and end of week
    var startOfWeek = this.timeTracker.clone().startOf('isoWeek');
    var endOfWeek = this.timeTracker.clone().endOf('isoWeek');
    this.globals.entoSelectedDate = `${startOfWeek.format('DD MMM')} - ${endOfWeek.format('DD MMM')}`;
    var day = startOfWeek;
    while (day.isSameOrBefore(endOfWeek)) {
      this.globals.entoSelectedWeek.push(moment(day).format("ddd  DD"));
      day = day.add(1, 'days');
    }
    return this.globals.entoSelectedWeek;
  }


  drop_data;
  drop(event: CdkDragDrop<string[]>) {
    console.log("event ",event);
    this.job_new_roster.forEach((element, index) => {
      if (event.item.data.id == element.id) {
        this.drop_data = element
        this.job_new_roster.splice(index, 1)
      }
    });
    event.item.data.date = event.container.id
    this.job_new_roster.push(event.item.data)
  }



}

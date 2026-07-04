import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { faCalendar, faClock,faSquarePlus, faUser, faTrash, faCreditCard, faPencil, faXmark, faL } from '@fortawesome/free-solid-svg-icons';
import { GlobalVariable } from 'app/shared/global';
import { StaffFortNightDetailComponent } from '../../component/staff-fort-night-detail/staff-fort-night-detail.component'
import moment from 'moment';

@Component({
  selector: 'app-one-week',
  templateUrl: './one-week.component.html',
  styleUrls: ['./one-week.component.scss']
})
export class OneWeekComponent implements OnInit {
  /**font awesome icon */
  faXmark = faXmark;
  faPencil = faPencil;
  faCreditCard = faCreditCard;
  faTrash = faTrash;
  calenderIcon = faCalendar;
  faClock = faClock;
  faUser = faUser;
  faSquarePlus=faSquarePlus;
  showShifts=false
  showShiftId;
  showArrow=false
  @Output() public found = new EventEmitter<boolean>();
  job_new_roster = [
    {
      id: 1,
      site_id: 1,
      date: 'Mon , 09/01',
      guard_name: 'Usman Bhatti',
      time: '17:00 - 23:00',
      shift_name: 'Usman Bhatti',
      status: 'completed',
    },
    {
      id: 2,
      site_id: 1,
      date: 'Tue , 10/01',
      guard_name: 'Usman Bhatti',
      time: '17:00 - 23:50',
      shift_name: "Evening shift",
      status: 'completed',
    },
    {
      id: 3,
      site_id: 1,
      date: 'Wed , 11/01',
      guard_name: 'Usman Bhatti',
      time: '17:00 - 23:00',
      shif_name: "Morning shift",
      status: 'completed',
    },
    {
      id: 4,
      site_id: 1,
      date: 'Thu , 12/01',
      guard_name: 'Naveed Qadir',
      time: '17:00 - 23:00',
      status: 'missed',
    },
    {
      id: 5,
      site_id: 1,
      date: 'Fri , 13/01',
      guard_name: 'Naveed Qadir',
      time: '17:00 - 23:00',
      status: 'missed',
    },
    {
      id: 6,
      site_id: 1,
      date: 'Sat , 14/01',
      guard_name: 'Naveed Qadir',
      time: '17:00 - 23:00',
      status: 'missed',
    },
    {
      id: 6,
      site_id: 1,
      date: 'Sun , 15/01',
      guard_name: 'Naveed Qadir',
      time: '17:00 - 23:00',
      status: 'missed',
    },
    {
      id: 7,
      site_id: 1,
      date: 'Mon , 02/01',
      guard_name: 'Naveed Qadir',
      time: '17:00 - 23:00',
      shift_name: 'Naveed Qadir',
      status: 'missed',
    },
    {
      id: 8,
      site_id: 1,
      date: 'Mon , 09/01',
      guard_name: 'Rameez',
      time: '17:00 - 23:00',
      shift_name: 'Rameez',
      status: 'missed',
    },

  ];
  sites = [
    {
      site_id: 1,
      name: 'Usman Bhatti',
      phone: 123123133,
    },
    {
      site_id: 2,
      name: 'Naveed Qadir',
      phone: 923123123,
    },
    {
      site_id: 3,
      name: 'Rameez Arrain',
      phone: 923123123,
    },

  ];
  constructor(
    public globals: GlobalVariable,
    public dialog: MatDialog,
  ) {

    this.globals.getWeekDays = [];
    var currentDate = moment();
    var weekStart = currentDate.clone().startOf('isoWeek');
    var weekEnd = currentDate.clone().endOf('isoWeek');
    this.globals.selectedDate = `${weekStart.format('DD MMM')} - ${weekEnd.format('DD MMM')}`;
    for (var i = 0; i <= 6; i++) {
      this.globals.getWeekDays.push(moment(weekStart).add(i, 'days').format("ddd , DD/MM"));
    };
  }

  ngOnInit(): void {
    // this.dialog.open(StaffFortNightDetailComponent, {
    //   width: '1000px'}
    // )
  
  }

  OpenShiftRoster(){
    this.globals.roster_three_shift_component=true
    this.globals.roster_three_sidebar=false
  }

  drop_data;
  drop(event: CdkDragDrop<string[]>) {
    this.job_new_roster.forEach((element, index) => {
      if (event.item.data.id == element.id) {
        this.drop_data = element
        this.job_new_roster.splice(index, 1)
      }
    });
    event.item.data.date = event.container.id
    this.job_new_roster.push(event.item.data)
  }


  showShift(id){
    this.showShiftId=id
    this.showShifts=!this.showShifts
    // this.showArrow=!this.showArrow
  }

  // staffDetail(){
  //   this.dialog.open(StaffFortNightDetailComponent, {
  //     width: '800px'}
  //   )
    
  // }

}

import { Component, Input, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastServiceService } from 'app/services/toast-service.service';
import { JobRoster1Service } from 'app/services/job-roster1.service';

@Component({
  selector: 'app-copy-daywise-shifts',
  templateUrl: './copy-daywise-shifts.component.html',
  styleUrls: ['./copy-daywise-shifts.component.scss']
})
export class CopyDaywiseShiftsComponent implements OnInit {

  selectedMonth: string = '';
  months: string[] = [];
  daysInMonth: string[] = [];
  selectedDaysMap: { [key: string]: boolean } = {};
  @Input() copyShiftId: any;

  constructor(public activeModal: NgbActiveModal, private toast: ToastServiceService, private rosterService: JobRoster1Service,) { }

  ngOnInit(): void {
    this.generateMonthNames();
    this.updateDaysForSelectedMonth();
  }

  generateMonthNames() {
    const currentYear = new Date().getFullYear();
    this.months = [];
  
    for (let i = 0; i < 12; i++) {
      const date = new Date(currentYear, i);
      const monthName = date.toLocaleString('default', { month: 'long' });
      this.months.push(monthName);
    }
  
    const currentMonthIndex = new Date().getMonth();
    this.selectedMonth = this.months[currentMonthIndex];
  
    this.updateDaysForSelectedMonth();
  }

  updateDaysForSelectedMonth() {
    const currentYear = new Date().getFullYear();
    const monthIndex = this.months.indexOf(this.selectedMonth);
    const totalDays = new Date(currentYear, monthIndex + 1, 0).getDate();
  
    this.daysInMonth = [];
    this.selectedDaysMap = {};
  
    for (let day = 1; day <= totalDays; day++) {
      const formatted = new Date(currentYear, monthIndex, day).toLocaleDateString('en-US', {
        day: '2-digit',
        month: 'short',
      });
      
      this.daysInMonth.push(formatted);
      this.selectedDaysMap[formatted] = false;
    }
  }

  toggleDaySelection(day: number): void {
    this.selectedDaysMap[day] = !this.selectedDaysMap[day];
  }

  copyShift() {
    const currentYear = new Date().getFullYear();
    const monthIndex = this.months.indexOf(this.selectedMonth);
  
    const selectedDays = Object.keys(this.selectedDaysMap).filter(day => this.selectedDaysMap[day])
      .map(dayStr => {
        const [monthShort, dayNum] = dayStr.split(' ');
        const date = new Date(`${monthShort} ${dayNum}, ${currentYear}`);
        const timestamp = Math.floor(date.getTime() / 1000); // convert to UNIX timestamp in seconds
  
        return {
          name: 'selected_days',
          value: timestamp
        };
      });
  
    const dataToSend = {
      copy_days: selectedDays,
      id: this.copyShiftId
    };
  
    // console.log('Copying shifts with:', dataToSend);
    this.rosterService.copyDaywiseShift(dataToSend).subscribe(({ success, message }) => {
      if (success) {
        this.toast.toastNotification(message, 'Copy Shift!')
        this.activeModal.close(dataToSend);
      } else {
        this.toast.toastNotification1(message, 'Copy Shift!')
      }
    });
  }
  

}

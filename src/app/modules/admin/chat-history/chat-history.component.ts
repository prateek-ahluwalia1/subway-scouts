import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import moment from 'moment';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { SmsServicesService } from 'app/services/sms-services.service';
import { GlobalVariable } from 'app/shared/global';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { ChatDetailsComponent } from './../chat-details/chat-details.component';
import { ToastServiceService } from 'app/services/toast-service.service';

interface Export {
  value: string;
  viewValue: string;
}

@Component({
  selector: 'app-chat-history',
  templateUrl: './chat-history.component.html',
  styleUrls: ['./chat-history.component.scss']
})
export class ChatHistoryComponent implements OnInit {
  exports: Export[] = [
    { value: 'excel-0', viewValue: 'Excel' },
    { value: 'pdf-1', viewValue: 'PDF' },
  ];
  Filter: any[] = [];
  currentDate = moment();
  selectedDate: string;
  search_term: string;
  curDate: string;
  chat_type: string;
  @ViewChild("content") content: ElementRef;
  routeId: any;

  constructor(
    private smsService: SmsServicesService,
    private modalService: NgbModal,
    private global: GlobalVariable,
    private trackAdmin: TrackAdminActivityService,
    private toast: ToastServiceService
  ) {

    let data;
    data = {
      to: moment().endOf('month').format('YYYY-MM-DD'),
      from: moment().startOf('month').format('YYYY-MM-DD')
    };
    this.smsService.allSmsHistory(data).subscribe(({ success, chat }) => {
      if (success) {
        chat.forEach(element => {
          element.names = element.first_name + (element.middle_name ? ' ' + element.middle_name : '') + (element.last_name ? ' ' + element.last_name : '');
        });
        this.Filter = chat;
      }
    });
    const routerId = localStorage.getItem('routerId');
    if (!routerId) {
      this.activity();
    }
  }

  ngOnInit(): void {
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');
    this.trackAdmin.storeActivity('Exit Chat History Page', 'Exit Chat History Page', this.routeId).subscribe(() => { });
  }

  activity() {
    this.trackAdmin.storeActivity('Chat History Page', 'Enter in Chat History Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id);
        this.routeId = id;
      }
    });
  }

  today() {
    const weekStart = this.currentDate.clone().startOf('isoWeek');
    this.selectedDate = moment(weekStart).format('YYYY-MM-DD');
    this.curDate = moment(weekStart).format('YYYY-MM-DD');
  }

  currentMonth() {
    this.curDate = moment().startOf('month').format('YYYY-MM-DD');
    this.selectedDate = moment().endOf('month').format('YYYY-MM-DD');
  }

  previousMonth() {
    this.curDate = moment().subtract(1, 'months').startOf('month').format('YYYY-MM-DD');
    this.selectedDate = moment().subtract(1, 'months').endOf('month').format('YYYY-MM-DD');
  }


  applyFilters() {
    let data;
    data = {
      to: moment(this.selectedDate).format('MM/DD/YYYY'),
      from: moment(this.curDate).format('MM/DD/YYYY')
    };

    data.admin_id = this.global.admin.admin_id;
    data.type = this.chat_type ? this.chat_type : '';
    this.smsService.allSmsHistory(data).subscribe(({ success, chat }) => {
      if (success) {
        this.Filter = chat;
      }
      else {
        this.toast.toastNotification1('Histroy Not Found', 'Not Found')
      }
    }, error => {
      this.toast.toastNotification1('Something went wrong. Please contact with support team', 'Error')
    });
  }

  date(time) {
    return moment(time * 1000).format('DD-MM-YYYY');
  }

  makePdf() {
    let dates = {
      to: this.selectedDate,
      from: this.curDate
    };
    let modal = this.modalService.open(ChatDetailsComponent, { size: 'xl' });
    modal.componentInstance.chats = this.Filter;
    modal.componentInstance.dates = dates;
    modal.componentInstance.search_term = this.search_term;
  }
}

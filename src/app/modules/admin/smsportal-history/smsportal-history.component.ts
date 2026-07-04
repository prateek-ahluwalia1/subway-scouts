import { Component, OnInit, OnDestroy, ViewChild, ElementRef, Renderer2 } from '@angular/core';
import moment from 'moment';
import { MatDatepickerInputEvent } from '@angular/material/datepicker';
import { GlobalVariable } from 'app/shared/global';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { SmsServicesService } from 'app/services/sms-services.service';

@Component({
  selector: 'app-smsportal-history',
  templateUrl: './smsportal-history.component.html',
  styleUrls: ['./smsportal-history.component.scss']
})
export class SMSPortalHistoryComponent implements OnInit, OnDestroy {
  filteredItems: any[] = [];
  message: string = '';
  id: any = [];
  selectedContact: any={};
  messages: any[] = [];
  selectedIndex: number;
  users: any[] = [];
  searchTerm: string = '';
  user_id: any;
  intervalId: any;
  usersCount: number;
  smshistPermissions;
  selectedDate: any;
  roomsFilter = {
    date: ''
  };
  routeId: any;
  @ViewChild('chatContainer', { static: false }) chatContainer: ElementRef;

  constructor(
    private smsService: SmsServicesService,
    private global: GlobalVariable,
    private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService,
    private toast: ToastServiceService,
    private renderer: Renderer2,
  ) {
    console.log(this.selectedContact);
    this.selectedContact = {}
    this.smsService.userChatGet().subscribe(({ message, users, success }) => {
      if (success) {
        this.users = users;
        this.filteredItems = this.users;
        this.usersCount = this.filteredItems.length;
        this.selectedContact = this.filteredItems[this.selectedIndex];
      }
    });

    let routerId = localStorage.getItem('routerId');
    if (!routerId) {
      this.activity();
    }
  }

  addEvent(type: string, event: MatDatepickerInputEvent<Date>) {
    this.selectedDate = moment(event.value).format('MM/DD/YYYY');
    this.id = '';
    this.messages = [];

    const formdata = new FormData();
    formdata.append('date', this.selectedDate);
    this.smsService.userChatGet(formdata).subscribe((res) => {
      this.users = res.users;
      this.filteredItems = this.users;
      this.selectedContact = this.filteredItems[this.selectedIndex];
    });
  }

  onDate(event): void {
    this.roomsFilter.date = event;
  }

  ngOnInit(): void {
    const per = this.permissionService.getPermissionsByTitle('Communications');
    this.smshistPermissions = per?.childPage?.find((item) => item.title === 'SMS Portal History');
  }

  newUserMessage() {
    this.smsService.getNewMessage(this.user_id).subscribe((res) => {
      if (res.success) {
        this.messages.push({
          direction: 'in',
          msg_body: res.msg
        });
      }
    });
  }

  mergeName(name) {
    return `${name.first_name} ${name.last_name}`;
  }

  search() {
    if (!this.searchTerm || this.searchTerm.length === 0) {
      this.filteredItems = this.users;
      return;
    }
    const searchTermLower = this.searchTerm.toLowerCase();
    const results = this.users.filter(item => {
      if (item && item.first_name && item.last_name && item.to_number) {
        const firstNameLower = item.first_name.toLowerCase();
        const lastNameLower = item.last_name.toLowerCase();
        const toNumberLower = item.to_number.toLowerCase();
        return firstNameLower.includes(searchTermLower) ||
          lastNameLower.includes(searchTermLower) || toNumberLower.includes(searchTermLower);
      }
      return false;
    });

    this.filteredItems = results;
  }



  setIndex(index?, user?) {
    this.messages = [];
    this.id = user.id;
    this.user_id = user.user_id;
    this.selectedIndex = index;
    this.selectedContact = this.filteredItems[this.selectedIndex];
    const fetchUserChat = () => {
      const data = {
        user_id: this.id,
        date: this.selectedDate ? this.selectedDate : ''
      };

      this.smsService.getUserChat(data).subscribe(({ success, message, chat }) => {
        if (success) {
          this.messages = chat;
          setTimeout(() => {
            this.scrollToBottom();
          }, 100);
        } else {
          this.toast.toastNotification1(message, 'Request Incomplete!');
        }
      });
    };
    fetchUserChat();
    this.intervalId = setInterval(fetchUserChat, 20000);
  }

  sendMessage() {
    const data = {
      to: [this.id],
      body: this.message,
      admin_id: this.global.admin.admin_id
    };
    const currentUnixTimestamp = this.time(Math.floor(Date.now() / 1000));
    if (this.message) {
      this.messages.push({
        direction: 'out',
        msg_body: this.message,
        datetime: currentUnixTimestamp
      });
      setTimeout(() => {
        this.scrollToBottom();
      }, 100);
      this.smsService.smsSend(data).subscribe(({ message, success }) => {
        if (success) {
          this.toast.toastNotification(message, 'SMS Operation!')
          this.message = '';
        }
        else {
          this.toast.toastNotification(message, 'SMS Operation!')
        }
      },
        (error) => {
          this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Request Incomplete!');
        }
      );
    }
    else {
      this.toast.toastNotification1('Please write message', 'Request Incomplete!')
    }


  }

  ngOnDestroy() {
    clearInterval(this.intervalId);
    this.trackAdmin.storeActivity('SMS Portal History', 'Exit SMS Portal History', this.routeId).subscribe((res) => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    });
  }

  time(time) {
    return moment(time * 1000).format('DD-MM-YYYY HH:mm');
  }

  activity() {
    this.trackAdmin.storeActivity('SMS History Page', 'Enter in SMS History Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id);
        this.routeId = id;
      }
    });
  }

  scrollToBottom() {
    this.renderer.setProperty(this.chatContainer.nativeElement, 'scrollTop', this.chatContainer.nativeElement.scrollHeight);
  }
}

import { HttpHeaders } from '@angular/common/http';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, OnInit, Renderer2, ViewChild } from '@angular/core';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { ActivatedRoute } from '@angular/router';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { NgxSpinnerService } from 'ngx-spinner';
import Pusher from 'pusher-js';
import { ChatService } from '../chat.service';

@Component({
  selector: 'app-internal-users',
  templateUrl: './internal-users.component.html',
  styleUrls: ['../card/card.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class InternalUsersComponent implements OnInit {

  contacts: any;
  searchContact: string;
  message: string;
  userType: string;
  userId: any;
  isShowContacts: boolean = false;
  chats: any;
  profile: any;
  receiverProfile: any;
  chatHistory: any[] = [];
  conversationStart: boolean = false;
  file: any;

  @ViewChild('chatContainer', { static: false }) chatContainer: ElementRef;
  @ViewChild('myIframe', { static: true }) iframe: ElementRef;

  newData: any = {};
  selectedChat: any;
  internalUser
  constructor(private service: ChatService, private _changeDetectorRef: ChangeDetectorRef,
    public global: GlobalVariable, private toast: ToastServiceService, private renderer: Renderer2,
    private spinner: NgxSpinnerService, private sanitizer: DomSanitizer, private elementRef: ElementRef,
    private route: ActivatedRoute) { }

  ngOnInit(): void {
    this.internalUser = this.route.snapshot.url[0]?.path;
    this.initializePusher();
    this.profile = {
      email: this.global?.admin.admin_email ?? '',
      avatar: '',
      name: this.global?.admin.admin_name ?? '',
      id: this.global?.admin.admin_id ?? ''
    };


    this.getChats();

    this.route.queryParamMap.subscribe(params => {
      const user_id = params.get('user');
      const type = params.get('type');
      const name = params.get('name');
      if (user_id && type) {
        this.conversationStart = true
        this.receiverProfile = {
          id: user_id,
          name: name ?? 'N/A',
          image: '',
          type: type,
        }
        this.userType = type
        this.userId = user_id
        this.findChatHistory()
        this._changeDetectorRef.markForCheck();
      }
    })

  }

  initializePusher(): void {
    const pusher = new Pusher('a1a91e8e3217abe50dee', {
      cluster: 'ap2',
    });

    if (this.internalUser == 'customer') {
      const channel = pusher.subscribe('customers-chat-channel');
      channel.bind('customers-chat-channel', (data) => {
        if (this.global.admin.admin_id == data.user_id || data?.customer_id == this.global.admin.admin_id) {
          this.handlePusherData(data);
        }
      });
    }
    else if (this.internalUser == 'staff') {
      const channel = pusher.subscribe('guards-chat-channel');
      channel.bind('guards-chat-channel', (data) => {
        if (this.global.admin.admin_id == data.user_id || data?.staff_id == this.global.admin.admin_id) {
          this.handlePusherData(data);
        }
      });
    }
    else if (this.internalUser == 'contractor') {
      const channel = pusher.subscribe('contractors-chat-channel');
      channel.bind('contractors-chat-channel', (data) => {
        console.log(data);
        if (this.global.admin.admin_id == data.user_id || data?.staff_id == this.global.admin.admin_id) {
          this.handlePusherData(data);
        }
      });
    }

  }

  handlePusherData(data: any): void {

    this.getChatById(data);
    this._changeDetectorRef.markForCheck()

  }


  getContacts() {
    this.isShowContacts = true;
    this.service.getUserContacts(this.internalUser).subscribe(res => {
      this.contacts = res.data;
      this._changeDetectorRef.markForCheck();
    });
  }

  sendMessage() {
    console.log(this.userType);
    
    this.spinner.show()
    let data: any = {}
    if (!this.message && !this.file) {
      this.spinner.hide()
      return;
    }
    switch (this.userType) {
      case 'staff':
      case 'customer':
      case 'contractor':
        if (this.global.admin.chat_type === 'super-admin' || this.global.admin.chat_type === 'admin') {
          data.user_id = this.global.admin.admin_id;
          data[`${this.userType}_id`] = this.userId;
          data.send_by = this.global.admin.chat_type
        }
        break;
      case 'admin':
        if (this.global.admin.chat_type === 'staff' || this.global.admin.chat_type === 'customer' || this.global.admin.chat_type === 'contractor') {
          data.admin_id = this.global.admin.admin_id;
          data[`${this.global.admin.chat_type}_id`] = this.userId;
          data.send_by = this.global.admin.chat_type
        }
        break;
      default:
        break;
    }

    if (this.message) {
      data.message = this.message
      data.file = this.file
      data.user_name = this.global.admin.admin_name
      data.type = this.internalUser
      this.service.sendMessageToExternal(data).subscribe(({ success, status }) => {
        if (success) {
          this.toast.toastNotification(status, 'Message Operation')
          this.message = ''
          this.file = ''
          this.newData = {}
        }
        else {
          this.toast.toastNotification(status, 'Message Operation')
        }
        this.spinner.hide()

      }, (error => {
        this.spinner.hide()
        this.toast.toastNotification('Something went wrong. Please contact with support team.', 'Message Operation')
      }))

      this._changeDetectorRef.markForCheck()
    }
  }

  getChats() {
    this.service.getChats(this.internalUser).subscribe(({ success, message }) => {
      if (success) {
        this.handleChats(message);
      }
      this._changeDetectorRef.markForCheck();
    });
  }
  handleChats(chats: any[]): void {

    chats.forEach(element => {
      if (element.contractor) {
        element.contacts = {
          type: 'contractor',
          name: element.contractor.name,
          id: element.contractor.id,
          image: element.contractor.profile_image,
          message: element.message,
          updated_at: element.updated_at

        };
      } else if (element.customer) {
        element.contacts = {
          type: 'customer',
          name: element.customer?.name,
          id: element.customer.id,
          image: element.customer.profile_image,
          message: element.message,
          updated_at: element.updated_at

        };
      } else if (element.guardz) {
        element.contacts = {
          type: 'staff',
          name: element.guardz.first_name + ' ' + element.guardz.last_name,
          id: element.guardz.id,
          image: element.guardz.profile_image,
          message: element.message,
          updated_at: element.updated_at

        };
      }
      else {
        element.contacts = {
          type: 'admin',
          name: element.admin?.name,
          id: element.admin?.id,
          image: element.admin?.profile_image,
          message: element?.message
        };
      }
    });
    console.log(chats);

    this.chats = chats;
  }

  // this function run when click on add new contact for chat 
  navigate(obj: any) {
    console.log(obj);
    this.conversationStart = true
    this.receiverProfile = {
      id: obj?.id,
      name: obj?.name,
      image: obj?.image,
      is_online: obj?.is_online,
    }
    this.userType = this.internalUser
    this.userId = obj?.id
    this.backToChat()
    this.findChatHistory()
    this._changeDetectorRef.markForCheck();

  }

  backToChat() {
    this.isShowContacts = !this.isShowContacts
    this.getChats()
    this.contacts = null
  }

  // this function run when had already contact for chat 
  getChatHistory(item) {
    this.selectedChat = item
    this.conversationStart = true
    this.receiverProfile = {
      id: item?.contacts?.id,
      name: item?.contacts?.name,
      image: item?.contacts?.name,
      type: item?.contacts?.name,
    }
    this.userType = item?.contacts.type
    this.userId = item?.contacts.id
    this.findChatHistory()
    this._changeDetectorRef.markForCheck();
  }

  findChatHistory() {
    let data: any = {}
    switch (this.userType) {
      case 'staff':
      case 'customer':
      case 'contractor':
        if (this.global.admin.chat_type === 'super-admin' || this.global.admin.chat_type === 'admin') {
          data.user_id = this.global.admin.admin_id;
          data[`${this.userType}_id`] = this.userId;
          data.send_by = this.global.admin.chat_type
        }
        break;
      case 'admin':
        if (this.global.admin.chat_type === 'staff' || this.global.admin.chat_type === 'customer' || this.global.admin.chat_type === 'contractor') {
          data.admin_id = this.global.admin.admin_id;
          data[`${this.global.admin.chat_type}_id`] = this.userId;
          // data.guard_id = this.userType;
          data.send_by = this.global.admin.chat_type
        }
        break;
      default:
        break;
    }
    this.getChatById(data)
  }

  getChatById(data) {
    data.type = this.internalUser
    this.service.getChatById(data).subscribe(({ success, data }) => {
      if (success) {
        this.chatHistory = data
        setTimeout(() => {
          this.scrollToBottom();
        }, 100);
        this._changeDetectorRef.markForCheck()

      }
    }, (error => {
      this.toast.toastNotification1('Something went wrong. Please contact with support team', 'Request Incomplete!')
    }))
  }

  scrollToBottom() {

    this.renderer.setProperty(this.chatContainer.nativeElement, 'scrollTop', this.chatContainer.nativeElement.scrollHeight);
  }



  uploadFile
  addAttachements() {
    let input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*,application/pdf";
    input.onchange = (_) => {
      let files = Array.from(input.files);
      if (!files || files.length === 0) return;
      this.uploadFile = files[0];
      const myFormData = new FormData();
      const headers = new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      })
      myFormData.append('file', this.uploadFile, this.uploadFile.name);
      myFormData.append('folder', 'chat')
      this.spinner.show()
      this.service.attachments(myFormData, {
        headers: headers
      }).subscribe(
        response => {
          if (response.success) {
            this.file = response.url
            this.spinner.hide()
            setTimeout(() => {
              this.scrollToBottom();
            }, 100);
          }
          this._changeDetectorRef.markForCheck();

        },
        (error) => {
          this.toast.toastNotification1('Something went wrong. Please try again later', 'Request Incomplete!')
          console.error(error);
          this.spinner.hide()
          this._changeDetectorRef.markForCheck();

        }
      );

    };
    input.click();
  }

  sanitizeUrl(url: string): SafeResourceUrl {
    return this.sanitizer.bypassSecurityTrustResourceUrl(url);
  }


  handleBrokenImage(event: Event) {
    const imgElement = event.target as HTMLImageElement;
    imgElement.src = '../../../../assets/images/download.png';
  }

  openFile(fileUrl: string): void {
    if (fileUrl) {
      window.open(fileUrl, '_blank');
    }
  }

  truncateMessage(message: string | undefined): string {
    if (!message) return '';
    return message.length > 7 ? message.slice(0, 20) + '...' : message;
  }

}

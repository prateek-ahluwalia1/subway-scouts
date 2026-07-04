import { HttpHeaders } from '@angular/common/http';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, OnDestroy, OnInit, Renderer2, ViewChild } from '@angular/core';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { ActivatedRoute } from '@angular/router';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { NgxSpinnerService } from 'ngx-spinner';
import Pusher from 'pusher-js';
import { ChatService } from '../chat.service';
import { Subject, takeUntil } from 'rxjs';

@Component({
  selector: 'app-admin',
  templateUrl: './admin.component.html',
  styleUrls: ['../card/card.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminComponent implements OnInit, OnDestroy {

  contacts: any;
  searchContact: string;
  message: string;
  userId: any;
  isShowContacts: boolean = false;
  chats: any;
  profile: any;
  receiverProfile: any;
  chatHistory: any[] = [];
  conversationStart: boolean = false;
  file: any;
  isImage: boolean = false;

  @ViewChild('chatContainer', { static: false }) chatContainer: ElementRef;
  @ViewChild('myIframe', { static: true }) iframe: ElementRef;

  newData: any = {};
  selectedChat: any;

  private _unsubscribeAll: Subject<any> = new Subject<any>();

  constructor(private service: ChatService, private _changeDetectorRef: ChangeDetectorRef,
    public global: GlobalVariable, private toast: ToastServiceService, private renderer: Renderer2,
    private spinner: NgxSpinnerService, private sanitizer: DomSanitizer,
    private route: ActivatedRoute) { }

  ngOnInit(): void {
    this.initializePusher();

    this.service.admins$
      .pipe(takeUntil(this._unsubscribeAll))
      .subscribe((contact: any) => {
        if (contact && contact.data) {
          this.contacts = contact.data.filter(user => user.id !== this.global.admin.admin_id);
        }
        this._changeDetectorRef.markForCheck();
      });

    this.service.previousContacts$
      .pipe(takeUntil(this._unsubscribeAll))
      .subscribe((preContact: any) => {
        if (preContact && preContact.message) {
          this.chats = this.extractContactsFromMessages(preContact.message)
          this._changeDetectorRef.markForCheck();
        }
      });



    this.profile = {
      email: this.global?.admin.admin_email ?? '',
      avatar: '',
      name: this.global?.admin.admin_name ?? '',
      id: this.global?.admin.admin_id ?? ''
    };


    this.getChats();

    this.route.queryParamMap.subscribe(params => {
      console.log(params);

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
        this.userId = user_id
        this.findChatHistory()
        this._changeDetectorRef.markForCheck();
      }
    })

  }

  extractContactsFromMessages(messages: any[]): any[] {
    const uniqueContacts = new Map();
    messages.forEach(message => {
      if (message.sender_id !== this.global.admin.admin_id) {
        uniqueContacts.set(message.sender.id, message.sender);
      }
      if (message.receiver_id !== this.global.admin.admin_id) {
        uniqueContacts.set(message.receiver.id, message.receiver);
      }
    });
    return Array.from(uniqueContacts.values());
  }

  initializePusher(): void {
    const pusher = new Pusher('a1a91e8e3217abe50dee', {
      cluster: 'ap2',
    });

    const channel = pusher.subscribe('admin-chat-channel');
    channel.bind('admin-chat-channel', (data) => {
      if (data?.sender_id == this.global.admin.admin_id || data?.receiver_id == this.global.admin.admin_id) {
        this.handlePusherData(data);
      }
    });
  }

  handlePusherData(data: any): void {
    this.getChatById(data);
    this._changeDetectorRef.markForCheck()
  }


  getContacts() {
    this.isShowContacts = !this.isShowContacts;
    this.service.getAllAdminsData().subscribe();
  }

  preventNewLine(event: KeyboardEvent): void {

    if (event.key === 'Enter') {
      event.preventDefault();
      this.sendMessage();
    }
  }
  isSendButtonDisabled(): boolean {
    return !this.message?.trim() && !this.file;
  }
  sendMessage() {
    this.spinner.show()
    if (this.isSendButtonDisabled()) {
      this.spinner.hide();
      return;
    }
    let data: any = {
      message: this.message,
      file: this.file,
      send_by: this.global.admin.admin_name,
      receiver_id: this.receiverProfile.id,
      sender_id: this.global.admin.admin_id
    };
    this.service.sendMessageToAdmin(data).subscribe(({ success, status }) => {
      if (success) {
        this.toast.toastNotification(status, 'Message Operation')
        this.message = ''
        this.file = ''
        this.newData = {}
        this.checkIfImage(this.file);
      }
      else {
        this.toast.toastNotification(status, 'Message Operation')
      }
      this.spinner.hide()
    }, (error => {
      this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Message Operation')
    }))

    this._changeDetectorRef.markForCheck()
  }

  getChats() {
    this.service.previousContactAdmin().subscribe();
  }

  // this function run when click on add new contact for chat 
  navigate(obj: any) {
    this.conversationStart = true
    this.receiverProfile = {
      id: obj?.id,
      name: obj?.name,
      image: obj?.image,
      is_online: obj?.is_online,
    }
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
    console.log(item);
    this.selectedChat = item
    this.conversationStart = true
    this.receiverProfile = {
      id: item?.id,
      name: item?.name,
      image: item?.image,
      is_online: item?.is_online,
    }
    this.userId = item?.id
    this.findChatHistory()
    this._changeDetectorRef.markForCheck();
  }

  findChatHistory() {
    let data = {
      sender_id: this.global.admin.admin_id,
      receiver_id: this.receiverProfile.id
    }
    this.getChatById(data)
  }

  getChatById(data) {

    this.service.getMessageAdmin(data).subscribe(({ success, data }) => {
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
            this.checkIfImage(this.file)
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
  isPDFFile(file: string): boolean {
    return file && file.endsWith('.pdf');
  }
  openFile(fileUrl: string): void {
    if (fileUrl) {
      if (fileUrl.endsWith('.png') || fileUrl.endsWith('.jpg') || fileUrl.endsWith('.jpeg') || fileUrl.endsWith('.gif')) {


      } else {

        window.open(fileUrl, '_blank');

      }
    }
  }

  ngOnDestroy(): void {
    this._unsubscribeAll.next(null);
    this._unsubscribeAll.complete();
  }
  checkIfImage(file: string): void {
    this.isImage = this.isImageFile(file);
  }
  isImageFile(file: string): boolean {
    if (file && file !== null) {
      const isImage = file.endsWith('.png') || file.endsWith('.jpg') || file.endsWith('.jpeg') || file.endsWith('.gif');
      console.log(`File: ${file}, Is Image: ${isImage}`);
      return isImage;
    }
    return false;
  }

  removeFile() {
    this.file = '';
  }

  getSafeUrl(url: string): SafeResourceUrl {
    return this.sanitizer.bypassSecurityTrustResourceUrl(url);
  }

}

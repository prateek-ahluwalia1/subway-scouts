import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { ServiceService } from './service.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { COMMA, ENTER } from "@angular/cdk/keycodes";
import { MatChipInputEvent } from '@angular/material/chips';
import { FormBuilder } from '@angular/forms';
import { ToastServiceService } from 'app/services/toast-service.service';
import { MsalService } from '@azure/msal-angular';
import { EmailSignatureService } from 'app/services/email-signature.service';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { forkJoin, map } from 'rxjs';

export interface Cc {
  email: string;
}
export interface Bcc {
  email: string;
}
export interface To {
  email: string;
}
@Component({
  selector: 'app-outlook',
  templateUrl: './outlook.component.html',
  styleUrls: ['./outlook.component.scss']
})
export class OutlookComponent implements OnInit, OnDestroy {
  includeSignature: boolean = false; // This will be bound to the checkbox

  selectedTab: string = 'inbox';
  title
  user: any;
  profilePic: any = 'assets/images/outlook-profile.png';
  listValue = [
    { label: 'Inbox', value: 'inbox', count: 0 },
    { label: 'Sent Email', value: 'sentItems', count: 0 },
    { label: 'Drafts', value: 'drafts', count: 0 },
    { label: 'Trash', value: 'deleteditems', count: 0 }
  ];

  emails: any[] = []; // Array to hold email data
  currentPage: number = 1;
  itemsPerPage: number = 20; // Change this as needed
  totalEmails: number = 0;
  isDetailShow: boolean = false
  selectedEmailDetails: any = {};
  isAuthenticated: boolean = false
  sendMeail: NgbModalRef;
  attachments: any[] = [];
  composeType: string = ''
  specificEmailId
  hoveredEmailId: string | null = null;

  // email send variable start here
  public addEmail;
  progress: number;
  @Input() data: any;
  from: string = ''; // Separate variable for 'from' field
  subject: string = ''; // Separate variable for 'subject' field
  message: string = '';

  selectable = true;
  removable = true;
  addOnBlur = true;
  to: To[] = [];
  bcc: Bcc[] = [];
  cc: Cc[] = [];
  visible = true;
  readonly separatorKeysCodes: number[] = [ENTER, COMMA];
  searchQuery: any;

  add(event: MatChipInputEvent, type): void {
    const input = event.input;
    const value = event.value;
    if ((value || '').trim()) {
      if (type == 'cc') {
        this.cc.push({ email: value.trim() });
      }
      else if (type == 'bcc') {
        this.bcc.push({ email: value.trim() });
      }
      else if (type == 'to') {
        this.to.push({ email: value.trim() });
      }
    }
    if (input) {
      input.value = '';
    }
  }

  remove(val: Bcc, type): void {
    if (type == 'cc') {
      const index = this.cc.indexOf(val);
      if (index >= 0) {
        this.cc.splice(index, 1);
      }
    }
    if (type == 'bcc') {
      const index = this.bcc.indexOf(val);
      if (index >= 0) {
        this.bcc.splice(index, 1);
      }
    }
    if (type == 'to') {
      const index = this.to.indexOf(val);
      if (index >= 0) {
        this.to.splice(index, 1);
      }
    }

  }
  // email send variable here here

  emailSignaturs: any[] = []
  showTemplate: boolean = false

  interval: any;
  constructor(private service: ServiceService, private spinner: NgxSpinnerService,
    private modalService: NgbModal,
    private fb: FormBuilder, private toast: ToastServiceService, private authService: MsalService,
    private sanitizer: DomSanitizer,
    private emailSignature: EmailSignatureService,
  ) { }


  ngOnInit(): void {
    this.title = 'Inbox';
    this.isAuthenticated = this.authService.instance.getAllAccounts().length > 0;

    if (this.isAuthenticated) {
      this.service.fetchFolders().subscribe(res => {
        console.log(res);

      })
      // const folderDetailsRequests = this.listValue.map(folder =>
      //   this.service.fetchFolderDetails(folder.value).pipe(
      //     map(details => ({ folder, details }))
      //   )
      // );

      // forkJoin(folderDetailsRequests).subscribe(results => {
      //   results.forEach(({ folder, details }) => {
      //     const folderToUpdate = this.listValue.find(f => f.value === folder.value);
      //     if (folderToUpdate) {
      //       folderToUpdate.count = details.totalItemCount;
      //       folderToUpdate.label = details.displayName;
      //     }
      //   });
      // });

      this.getProfile();
      this.fetchEmails();
      this.setRefreshInterval();
    }

    this.setupForm();
    this.getAllSignature();
  }

  setRefreshInterval() {
    this.interval = setInterval(() => {
      this.fetchEmails();
    }, 1000000); // Consider adjusting the interval based on actual needs
  }

  setupForm() {
    this.addEmail = this.fb.group({
      body: [],
      subject: []
    });
  }


  login() {
    const loginRequest = {
      scopes: ['User.Read', 'Mail.ReadBasic', 'Mail.Read', 'Mail.Send', 'Mail.ReadWrite.Shared', 'Mail.ReadWrite', 'MailboxSettings.ReadWrite'],
    };

    this.authService
      .loginPopup(loginRequest)
      .subscribe((response) => {
        localStorage.setItem('outlookToken', response.accessToken)
        console.log('Login successful', response);
        this.isAuthenticated = true
        this.getProfile();
        this.fetchEmails();
        this.listValue.forEach(folder => {
          this.service.fetchFolderDetails(folder.value).subscribe(details => {
            const folderToUpdate = this.listValue.find(f => f.value === folder.value);
            if (folderToUpdate) {
              folderToUpdate.count = details.totalItemCount;
              folderToUpdate.label = details.displayName;
            }
          });
        });
      }, (error) => {
        console.error('Login failed', error);
      });
  }

  logout() {
    this.authService.logout();
    this.isAuthenticated = false
  }

  // get Login User Profile Data
  getProfile() {
    this.service.getUserProfile().subscribe(data => {
      this.user = data;
    });
  }


  getIconClass(value: string): string {
    switch (value) {
      case 'inbox': return 'fa fa-inbox';
      case 'sentItems': return 'fa fa-envelope';
      case 'important': return 'fa fa-star';
      case 'drafts': return 'fa fa-file';
      case 'deleteditems': return 'fa fa-trash';
      default: return '';
    }
  }

  onTabClick(tabValue: any) {
    this.isDetailShow = false;
    this.selectedEmailDetails = ''
    this.title = tabValue.label
    this.selectedTab = tabValue.value;
    this.currentPage = 1; // Reset current page when changing tabs
    this.fetchEmails();
  }

  fetchEmails() {
    this.spinner.show()
    this.service.fetchEmails(this.selectedTab, this.currentPage, this.itemsPerPage).subscribe(
      res => {
        this.emails = res.value;
        this.spinner.hide()
        this.service.fetchFolderDetails(this.selectedTab).subscribe(details => {
          this.totalEmails = details.totalItemCount;
        });
      },
      error => {
        this.spinner.hide()
        console.error(error);
        // Handle errors here
      }
    );
  }

  searchEmails(query: string): void {
    if (!query.trim()) {
      return;
    }

    this.spinner.show();
    this.service.searchEmails(query).subscribe(
      res => {
        this.totalEmails = res.value.length;
        this.emails = res.value;
        this.spinner.hide();
      },
      error => {
        this.spinner.hide();
        console.error(error);
      }
    );
  }


  goToPage(page: number) {
    if (page < 1) {
      return;
    }
    const totalPages = Math.ceil(this.totalEmails / this.itemsPerPage);
    if (page > totalPages) {
      return;
    }
    this.currentPage = page;
    this.fetchEmails();
  }


  getMinValue(value1: number, value2: number): number {
    return Math.min(value1, value2);
  }

  // refresh emais
  refresh() {
    this.fetchEmails()
  }

  getEmailDetails(emailId: string): void {
    if (emailId) {
      this.spinner.show();
      this.isDetailShow = true;
      this.service.fetchEmailById(emailId).subscribe((emailDetails) => {
        this.selectedEmailDetails = emailDetails; // Store email details

        // Fetch and store email attachments
        this.service.fetchEmailAttachments(emailId).subscribe((attachments) => {
          this.selectedEmailDetails.attachments = attachments;
          console.log(this.selectedEmailDetails);


          this.spinner.hide();

          if (!emailDetails.isRead) {
            // Update the read status to true
            this.service.updateEmailProperties(emailId, { isRead: true }).subscribe(
              () => {
                console.log('Email read status updated.');
              },
              (error) => {
                console.error('Error updating email read status:', error);
              }
            );
          }
        });
      });
    }
  }


  goBack(): void {
    this.isDetailShow = false;
    this.selectedEmailDetails = ''
    this.fetchEmails()
  }


  sendEmailModel(content) {
    const modalRef = this.modalService.open(content, {
      windowClass: "modal-right-email-lead",
      size: 'lg', // Set the size to large
      centered: true, // Center the modal vertically
      backdropClass: 'modal-no-backdrop',
    });

    modalRef.result.then(
      (result) => {
        console.log('Modal closed with result:', result);
        this.reset()
      },
      (reason) => {
        console.log('Modal dismissed with reason:', reason);
        if (reason == 'Cross click') {
          this.reset()
        }
      }
    );
  }

  // send email
  sendEmail() {
    const attachments: File[] = this.attachments;
    let emailBody = this.addEmail.value.body;

    // Append the dynamically constructed signature if the checkbox is checked
    if (this.includeSignature) {
      emailBody += this.service.getSignature();
    }

    const email = {
      to: this.to.map(item => item.email),
      cc: this.cc.map(item => item.email),
      bcc: this.bcc.map(item => item.email),
      subject: this.addEmail.value.subject,
      body: emailBody
    };

    this.service.sendEmail(email, attachments).subscribe(
      () => {
        // Handle success
        this.toast.toastNotification('Email sent successfully', 'Email Sent!')
        this.modalService.dismissAll()
        this.attachments = []
      },
      (error) => {
        console.log(error);
        this.toast.toastNotification(error?.error?.error?.message, 'Email Sent!')
      }
    );
  }

  // Method to add attachments when the file input changes
  addAttachments(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files) {
      for (let i = 0; i < input.files.length; i++) {
        const file = input.files[i];

        // Read the file content and convert it to base64
        const reader = new FileReader();
        reader.onload = () => {
          const content = reader.result.toString().split(',')[1]; // Get base64 content
          const attachment = {
            name: file.name,
            content: content,
          };
          this.attachments.push(attachment);

          // Clear the file input (optional)
          input.value = '';
        };
        reader.readAsDataURL(file);
      }
    }
  }


  // Method to remove an attachment by index
  removeAttachment(index: number): void {
    if (index >= 0 && index < this.attachments.length) {
      this.attachments.splice(index, 1);
    }
  }

  openFileInput(): void {
    const fileInput = document.getElementById('fileInput') as HTMLInputElement;
    fileInput.click();
  }



  // Method to open the email composition modal for replying to an email
  replyForwardToEmail(email: any, content, type) {
    this.specificEmailId = email
    this.composeType = type

    if (type == 'reply' && !email.isDraft) {
      this.addEmail.patchValue({
        subject: `${email.subject}`,
        body: `<br><blockquote>${email.body.content}</blockquote>`
      });
      this.to.push({ email: email.sender.emailAddress.address.trim() });
    }
    else if (type == 'draft') {
      this.addEmail.patchValue({
        subject: email.subject, // Set the subject with "Fwd:"
        body: email.body.content // Set the body with the email content
      });
      this.to.push({ email: email.toRecipients.map(recipient => recipient.emailAddress.address) });
      console.log(this.to);


    }
    else {
      this.addEmail.patchValue({
        subject: `Fwd: ${email.subject}`, // Set the subject with "Fwd:"
        body: `<br><blockquote>${email.body.content}</blockquote>` // Set the body with the email content
      });
    }
    this.openEmailCompositionModal(content);
  }

  openEmailCompositionModal(content) {
    const modalRef: NgbModalRef = this.modalService.open(content, {
      windowClass: 'modal-right-email-lead',
      size: 'lg',
      centered: true,
      backdropClass: 'modal-no-backdrop',
    });

    modalRef.result.then(
      (result) => {
        console.log('Modal closed with result:', result);
        this.reset()
      },
      (reason) => {
        console.log('Modal dismissed with reason:', reason);
        if (reason == 'Cross click') {
          this.reset()
        }
      }
    );
  }

  replyForwardEmail(type) {
    const attachments: File[] = this.attachments;
    const email = {
      to: this.to.map(item => item.email),
      cc: this.cc.map(item => item.email),
      bcc: this.bcc.map(item => item.email),
      subject: this.addEmail.value.subject,
      body: this.addEmail.value.body
    };

    this.service.replyForwardEmail(this.specificEmailId, email, attachments, type).subscribe(
      () => {
        // Handle success
        this.toast.toastNotification('Email sent successfully', 'Email Sent!')
        this.modalService.dismissAll()
        this.reset()
      },
      () => {
        // Handle error
        this.toast.toastNotification('Something went wrong', 'Email Sent!')
      }
    );
  }

  // forwardEmail() {
  //   const attachments: File[] = this.attachments;
  //   const email = {
  //     to: this.to.map(item => item.email),
  //     cc: this.cc.map(item => item.email),
  //     bcc: this.bcc.map(item => item.email),
  //     subject: this.addEmail.value.subject,
  //     body: this.addEmail.value.body
  //   };

  //   this.service.sendEmail(email,attachments).subscribe(
  //     response => {
  //       // Handle success
  //       this.toast.toastNotification('Email sent successfully', 'Email Sent!')
  //       this.modalService.dismissAll()
  //       this.addEmail.reset()
  //       this.to = []
  //       this.cc = []
  //       this.bcc = []
  //     },
  //     error => {
  //       // Handle error
  //       this.toast.toastNotification('Something went wrong', 'Email Sent!')
  //     }
  //   );
  // }


  deleteEmail(id) {
    this.service.deleteEmail(id).subscribe(
      () => {
        this.fetchEmails()
        this.toast.toastNotification('Email delete successfully', 'Email Delete!')
      },
      (error) => {
        // Handle deletion error
        console.error('Error deleting email:', error);
        this.toast.toastNotification('Something went wrong', 'Email Delete!')
      }
    );

  }

  getAllSignature() {
    let admin = JSON.parse(localStorage.getItem('admin'))
    this.emailSignature.getSignature(admin.admin_id).subscribe(res => {
      this.emailSignaturs = res.data;
    })
  }

  importTemplate() {
    this.showTemplate = !this.showTemplate
  }

  onTemplateSelectionChange(event: any) {
    const id = event.value;
    const email = this.emailSignaturs.find((obj) => obj.id === id)
    if (email) {
      this.addEmail.get('body').setValue(email.body);
      this.addEmail.patchValue({
        body: email.body // Set the body with the email content

      });
    }
  }

  reset() {
    this.to = []
    this.cc = []
    this.bcc = []
    this.showTemplate = false
    this.composeType = null,
      this.attachments = []
    this.addEmail.reset()
    this.includeSignature = false
  }


  viewAttachment(attachment: any): void {
    const attachmentUrl = attachment.downloadUrl; // Replace with the actual attachment URL
    window.open(attachmentUrl, '_blank');
  }


  getAttachmentUrl(attachment) {
    const base64Data = attachment.contentBytes;
    const binaryData = atob(base64Data); // Decode Base64 to binary

    // Convert the binary data to a Uint8Array
    const uint8Array = new Uint8Array(binaryData.length);
    for (let i = 0; i < binaryData.length; i++) {
      uint8Array[i] = binaryData.charCodeAt(i);
    }

    // Create a Blob from the Uint8Array
    const blob = new Blob([uint8Array], { type: attachment.contentType });

    // Create a URL for the Blob and trigger the download
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = attachment.name; // Set the filename to the attachment's name
    a.click();
  }


  sanitizeEmailBody(emailBody: string, attachments: any[]): SafeHtml {
    if (!emailBody || !attachments) {
      return '';
    }

    const cidRegex = /<img.*?src=["']cid:(.*?)["'].*?>/g;
    let sanitizedEmailBody = emailBody;

    // Replace CID references with data URLs
    sanitizedEmailBody = sanitizedEmailBody.replace(cidRegex, (match, cid) => {
      const attachment = attachments.find((att) => att.contentId === cid);

      if (attachment) {
        const dataURL = `data:${attachment.contentType};base64,${attachment.contentBytes}`;
        return `<img src="${dataURL}" />`;
      }

      return match;
    });

    return this.sanitizer.bypassSecurityTrustHtml(sanitizedEmailBody);
  }

  // Call this method to render the email body
  renderEmailBody(emailBody: string): SafeHtml {
    return this.sanitizeEmailBody(emailBody, this.selectedEmailDetails?.attachments);
  }


  ngOnDestroy() {
    if (this.interval) {
      clearInterval(this.interval);
    }
  }


  getAttachmentIcon(contentType: string): string {
    if (contentType.startsWith('image/')) {
      return '../../../../assets/images/avatars/jpg-file.png';
    } else if (contentType === 'application/pdf') {
      return '../../../../assets/images/avatars/pdf-file.png';
    } else if (contentType.includes('spreadsheetml') || contentType.includes('excel')) {
      return '../../../../assets/images/avatars/xls-file.png';
    } else if (contentType.includes('wordprocessingml') || contentType.includes('word')) {
      return 'description';
    } else if (contentType.includes('presentationml') || contentType.includes('powerpoint')) {
      return '../../../../assets/images/powerpoint.png';
    }
    return '../../../../assets/images/avatars/docs-file.png';
  }


}

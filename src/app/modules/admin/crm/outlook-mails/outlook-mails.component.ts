import { Component, Input, OnInit, TemplateRef, ViewChild, ViewContainerRef } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MsalService } from '@azure/msal-angular';
import { MailboxService } from '../../mailbox/mailbox.service';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { Overlay, OverlayRef } from '@angular/cdk/overlay';
import { MailboxComposeComponent } from '../../mailbox/compose/compose.component';
import { TemplatePortal } from '@angular/cdk/portal';
import { MatButton } from '@angular/material/button';

@Component({
  selector: 'app-outlook-mails',
  templateUrl: './outlook-mails.component.html'
})
export class OutlookMailsComponent implements OnInit {

  @Input() isAuthenticated: boolean;
  @Input() user: any;
  @Input() outlookEmails: any[] = []
  private _overlayRef: OverlayRef;

  @ViewChild('infoDetailsPanelOrigin') private _infoDetailsPanelOrigin: MatButton;
  @ViewChild('infoDetailsPanel') private _infoDetailsPanel: TemplateRef<any>;
  constructor(private _matDialog: MatDialog, private _msalService: MsalService, private _mailboxService: MailboxService,
    private _viewContainerRef: ViewContainerRef,
    private _sanitizer: DomSanitizer, private _overlay: Overlay,) { }

  ngOnInit(): void {

  }

  openModal() {
    if (this.isAuthenticated) {
      const dialogRef = this._matDialog.open(MailboxComposeComponent, {
        data: { email: this.user?.email, readonly: true }
      });
      dialogRef.afterClosed().subscribe((result) => {
        this.getEmailsByRecipient()
        console.log('Compose dialog was closed!', result);
      });
    }
    else {
      this.login()
    }
  }

  login() {
    const loginRequest = {
      scopes: ['User.Read', 'Mail.ReadBasic', 'Mail.Read', 'Mail.Send', 'Mail.ReadWrite.Shared', 'Mail.ReadWrite', 'MailboxSettings.ReadWrite'],
    };

    this._msalService
      .loginPopup(loginRequest)
      .subscribe((response) => {
        console.log(response);
        this.isAuthenticated = true;
        this.getEmailsByRecipient();
      }, (error) => {
        alert('Login failed');
      });

    this.isAuthenticated = this._msalService.instance.getAllAccounts().length > 0;

  }

  getEmailsByRecipient() {
    if (this.user) {
      this._mailboxService.getMailsByRecipient().subscribe(
        (emails) => {
          this.outlookEmails = emails.filter((email) => {
            const fromCheck = email.from?.emailAddress?.address === this.user.email;
            const toCheck = email.toRecipients?.some(
              (recipient) => recipient?.emailAddress?.address === this.user.email
            );
            return fromCheck || toCheck;
          });
          this.outlookEmails.forEach(element => {
            if (element.hasAttachments) {
              this._mailboxService.fetchEmailAttachments(element.id).subscribe((attachments) => {
                element.attachments = attachments;
              });
            }
          });
          console.log('Emails related to the specified address:', this.outlookEmails);
        },
        (error) => {
          console.error('Failed to fetch emails', error);
        }
      );
    }
  }


  renderEmailBody(emailBody: string, attachments: any[]): SafeHtml {
    return this.sanitizeEmailBody(emailBody, attachments);

  }

  sanitizeEmailBody(emailBody: string, attachments: any[]): SafeHtml {
    const cidRegex = /<img.*?src=["']cid:(.*?)["'].*?>/g;
    let sanitizedEmailBody = emailBody;

    sanitizedEmailBody = sanitizedEmailBody?.replace(cidRegex, (match, cid) => {
      const attachment = attachments?.find((att) => att.contentId === cid);

      if (attachment) {
        const dataURL = `data:${attachment.contentType};base64,${attachment.contentBytes}`;
        return `<img src="${dataURL}" />`;
      }

      return match;
    });

    return this._sanitizer.bypassSecurityTrustHtml(sanitizedEmailBody);
  }

  trackByEmailId(index: number, email: any): string {
    return email.id;
  }

  getAttachmentIcon(contentType: string): string {
    if (contentType.startsWith('image/')) {
      return '../../../../../assets/images/avatars/jpg-file.png';
    } else if (contentType === 'application/pdf') {
      return '../../../../../assets/images/avatars/pdf-file.png';
    } else if (contentType.includes('spreadsheetml') || contentType.includes('excel')) {
      return '../../../../../assets/images/avatars/xls-file.png';
    } else if (contentType.includes('wordprocessingml') || contentType.includes('word')) {
      return 'description';
    } else if (contentType.includes('presentationml') || contentType.includes('powerpoint')) {
      return '../../../../../assets/images/powerpoint.png';
    }
    return '../../../../../assets/images/avatars/docs-file.png';
  }

  openInfoDetailsPanel(): void {
    this._overlayRef = this._overlay.create({
      backdropClass: '',
      hasBackdrop: true,
      scrollStrategy: this._overlay.scrollStrategies.block(),
      positionStrategy: this._overlay.position()
        .flexibleConnectedTo(this._infoDetailsPanelOrigin._elementRef.nativeElement)
        .withFlexibleDimensions(true)
        .withViewportMargin(16)
        .withLockedPosition(true)
        .withPositions([
          {
            originX: 'start',
            originY: 'bottom',
            overlayX: 'start',
            overlayY: 'top'
          },
          {
            originX: 'start',
            originY: 'top',
            overlayX: 'start',
            overlayY: 'bottom'
          },
          {
            originX: 'end',
            originY: 'bottom',
            overlayX: 'end',
            overlayY: 'top'
          },
          {
            originX: 'end',
            originY: 'top',
            overlayX: 'end',
            overlayY: 'bottom'
          }
        ])
    });

    const templatePortal = new TemplatePortal(this._infoDetailsPanel, this._viewContainerRef);

    this._overlayRef.attach(templatePortal);

    this._overlayRef.backdropClick().subscribe(() => {
      if (this._overlayRef && this._overlayRef.hasAttached()) {
        this._overlayRef.detach();
      }

      if (templatePortal && templatePortal.isAttached) {
        templatePortal.detach();
      }
    });
  }

  getAttachmentUrl(attachment): void {
    const base64Data = attachment.contentBytes;
    const binaryData = atob(base64Data);
    const uint8Array = new Uint8Array(binaryData.length);
    for (let i = 0; i < binaryData.length; i++) {
      uint8Array[i] = binaryData.charCodeAt(i);
    }

    const blob = new Blob([uint8Array], { type: attachment.contentType });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = attachment.name;
    a.click();
  }
}

import { ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, OnDestroy, OnInit, TemplateRef, ViewChild, ViewContainerRef, ViewEncapsulation } from '@angular/core';
import { TemplatePortal } from '@angular/cdk/portal';
import { Overlay, OverlayRef } from '@angular/cdk/overlay';
import { MatButton } from '@angular/material/button';
import { Subject, Subscription, from, of } from 'rxjs';
import { concatMap, map, switchMap, takeUntil, toArray } from 'rxjs/operators';
import { MailboxService } from 'app/modules/admin/mailbox/mailbox.service';
import { Mail, MailFolder, MailLabel } from 'app/modules/admin/mailbox/mailbox.types';
import { labelColorDefs } from 'app/modules/admin/mailbox/mailbox.constants';
import { FormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { ToastServiceService } from 'app/services/toast-service.service';
import { ActivatedRoute, Router } from '@angular/router';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { MatChipInputEvent } from '@angular/material/chips';

@Component({
    selector: 'mailbox-details',
    templateUrl: './details.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class MailboxDetailsComponent implements OnInit, OnDestroy {
    @ViewChild('infoDetailsPanelOrigin') private _infoDetailsPanelOrigin: MatButton;
    @ViewChild('infoDetailsPanel') private _infoDetailsPanel: TemplateRef<any>;
    composeForm: UntypedFormGroup;

    folders: MailFolder[];
    labelColors: any;
    labels: MailLabel[];
    mail: Mail;
    replyFormActive: boolean = false;
    private _overlayRef: OverlayRef;
    private _unsubscribeAll: Subject<any> = new Subject<any>();
    forwardToRecipients: string = '';
    replyType: string = '';
    attachments: any[] = [];
    conversationEmails: Mail[] = [];

    toEmails: string[] = [];
    ccEmails: string[] = [];
    bccEmails: string[] = [];
    toEmailCtrl = this._formBuilder.control('', [Validators.email]);
    ccEmailCtrl = this._formBuilder.control('', [Validators.email]);
    bccEmailCtrl = this._formBuilder.control('', [Validators.email]);
    selectable = true;
    removable = true;
    separatorKeysCodes: number[] = [ENTER, COMMA];
    copyFields = { cc: false, bcc: false };

    private folderSubscription: Subscription;
    currentUrl: string;
    constructor(
        private _activatedRoute: ActivatedRoute,
        private _elementRef: ElementRef,
        private _mailboxService: MailboxService,
        private _overlay: Overlay,
        private _router: Router,
        private _viewContainerRef: ViewContainerRef,
        private _formBuilder: FormBuilder,
        private _toastService: ToastServiceService,
        private _sanitizer: DomSanitizer,
        private _cdr: ChangeDetectorRef,
        private router: Router, private activatedRoute: ActivatedRoute
    ) { }

    ngOnInit(): void {
        this.composeForm = this._formBuilder.group({
            to: this.toEmailCtrl,
            cc: this.ccEmailCtrl,
            bcc: this.bccEmailCtrl,
            subject: [''],
            body: ['']
        });
        this.labelColors = labelColorDefs;

        this._mailboxService.folders$
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe((folders: MailFolder[]) => {
                this.folders = folders;
                this._cdr.markForCheck();
            });

        this._mailboxService.mail$
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe((mail: Mail) => {
                this.mail = mail;
                if (mail) {
                    this._mailboxService.fetchEmailAttachments(mail.id).subscribe((attachments) => {
                        this.mail.attachments = attachments;
                        this._cdr.markForCheck();
                    });

                    if (mail.isDraft) {
                        if (mail.ccRecipients) {
                            this.showCopyField('cc');
                        }
                        if (mail.bccRecipients) {
                            this.showCopyField('bcc');
                        }
                        this.toEmails.push(...mail.toRecipients.map(recipient => recipient.emailAddress.address));
                        this.ccEmails.push(...mail.ccRecipients.map(recipient => recipient.emailAddress.address));
                        this.bccEmails.push(...mail.bccRecipients.map(recipient => recipient.emailAddress.address));
                        this.composeForm.patchValue({
                            body: mail.body.content,
                            subject: mail.subject
                        });
                    }

                    if (mail.conversationId) {
                        this.conversationEmails = [];
                        this.loadConversation(mail.conversationId);
                    }
                    this._cdr.markForCheck();
                }
            });

        this._mailboxService.selectedMailChanged
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe(() => {
                this.replyFormActive = this.mail?.isDraft;
                this._cdr.markForCheck();
            });

        this.toggleUnread(true)

    }

    loadConversation(conversationId: string): void {
        this._mailboxService.getConversationEmails(conversationId).pipe(
            switchMap(emails => {
                this.conversationEmails = emails;
                return from(emails).pipe(
                    concatMap((mail: any) => {
                        if (mail.hasAttachments) {
                            return this._mailboxService.fetchEmailAttachments(mail.id).pipe(
                                map(attachments => {
                                    mail.attachments = attachments;
                                    return mail;
                                })
                            );
                        } else {
                            return of(mail);
                        }
                    }),
                    toArray()
                );
            })
        ).subscribe(emailsWithAttachments => {
            this.conversationEmails = emailsWithAttachments;
            this._cdr.markForCheck();
        });
    }

    addToEmail(event: MatChipInputEvent): void {
        const input = event.input;
        const value = event.value;

        if ((value || '').trim()) {
            this.toEmails.push(value.trim());
        }

        if (input) {
            input.value = '';
        }

        this.toEmailCtrl.setValue(null);
        this._cdr.markForCheck();
    }

    removeToEmail(email: string): void {
        const index = this.toEmails.indexOf(email);

        if (index >= 0) {
            this.toEmails.splice(index, 1);
        }
        this._cdr.markForCheck();
    }

    addCcEmail(event: MatChipInputEvent): void {
        const input = event.input;
        const value = event.value;

        if ((value || '').trim()) {
            this.ccEmails.push(value.trim());
        }

        if (input) {
            input.value = '';
        }

        this.ccEmailCtrl.setValue(null);
        this._cdr.markForCheck();
    }

    removeCcEmail(email: string): void {
        const index = this.ccEmails.indexOf(email);

        if (index >= 0) {
            this.ccEmails.splice(index, 1);
        }
        this._cdr.markForCheck();
    }

    addBccEmail(event: MatChipInputEvent): void {
        const input = event.input;
        const value = event.value;

        if ((value || '').trim()) {
            this.bccEmails.push(value.trim());
        }

        if (input) {
            input.value = '';
        }

        this.bccEmailCtrl.setValue(null);
        this._cdr.markForCheck();
    }

    removeBccEmail(email: string): void {
        const index = this.bccEmails.indexOf(email);

        if (index >= 0) {
            this.bccEmails.splice(index, 1);
        }
        this._cdr.markForCheck();
    }

    showCopyField(field: 'cc' | 'bcc'): void {
        this.copyFields[field] = true;
        this._cdr.markForCheck();
    }

    getCurrentFolder(): string {
        return this._mailboxService.getCurrentFolder();
    }

    ngOnDestroy(): void {
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
    }

    moveToTrash(): void {
        this._mailboxService.moveMailToTrash(this.mail.id).subscribe((response) => {
            if (response.success) {
                this._router.navigate(['./'], { relativeTo: this._activatedRoute.parent });
                this._toastService.toastNotification(response.message, 'Mail Operation!');
                this._cdr.markForCheck();
            }
        }, error => {
            alert("An error occurred while deleting the email. Please try again later.");
            console.error('Failed to delete email', error);
        });
    }

    moveToArchive(): void {
        this._mailboxService.moveMailToArchive(this.mail.id).subscribe((response) => {
            if (response.success) {
                this._router.navigate(['./'], { relativeTo: this._activatedRoute.parent });
                this._toastService.toastNotification(response.message, 'Mail Operation!');
                this._cdr.markForCheck();
            }
        }, error => {
            alert("An error occurred while archiving the email. Please try again later.");
            console.error('Failed to archive email', error);
        });
    }

    unarchiveMail(): void {
        this._mailboxService.unarchiveMail(this.mail.id).subscribe((response) => {
            if (response.success) {
                this._router.navigate(['./'], { relativeTo: this._activatedRoute.parent });
                this._toastService.toastNotification(response.message, 'Mail Operation!');
                this._cdr.markForCheck();
            }
        }, error => {
            alert("An error occurred while unarchiving the email. Please try again later.");
            console.error('Failed to unarchive email', error);
        });
    }

    toggleUnread(status: boolean): void {
        this._mailboxService.updateMail(this.mail.id, { isRead: status }).subscribe((response) => {
            if (response.success) {
                this.mail.isRead = status;
                this._cdr.markForCheck();
            }
        }, error => {
            alert("An error occurred while updating the email. Please try again later.");
            console.error('Failed to update email', error);
        });
    }

    reply(type: string): void {
        this.replyType = type;
        this.replyFormActive = true;
        setTimeout(() => {
            this._elementRef.nativeElement.scrollTop = this._elementRef.nativeElement.scrollHeight;
        });
        this._cdr.markForCheck();
    }

    forward(type: string): void {
        this.replyType = type;
        this.replyFormActive = true;
        setTimeout(() => {
            this._elementRef.nativeElement.scrollTop = this._elementRef.nativeElement.scrollHeight;
        });
        this._cdr.markForCheck();
    }

    discard(): void {
        this.replyFormActive = false;
        this._cdr.markForCheck();
    }

    send(): void {
        if (this.composeForm.invalid) {
            return;
        }

        const bodyContent = this.composeForm.get('body').value;

        const email = {
            body: bodyContent,
            attachments: this.attachments
        };

        if (this.mail.isDraft) {
            this.sendDraftMail();
        } else if (this.replyType === 'forward') {
            this.forwardToMail();
        } else {
            let lastItem = this.conversationEmails.pop();
            let apiMethod = 'replyToMail';

            this._mailboxService[apiMethod](lastItem.id ?? this.mail.id, email, this.replyType).subscribe(response => {
                if (response.success) {
                    this._toastService.toastNotification('Mail sent successfully', 'Mail Operation!');
                    this.composeForm.reset();
                    this.discard();
                    this.loadConversation(this.mail.conversationId);
                    this._cdr.markForCheck();
                }
            }, error => {
                alert("An error occurred while sending the email. Please try again later.");
                console.error('Failed to send email', error);
            });
        }
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
            this._cdr.markForCheck();
        });
    }

    trackByFn(index: number, item: any): any {
        return item.id || index;
    }

    getAttachmentIcon(contentType: string): string {
        if (!contentType) {
            return '../../../../assets/images/avatars/docs-file.png';
        }
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
        this._cdr.markForCheck();
    }

    onFileSelected(event: Event): void {
        const input = event.target as HTMLInputElement;
        if (input.files) {
            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];

                const reader = new FileReader();
                reader.onload = () => {
                    const content = reader.result.toString().split(',')[1]; // Get base64 content
                    const attachment = {
                        name: file.name,
                        content: content,
                        progress: 100
                    };
                    this.attachments.push(attachment);
                    input.value = '';
                    this._cdr.markForCheck();
                };
                reader.readAsDataURL(file);
            }
        }
    }

    removeAttachment(index: number): void {
        if (index >= 0 && index < this.attachments.length) {
            this.attachments.splice(index, 1);
            this._cdr.markForCheck();
        }
    }

    trackByEmailId(index: number, email: any): string {
        return email.id;
    }

    toggleImportant(): void {
        const newImportance = this.mail.importance === 'high' ? 'normal' : 'high';
        this._mailboxService.updateImportance(this.mail.id, newImportance).subscribe(response => {
            if (response.success) {
                this._toastService.toastNotification(response.message, 'Importance!');
                this.mail.importance = newImportance;
                this._cdr.markForCheck();
            }
        }, error => {
            alert("An error occurred while updating the email importance. Please try again later.");
            console.error('Failed to update email importance', error);
        });
    }

    toggleStar(): void {
        const newStarred = !this.mail.categories.includes('Starred');
        this._mailboxService.toggleStar(this.mail.id, newStarred).subscribe(response => {
            if (response.success) {
                this._toastService.toastNotification(response.message, 'Starred!');
                if (newStarred) {
                    this.mail.categories.push('Starred');
                } else {
                    const index = this.mail.categories.indexOf('Starred');
                    if (index > -1) {
                        this.mail.categories.splice(index, 1);
                    }
                }
                this._cdr.markForCheck();
            }
        }, error => {
            alert("An error occurred while updating the email star status. Please try again later.");
            console.error('Failed to update email star status', error);
        });
    }

    toggleFlag(): void {
        const newFlagStatus = this.mail.flag.flagStatus === 'flagged' ? 'complete' : 'flagged';
        this._mailboxService.updateFlag(this.mail.id, newFlagStatus).subscribe(response => {
            if (response.success) {
                this._toastService.toastNotification(response.message, 'Flag!');
                this.mail.flag.flagStatus = newFlagStatus;
                this._cdr.markForCheck();
            }
        }, error => {
            alert("An error occurred while updating the email flag status. Please try again later.");
            console.error('Failed to update email flag status', error);
        });
    }

    restoreMail(): void {
        this._mailboxService.restoreDeletedMail(this.mail.id).subscribe(response => {
            if (response.success) {
                this._router.navigate(['./'], { relativeTo: this._activatedRoute.parent });
                this._toastService.toastNotification(response.message, 'Mail Operation!');
                this._cdr.markForCheck();
            } else {
                console.error(response.message);
            }
        }, error => {
            console.error('Error restoring email:', error);
            alert("An error occurred while restoring email. Please try again later.");
        });
    }

    permanentlyDeleteMail(): void {
        this._mailboxService.permanentlyDeleteMail(this.mail.id).subscribe(response => {
            if (response.success) {
                this._router.navigate(['./'], { relativeTo: this._activatedRoute.parent });
                this._toastService.toastNotification(response.message, 'Mail Operation!');
                this._cdr.markForCheck();
            } else {
                console.error(response.message);
            }
        }, error => {
            alert("An error occurred while permanently deleting email. Please try again later.");
            console.error('Error permanently deleting email:', error);
        });
    }

    renderEmailBody(emailBody: string, attachments: any[]): SafeHtml {
        this._cdr.markForCheck();
        return this.sanitizeEmailBody(emailBody, attachments);
    }

    sanitizeEmailBody(emailBody: string, attachments: any[]): SafeHtml {
        if (!emailBody) {
            return this._sanitizer.bypassSecurityTrustHtml('');
        }

        const cidRegex = /<img(.*?)src=["']cid:(.*?)["'](.*?)>/g;
        let sanitizedEmailBody = emailBody;

        sanitizedEmailBody = sanitizedEmailBody.replace(cidRegex, (match, preSrc, cid, postSrc) => {
            const attachment = attachments ? attachments.find(att => att.contentId === cid) : null;
            if (attachment) {
                const dataURL = `data:${attachment.contentType};base64,${attachment.contentBytes}`;
                return `<img ${preSrc} src="${dataURL}" ${postSrc}/>`;
            }
            return match;  // Return original match if not found
        });

        this._cdr.markForCheck();
        return this._sanitizer.bypassSecurityTrustHtml(sanitizedEmailBody);
    }


    // forward mail
    forwardToMail(): void {
        if (this.composeForm.invalid) {
            return;
        }

        const toRecipients = this.toEmails.map(email => ({ emailAddress: { address: email.trim() } }));
        const bodyContent = this.composeForm.get('body').value;

        const email = {
            body: bodyContent,
            to: toRecipients,
        };

        let lastItem = this.conversationEmails.pop();

        this._mailboxService.forwardToMail(lastItem.id ?? this.mail.id, email).subscribe(response => {
            if (response.success) {
                this._toastService.toastNotification(response.message, 'Mail Operation!');
                this.composeForm.reset();
                this.discard();
                this.loadConversation(this.mail.conversationId);
                this._cdr.markForCheck();
            }
        }, error => {
            alert("An error occurred while sending the email. Please try again later.");
            console.error('Failed to send email', error);
        });
    }

    // send draft mail
    sendDraftMail() {
        if (this.composeForm.invalid) {
            return;
        }

        const toRecipients = this.toEmails.map(email => ({ emailAddress: { address: email.trim() } }));
        const ccRecipients = this.ccEmails.map(email => ({ emailAddress: { address: email.trim() } }));
        const bccRecipients = this.bccEmails.map(email => ({ emailAddress: { address: email.trim() } }));
        const bodyContent = this.composeForm.get('body').value;
        const subject = this.composeForm.get('subject').value;

        if (toRecipients.length === 0) {
            alert('A message needs to have at least one recipient.');
            return;
        }

        const email = {
            subject: subject,
            body: bodyContent,
            toRecipients: toRecipients,
            ccRecipients: ccRecipients,
            bccRecipients: bccRecipients,
            attachments: this.attachments
        };

        this._mailboxService.updateDraftMail(this.mail.id, email).subscribe(response => {
            if (response.success) {
                this._mailboxService.sendDraftMail(this.mail.id).subscribe(sendResponse => {
                    if (sendResponse.success) {
                        this._toastService.toastNotification('Mail sent successfully', 'Mail Operation!');
                        this.composeForm.reset();
                        this.discard();
                        this._mailboxService.getFolders().subscribe()
                        this._cdr.markForCheck();
                    } else {
                        alert(sendResponse.message);
                    }
                }, error => {
                    alert("An error occurred while sending the email. Please try again later.");
                    console.error('Failed to send email', error);
                });
            } else {
                alert(response.message);
            }
        }, error => {
            alert("An error occurred while updating the draft. Please try again later.");
            console.error('Failed to update draft', error);
        });
    }



}

import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { Component, Inject, OnInit, ViewEncapsulation } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MatChipInputEvent } from '@angular/material/chips';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { MailboxService } from 'app/modules/admin/mailbox/mailbox.service';
import { EmailSignatureService } from 'app/services/email-signature.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
    selector: 'mailbox-compose',
    templateUrl: './compose.component.html',
})
export class MailboxComposeComponent implements OnInit {
    composeForm: UntypedFormGroup;
    copyFields: { cc: boolean; bcc: boolean } = {
        cc: false,
        bcc: false
    };
    toEmails: string[] = [];
    ccEmails: string[] = [];
    bccEmails: string[] = [];
    attachments: any[] = [];
    readonly separatorKeysCodes: number[] = [ENTER, COMMA];
    toEmailCtrl = this._formBuilder.control('', [Validators.email]);
    ccEmailCtrl = this._formBuilder.control('', [Validators.email]);
    bccEmailCtrl = this._formBuilder.control('', [Validators.email]);
    selectable = true;
    removable = true;

    emailSignatures: any;
    selectedSignature: any = null;
    constructor(
        public matDialogRef: MatDialogRef<MailboxComposeComponent>,
        private _formBuilder: UntypedFormBuilder,
        private _mailboxService: MailboxService,
        private toast: ToastServiceService,
        private _emailSignature: EmailSignatureService,
        @Inject(MAT_DIALOG_DATA) public data: any,
        private _spinner: NgxSpinnerService
    ) { }

    ngOnInit(): void {
        // Create the form
        this.composeForm = this._formBuilder.group({
            subject: [''],
            body: ['', [Validators.required]]
        });

        if (this.data) {
            this.toEmails = []
            const value = (this.data.email || '').trim();
            if (value) {
                this.toEmails.push(value);
            }
        }

        console.log(this.data);

        // this.getAllSignature();
    }

    showCopyField(name: string): void {
        if (name !== 'cc' && name !== 'bcc') {
            return;
        }

        this.copyFields[name] = true;
    }

    addToEmail(event: MatChipInputEvent): void {
        const value = (event.value || '').trim();

        if (value) {
            this.toEmails.push(value);
        }

        event.chipInput!.clear();
    }

    removeToEmail(email: string): void {
        const index = this.toEmails.indexOf(email);

        if (index >= 0) {
            this.toEmails.splice(index, 1);
        }
    }

    addCcEmail(event: MatChipInputEvent): void {
        const value = (event.value || '').trim();

        if (value) {
            this.ccEmails.push(value);
        }

        event.chipInput!.clear();
    }

    removeCcEmail(email: string): void {
        const index = this.ccEmails.indexOf(email);

        if (index >= 0) {
            this.ccEmails.splice(index, 1);
        }
    }

    addBccEmail(event: MatChipInputEvent): void {
        const value = (event.value || '').trim();

        if (value) {
            this.bccEmails.push(value);
        }

        event.chipInput!.clear();
    }

    removeBccEmail(email: string): void {
        const index = this.bccEmails.indexOf(email);

        if (index >= 0) {
            this.bccEmails.splice(index, 1);
        }
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
                };
                reader.readAsDataURL(file);
            }
        }
    }

    removeAttachment(index: number): void {
        if (index >= 0 && index < this.attachments.length) {
            this.attachments.splice(index, 1);
        }
    }

    saveAndClose(): void {
        this.matDialogRef.close();
    }

    discard(): void {
        this.matDialogRef.close();
    }

    saveAsDraft(): void {
        if (this.data && this.data.readonly) {
            return
        }
        this._spinner.show()
        const draft = this.composeForm.value;
        draft.to = this.toEmails.join(', ');
        draft.cc = this.ccEmails.join(', ');
        draft.bcc = this.bccEmails.join(', ');
        draft.attachments = this.attachments;
        if (this.selectedSignature) {
            draft.body += `\n\n${this.selectedSignature.body}`;
        }
        this._mailboxService.saveDraft(draft).subscribe(() => {
            this.toast.toastNotification('Draft saved successfully', 'Mail Operation!');
            this.matDialogRef.close();
            this._spinner.hide()

        }, error => {
            alert("An error occurred while drafting the email. Please try again later.");
            console.error('Failed to save draft', error);
            this._spinner.hide()

        });
    }

    send(): void {
        this._spinner.show()
        if (this.composeForm.invalid) {
            this._spinner.hide()
            return;
        }

        const email = this.composeForm.value;
        email.to = this.toEmails.join(', ');
        email.cc = this.ccEmails.join(', ');
        email.bcc = this.bccEmails.join(', ');
        email.attachments = this.attachments;
        if (this.selectedSignature) {
            email.body += `\n\n${this.selectedSignature.body}`;
        }
        this._mailboxService.sendMail(email).subscribe(
            response => {
                this.matDialogRef.close();
                if (response.success) {
                    this._spinner.hide()

                    this.toast.toastNotification('Mail sent successfully', 'Mail Operation!');
                    console.log(response.message);
                } else {
                    console.error(response.message);
                }
            },
            error => {
                console.error(error.message);
                if (error.code === "ErrorExceededMessageLimit") {
                    this._spinner.hide()

                    alert("Cannot send mail. Daily Message/Recipient limit exceeded. Follow the instructions in your Inbox to verify your account.");
                } else {
                    alert("An error occurred while sending the email. Please try again later.");
                    this._spinner.hide()

                }
            }
        );
    }


    getAllSignature() {
        let admin = JSON.parse(localStorage.getItem('admin'));
        this._emailSignature.getSignature(admin.admin_id).subscribe(res => {
            this.emailSignatures = res.data;
        });
    }

    onSignatureSelected(event: Event): void {
        const selectedTitle = (event.target as HTMLInputElement).value;
        this.selectedSignature = this.emailSignatures.find(signature => signature.title === selectedTitle);
    }
}

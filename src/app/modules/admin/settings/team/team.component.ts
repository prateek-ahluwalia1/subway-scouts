import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit, ViewEncapsulation } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { SupportService } from '../support.service';
import { NgForm } from '@angular/forms';
import { ToastServiceService } from 'app/services/toast-service.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpClient, HttpEventType, HttpHeaders } from '@angular/common/http';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
interface Attachment {
    fileName: string;
    size: string;
    url?: any
}
@Component({
    selector: 'settings-team',
    templateUrl: './team.component.html',
    styles: [`
    .crm-email-attachments {
    .attachment {
        display: flex;
        align-items: center;
        background: #eee;
        box-shadow: 0 8px 8px -4px lightblue;
        justify-content: space-around;
        padding: 10px;
        cursor: pointer;
    }
}`],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class SettingsTeamComponent implements OnInit {
    @Input() data: string;
    name: string = '';
    email: string = '';
    subject: string = '';
    message: string = '';
    priority: string = '';

    uploadFile
    uploadFileUrl
    attachments: Attachment[] = [];
    progress: number;

    submitted: boolean = false
    constructor(public global: GlobalVariable, private service: SupportService, private toast: ToastServiceService,
        private _changeDetectorRef: ChangeDetectorRef, private spinner: NgxSpinnerService, private http: HttpClient,
        private trackAdmin: TrackAdminActivityService,) { }

    ngOnInit(): void {
        this.name = this.global.admin.admin_name;
        this.email = this.global.admin.admin_email;
    }

    submitForm(ticketForm: NgForm) {
        this.submitted = true
        this.spinner.show()
        if (ticketForm.valid) {
            const ticketData = this.createTicketData();
            this.service.submitTicket(ticketData).subscribe(
                ({ success, message }) => {
                    if (success) {
                        this.toast.toastNotification(message, 'Support Ticket!');
                        this.resetForm();
                        this.trackAdmin.storeActivity('Support Ticket', `Open a ticket wit subject: ${this.subject}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
                        })
                        this.submitted = false
                        this.attachments = []
                        this._changeDetectorRef.markForCheck();
                    }
                    this.spinner.hide()
                },
                (error) => {
                    console.error('Error submitting ticket:', error);
                    this.spinner.hide()
                }
            );
        }
        else {
            this.spinner.hide()
        }
    }

    private createTicketData() {
        const commonTicketData = {
            name: this.name,
            email: this.email,
            subject: this.subject,
            priority: this.priority,
            message: this.message,
            ticket_type: this.data,
            attachments: this.attachments
        };

        if (this.global.admin?.admin_user_type !== 'guard') {
            commonTicketData['admins_id'] = this.global.admin.admin_id;
        } else {
            commonTicketData['guard_id'] = this.global.admin.admin_id;
        }

        return commonTicketData;
    }

    resetForm() {
        this.subject = '';
        this.message = '';
        this.priority = '';
    }


    addAttachments() {
        let input = document.createElement("input");
        input.type = "file";
        input.accept = "image/*,application/pdf";
        input.onchange = (_) => {
            let files = Array.from(input.files);
            if (!files || files.length === 0) return;
            this.uploadFile = files[0];
            const objectURL = URL.createObjectURL(this.uploadFile);
            const headers = new HttpHeaders({
                'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
            });

            const formData = new FormData();
            formData.append('file', this.uploadFile, this.uploadFile.name);
            formData.append('folder', 'crm_customers_file');
            formData.append('admin_id', this.global.admin.admin_id);
            formData.append('type', 'mail');

            this.http.post(this.global.baseUrl + 'upload-file-with-size', formData, {
                headers: headers,
                reportProgress: true, // Enables progress tracking
                observe: 'events'
            }).subscribe(
                (event: any) => {
                    if (event.type === HttpEventType.UploadProgress) {
                        this.progress = Math.round((100 / event.total) * event.loaded);
                    } else if (event.type === HttpEventType.Response) {
                        // File upload is complete
                        this.progress = null;
                        const response = event.body;
                        if (response && response.success) {
                            this.attachments.push({
                                fileName: response.name,
                                url: response.url,
                                size: response.size
                            });
                            this._changeDetectorRef.markForCheck()
                        }
                    }
                },
                (error) => {
                    console.error(error);
                    this.progress = null;
                }
            );
        };
        input.click();
    }


    removeAttachment(attachment: Attachment): void {
        const index = this.attachments.indexOf(attachment);
        if (index >= 0) {
            this.attachments.splice(index, 1);
        }
    }

    roundSize(size: string): string {
        let sizeInKB = parseFloat(size);
        if (sizeInKB > 0) {
            const sizes = ['KB', 'MB', 'GB', 'TB'];
            let i = 0;
            while (sizeInKB >= 1024 && i < sizes.length - 1) {
                sizeInKB /= 1024;
                i++;
            }
            return sizeInKB.toFixed(2) + ' ' + sizes[i];
        } else {
            return '';
        }
    }

    viewFile(url) {
        window.open(url, '_blank')
    }



}

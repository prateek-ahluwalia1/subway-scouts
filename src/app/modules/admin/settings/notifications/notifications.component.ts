import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { UntypedFormGroup } from '@angular/forms';
import { SupportService } from '../support.service';
import { GlobalVariable } from 'app/shared/global';
import { ToastServiceService } from 'app/services/toast-service.service';
import { HttpClient, HttpEventType, HttpHeaders } from '@angular/common/http';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
interface Attachment {
    fileName: string;
    size: string;
    url?: any
}
@Component({
    selector: 'settings-notifications',
    templateUrl: './notifications.component.html',
    styleUrls: ['./notifications.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class SettingsNotificationsComponent implements OnInit {
    notificationsForm: UntypedFormGroup;
    @Input() chatObj;
    chats: any[] = []
    message: string = '';
    chatDetail
    uploadFile
    uploadFileUrl
    attachments: Attachment[] = [];
    progress: number;

    constructor(
        private service: SupportService,
        public global: GlobalVariable,
        private _changeDetectorRef: ChangeDetectorRef,
        private toast: ToastServiceService,
        private http: HttpClient, private trackAdmin: TrackAdminActivityService,
    ) {
    }

    ngOnInit() {
        this.getChats(this.chatObj)
    }
    getChats(data) {
        this.service.getChats(data).subscribe(({ success, data, ticketDetails }) => {
            if (success) {
                data?.forEach(element => {
                    if (element.attachments && element.attachments != '{}' && element.attachments != '[]' && element.attachments != 'null') {
                        element.attachments = JSON.parse(element.attachments);
                    }
                    else {
                        element.attachments = []
                    }
                });
                this.chats = data
                this.chatDetail = ticketDetails
                this._changeDetectorRef.markForCheck()
            }
        })
    }


    submitChatForm() {
        let data = {
            ticket_id: this.chatObj.id,
            guard_id: this.global.admin.admin_user_type == 'guard' ? this.chatObj.guard_id : null,
            message: this.message,
            admin_id: this.global.admin.admin_id,
            attachments: this.attachments
        }
        this.service.sendMessage(data).subscribe(res => {
            if (res.success) {
                this.attachments = []
                this.getChats(this.chatObj)
                this.trackAdmin.storeActivity('Support', `Reply to a ticket Requester: ${this.chatDetail.name} and subject ${this.chatObj?.subject}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
                    this.message = '';
                })
                this._changeDetectorRef.markForCheck()
            }

        }, (error => {
            this.toast.toastNotification1('something went wrong. Please try again', 'Request Incomplete!')
        }))
        this.message = '';
        // Clear the textarea
    }

    closeTicket() {
        this.service.closeTicket(this.chatObj?.id).subscribe(({ success, message }) => {
            if (success) {
                this.getChats(this.chatObj)
                this.trackAdmin.storeActivity('Support', `Close a ticket: ${this.chatObj?.subject}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
                })
                this.toast.toastNotification(message, 'Suporrt Operation!')
            }
        }, (error => {
            this.toast.toastNotification1('something went wrong. Please try again', 'Request Incomplete!')
        }))
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
                        }
                    }
                    this._changeDetectorRef.markForCheck()
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

        // Check if the size is greater than zero before formatting
        if (sizeInKB > 0) {
            const sizes = ['KB', 'MB', 'GB', 'TB'];
            let i = 0;
            while (sizeInKB >= 1024 && i < sizes.length - 1) {
                sizeInKB /= 1024;
                i++;
            }
            return sizeInKB.toFixed(2) + ' ' + sizes[i];
        } else {
            return ''; // Return an empty string if the size is zero
        }
    }

    viewFile(url) {
        window.open(url, '_blank')
    }
}

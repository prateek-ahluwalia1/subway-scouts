import { COMMA, ENTER } from "@angular/cdk/keycodes";
import { HttpClient, HttpEventType, HttpHeaders } from "@angular/common/http";
import { Component, Input, OnInit } from "@angular/core";
import { FormBuilder } from "@angular/forms";
import { MatChipInputEvent } from "@angular/material/chips";
import { Router } from "@angular/router";
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";
import { AgentService } from "app/services/crm/agent.service";
import { ToastServiceService } from "app/services/toast-service.service";
import { GlobalVariable } from "app/shared/global";
import { NgxSpinnerService } from "ngx-spinner";
export interface Bcc {
    email: string;
}
interface Attachment {
    fileName: string;
    size: string;
    url?: any
}
@Component({
    selector: "email-model",
    templateUrl: "./email-modal.component.html",
    styleUrls: ["./email-modal.component.scss"],
})
export class EmailModalComponent implements OnInit {


    public addEmail;
    progress: number;
    @Input() data: any;
    from: string = ''; // Separate variable for 'from' field
    to: string[] = []; // Separate array for 'to' emails
    subject: string = ''; // Separate variable for 'subject' field
    message: string = '';

    backUrl: string = '/crm/quotation';


    bcc: Bcc[] = [];
    selectable = true;
    removable = true;
    addOnBlur = true;
    attachments: Attachment[] = [];
    readonly separatorKeysCodes: number[] = [ENTER, COMMA];

    add(event: MatChipInputEvent, type): void {
        const input = event.input;
        const value = event.value;
        if ((value || '').trim()) {
            if (type == 'bcc') {
                this.bcc.push({ email: value.trim() });
            }
        }
        // Reset the input value
        if (input) {
            input.value = '';
        }
    }

    remove(fruit: Bcc, type): void {
        if (type == 'bcc') {
            const index = this.bcc.indexOf(fruit);
            if (index >= 0) {
                this.bcc.splice(index, 1);
            }
        }
    }
    constructor(public activeModal: NgbActiveModal, private fb: FormBuilder, private toast: ToastServiceService,
        private service: AgentService, public global: GlobalVariable, private http: HttpClient,
        private spinnerService: NgxSpinnerService,
    ) {

    }
    ngOnInit(): void {
        if (this.data?.attachments && this.data?.attachments.length > 0) {
            this.data.attachments.forEach(element => {
                this.attachments.push({
                    fileName: element.name,
                    size: element.size ?? 'N/A',
                    url: element.url
                });
            });
        }
        console.log(this.data);
        this.addEmail = this.fb.group({
            body: [],
            subject: []
        })

        if (this.data && this.data.email) {
            this.bcc.push({ email: this.data.email });
        }

        if (this.data) {
            this.addEmail.patchValue({
                subject: this.data?.subject,
                body: this.data?.message
            });
            if (this.data.to) {
                this.bcc.push({ email: this.data?.to?.map(recipient => recipient) });
            }
        }
    }



    uploadFile
    uploadFileUrl

    handleFileInput() {
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

    // roundSize(size: string): string {
    //     let sizeInKB = parseFloat(size);
    //     const sizes = ['KB', 'MB', 'GB', 'TB'];
    //     let i = 0;
    //     while (sizeInKB >= 1024 && i < sizes.length - 1) {
    //         sizeInKB /= 1024;
    //         i++;
    //     }
    //     return sizeInKB.toFixed(2) + ' ' + sizes[i];
    // }

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


    onSubmit() {
        const attachmentsArray = this.attachments.length > 0
            ? this.attachments.map(attachment => ({ name: attachment.fileName, url: attachment.url }))
            : [];
        const formData = {
            ...this.addEmail.value,
            bcc: this.bcc.map(item => item.email),
            attachments: attachmentsArray,
            lead_id: this.data.id,
            user_id: this.global.admin.admin_id
        };
        if (this.attachments.length === 0) {
            let title = 'Confirm Action'
            let message = 'Do you want to send this without attachments?'
            let yes = 'Yes'
            let no = 'No'
            this.global.confirmationBox(title, message, yes, no).subscribe(res => {
                if (res.clickedButtonID == 'Yes') {
                    this.spinnerService.show();
                    this.service.sendEmail(formData).subscribe(({ message, success }) => {
                        if (success) {
                            this.toast.toastNotification(message, 'Send Email!')
                            this.activeModal.close("Accept click");
                        }
                        this.spinnerService.hide();
                    })
                }

            })
        }
        else {
            this.spinnerService.show();
            this.service.sendEmail(formData).subscribe(({ message, success }) => {
                if (success) {
                    this.toast.toastNotification(message, 'Send Email!')
                    this.activeModal.close("Accept click");
                }
                this.spinnerService.hide();
            })
        }
    }

    viewFile(url) {
        window.open(url, '_blank')
    }

    activatedModal(data) {
        this.activeModal.close(data);
        this.addEmail.reset()
        this.data = []
        this.attachments = []
        this.bcc = []
    }
}

import { ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, Inject, Input, OnDestroy, OnInit, ViewChild, ViewChildren, ViewEncapsulation } from '@angular/core';
import { FormBuilder, FormControlName, FormGroup } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { ScrumboardService } from '../../scrumboard.service';
import { GenericValidator } from 'app/shared/generic-validator';
import { ToastServiceService } from 'app/services/toast-service.service';
import { AgentService } from 'app/services/crm/agent.service';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { MatSelect } from '@angular/material/select';
import { ServiceService } from 'app/services/service.service';

@Component({
    selector: 'scrumboard-card-details',
    templateUrl: './details.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ScrumboardCardDetailsComponent implements OnInit, OnDestroy {
    @ViewChildren(FormControlName, { read: ElementRef }) formInputElements: ElementRef[];
    @ViewChild(MatSelect, { static: false }) selectElement: MatSelect;
    @Input() id
    cardData: any = []
    fieldColspan = 3;
    cardForm: FormGroup;
    errorMessage: string;
    agents: any = []
    isAgentHave: boolean = false
    displayMessage: { [key: string]: string } = {};
    comments: any = []
    private genericValidator: GenericValidator;
    private validationMessages: { [key: string]: { [key: string]: string } | {} } = {
        title: {
            required: 'Name is required.',
            minlength: 'Name must be at least five characters.',
            maxlength: 'Name cannot exceed 100 characters.'
        },
    };
    constructor(private fb: FormBuilder, private service: ScrumboardService, private cusService: AgentService,
        private _changeDetectorRef: ChangeDetectorRef,
        public server: ServiceService,
        private toast: ToastServiceService, public ngbActiveModal: NgbActiveModal,
    ) {
    }
    ngOnInit(): void {
        if (this.id) {
            this.getCardDdata(this.id)
        }
        this.cardForm = this.fb.group({
            description: [''],
            saleperson_id: [''],
            comment: [''],
        });

        this.server.getAdmin('active')
            .subscribe(agents => {
                this.agents = agents.data
            });

        this.getComments()
        this._changeDetectorRef.markForCheck();

    }

    ngOnDestroy(): void {
    }

    getComments() {
        this.service.getComment(this.id).subscribe(({ success, data }) => {
            if (success) {
                this.comments = data
                this._changeDetectorRef.markForCheck();
            }

        })
    }

    saveProject(): void {
        if (this.id) {
            this.cardForm.value.id = this.id
        }
        this.service.saveCard(this.cardForm.value).subscribe(({ message, success }) => {
            if (success) {
                this.toast.toastNotification(message, 'Task Operation!')
                this.dismiss(success)
            }
        })
    }

    dismiss(e) {
        this.ngbActiveModal.dismiss(e);
    }
    ngAfterViewInit() {
        this.setFocusOnSelect();
    }
    getCardDdata(id) {
        let data = {
            id: id
        }
        this.service.getCardDdata(data).subscribe(({ message, success, data }) => {
            if (success) {
                if (data.saleperson_id) {
                    this.isAgentHave = true
                }
                else {
                    this.isAgentHave = false
                }
                this.cardData = data
                console.log(this.cardData);

                this.cardForm.patchValue({
                    description: data.description,
                    saleperson_id: parseInt(data.saleperson_id),
                    phone: data.phone,
                });
            }
            this._changeDetectorRef.markForCheck();
        })

    }

    addAgent() {
        this.isAgentHave = !this.isAgentHave
    }
    setFocusOnSelect() {
        if (this.selectElement) {
            setTimeout(() => {
                this.selectElement.focus();
            }, 0);
        }
    }
    // Assuming you have a function to calculate the time difference in a human-readable format
    getTimeAgo(createdAt: string): string {
        const createdTime = new Date(createdAt);
        const currentTime = new Date();
        const timeDifferenceMilliseconds = currentTime.getTime() - createdTime.getTime();

        // Convert milliseconds to seconds, minutes, hours, and days
        const seconds = Math.floor(timeDifferenceMilliseconds / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 0) {
            return `${days} day${days > 1 ? 's' : ''} ago`;
        } else if (hours > 0) {
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else if (minutes > 0) {
            return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        } else {
            return `${seconds} second${seconds !== 1 ? 's' : ''} ago`;
        }
    }


    save(): void {
        if (this.id) {
            this.cardForm.value.id = this.id
        }
        this.service.saveComment(this.cardForm.value).subscribe(({ message, success }) => {
            if (success) {
                this.cardForm.get('comment').setValue('');
                this.getComments()
            }
        })
    }

}

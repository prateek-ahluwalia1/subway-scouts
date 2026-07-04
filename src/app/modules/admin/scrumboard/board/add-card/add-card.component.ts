import { ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, EventEmitter, Input, OnInit, Output, ViewChild, ViewEncapsulation } from '@angular/core';
import { CdkTextareaAutosize } from '@angular/cdk/text-field';
import { FormGroup, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { AgentService } from 'app/services/crm/agent.service';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
    selector: 'scrumboard-board-add-card',
    templateUrl: './add-card.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ScrumboardBoardAddCardComponent implements OnInit {
    @Input() buttonTitle: string = 'Add a customer';
    @Output() readonly saved: EventEmitter<{ customer_id: string }> = new EventEmitter<{ customer_id: string }>();

    form: FormGroup;
    formVisible: boolean = false;
    customer: any = []
    /**
     * Constructor
     */
    constructor(
        private _changeDetectorRef: ChangeDetectorRef,
        private _formBuilder: UntypedFormBuilder,
        private service: AgentService,
        private toast: ToastServiceService
    ) {
    }

    // -----------------------------------------------------------------------------------------------------
    // @ Lifecycle hooks
    // -----------------------------------------------------------------------------------------------------

    /**
     * On init
     */
    ngOnInit(): void {
        // Initialize the new list form
        this.form = this._formBuilder.group({
            customer_id: ['', [Validators.required]]
        });


        this.service.getList('customer')
            .subscribe(customers => {
                this.customer = customers.data
            });
    }


    save(): void {
        const customer_id = this.form.get('customer_id').value;
        if (this.form.invalid) {
            this.toast.toastNotification1('Please select a customer for further processing', 'Invalid Field!');
            return;
        }

        console.log('Valid form submission');
        console.log(customer_id);

        // Rest of your code for valid form submission
        this.saved.next({ customer_id: customer_id });
        this.formVisible = false;
        this.form.get('customer_id').setValue('');
        this._changeDetectorRef.markForCheck();
    }

    /**
     * Toggle the visibility of the form
     */
    toggleFormVisibility(): void {
        // Toggle the visibility
        this.formVisible = !this.formVisible;

        // If the form becomes visible, focus on the title field
        if (this.formVisible) {
            // this.titleInput.nativeElement.focus();
        }
    }
}

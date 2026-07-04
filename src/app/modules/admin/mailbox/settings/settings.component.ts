import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { UntypedFormArray, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { debounceTime, take } from 'rxjs';
import { MailboxComponent } from 'app/modules/admin/mailbox/mailbox.component';
import { MailboxService } from 'app/modules/admin/mailbox/mailbox.service';
import { MailLabel } from 'app/modules/admin/mailbox/mailbox.types';
import { labelColorDefs, labelColors } from 'app/modules/admin/mailbox/mailbox.constants';

@Component({
    selector     : 'mailbox-settings',
    templateUrl  : './settings.component.html',
    encapsulation: ViewEncapsulation.None
})
export class MailboxSettingsComponent implements OnInit
{
    labelColors: any = labelColors;
    labelColorDefs: any = labelColorDefs;
    labels: MailLabel[];
    labelsForm: UntypedFormGroup;

    /**
     * Constructor
     */
    constructor(
        public mailboxComponent: MailboxComponent,
        private _formBuilder: UntypedFormBuilder,
        private _mailboxService: MailboxService
    )
    {
    }

    // -----------------------------------------------------------------------------------------------------
    // @ Lifecycle hooks
    // -----------------------------------------------------------------------------------------------------

    /**
     * On init
     */
    ngOnInit(): void
    {
        // Create the labels form
        this.labelsForm = this._formBuilder.group({
            labels  : this._formBuilder.array([]),
            newLabel: this._formBuilder.group({
                title: ['', Validators.required],
                color: ['orange']
            })
        });

    }

    // -----------------------------------------------------------------------------------------------------
    // @ Public methods
    // -----------------------------------------------------------------------------------------------------

    /**
     * Add a label
     */
    addLabel(): void
    {
        // this._mailboxService.addLabel(this.labelsForm.get('newLabel').value).subscribe((addedLabel) => {

        //     (this.labelsForm.get('labels') as UntypedFormArray).push(this._formBuilder.group({
        //         id   : [addedLabel.id],
        //         title: [addedLabel.title, Validators.required],
        //         slug : [addedLabel.slug],
        //         color: [addedLabel.color]
        //     }));

        //     // Reset the new label form
        //     this.labelsForm.get('newLabel').markAsPristine();
        //     this.labelsForm.get('newLabel').markAsUntouched();
        //     this.labelsForm.get('newLabel.title').reset();
        //     this.labelsForm.get('newLabel.title').clearValidators();
        //     this.labelsForm.get('newLabel.title').updateValueAndValidity();
        // });
    }

    /**
     * Delete a label
     */
    deleteLabel(id: string): void
    {
        // Get the labels form array
        const labelsFormArray = this.labelsForm.get('labels') as UntypedFormArray;

        // Remove the label from the labels form array
        labelsFormArray.removeAt(labelsFormArray.value.findIndex(label => label.id === id));

        // Delete label on the server
        // this._mailboxService.deleteLabel(id).subscribe();
    }

    /**
     * Update labels
     */
    updateLabels(): void
    {
        // Iterate through the labels form array controls
        (this.labelsForm.get('labels') as UntypedFormArray).controls.forEach((labelFormGroup) => {

            // If the label has been edited...
            if ( labelFormGroup.dirty )
            {
                // Update the label on the server
                // this._mailboxService.updateLabel(labelFormGroup.value.id, labelFormGroup.value).subscribe();
            }
        });

        // Reset the labels form array
        this.labelsForm.get('labels').markAsPristine();
        this.labelsForm.get('labels').markAsUntouched();
    }
}

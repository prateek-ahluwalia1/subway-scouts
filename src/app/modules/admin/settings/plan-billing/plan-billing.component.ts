import { ChangeDetectionStrategy, Component, EventEmitter, OnInit, Output, ViewEncapsulation } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup } from '@angular/forms';

@Component({
    selector: 'settings-plan-billing',
    templateUrl: './plan-billing.component.html',
    styleUrls: ['./plan-billing.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class SettingsPlanBillingComponent implements OnInit {
    @Output() dataEvent = new EventEmitter<string>();
    constructor(
        private _formBuilder: UntypedFormBuilder
    ) {
    }
    ngOnInit(): void {

    }

    sendDataToParent(type) {
        const dataToSend = type; // Replace with your data
        this.dataEvent.emit(dataToSend);
      }

}

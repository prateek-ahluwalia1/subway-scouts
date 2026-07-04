import { ChangeDetectionStrategy, Component, ViewEncapsulation } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';

@Component({
    selector: 'scrumboard',
    templateUrl: './scrumboard.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ScrumboardComponent {
    /**
     * Constructor
     */
    constructor(
    ) {
    }

}

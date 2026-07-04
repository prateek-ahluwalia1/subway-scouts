import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { MAT_DATE_FORMATS } from '@angular/material/core';
import * as moment from 'moment';
import { SharedModule } from 'app/shared/shared.module';
import { ScrumboardBoardAddCardComponent } from './board/add-card/add-card.component';
import { ScrumboardBoardAddListComponent } from './board/add-list/add-list.component';
import { ScrumboardBoardComponent } from './board/board.component';
import { ScrumboardBoardsComponent } from './boards/boards.component';
import { ScrumboardCardComponent } from './card/card.component';
import { ScrumboardCardDetailsComponent } from './card/details/details.component';
import { ScrumboardComponent } from './scrumboard.component';
import { scrumboardRoutes } from './scrumboard.routing';
import { MaterialModule } from 'app/shared/material.module';
import { AddNewBusinessComponent } from './add-new-business/add-new-business.component';
import {MatGridListModule} from '@angular/material/grid-list';


@NgModule({
    declarations: [
        ScrumboardComponent,
        ScrumboardBoardsComponent,
        ScrumboardBoardComponent,
        ScrumboardBoardAddCardComponent,
        ScrumboardBoardAddListComponent,
        ScrumboardCardComponent,
        ScrumboardCardDetailsComponent,
        AddNewBusinessComponent
    ],
    imports     : [
        RouterModule.forChild(scrumboardRoutes),
        MaterialModule,
        SharedModule,MatGridListModule
    ],
    providers   : [
        {
            provide : MAT_DATE_FORMATS,
            useValue: {
                parse  : {
                    dateInput: moment.ISO_8601
                },
                display: {
                    dateInput         : 'll',
                    monthYearLabel    : 'MMM YYYY',
                    dateA11yLabel     : 'LL',
                    monthYearA11yLabel: 'MMMM YYYY'
                }
            }
        }
    ]
})
export class ScrumboardModule
{
}

import { Route } from '@angular/router';
import { ScrumboardBoardComponent } from './board/board.component';
import { ScrumboardBoardsComponent } from './boards/boards.component';
import { ScrumboardCardComponent } from './card/card.component';
import { ScrumboardBoardsResolver, ScrumboardBoardResolver, ScrumboardCardResolver } from './scrumboard.resolvers';


export const scrumboardRoutes: Route[] = [
    {
        path     : '',
        component: ScrumboardBoardsComponent,
        resolve  : {
            boards: ScrumboardBoardsResolver
        }
    },
    {
        path     : ':boardId',
        component: ScrumboardBoardComponent,
        // resolve  : {
        //     board: ScrumboardBoardResolver
        // },
    }
];

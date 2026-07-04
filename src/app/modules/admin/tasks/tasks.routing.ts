import { Route } from '@angular/router';
import { CanDeactivateTasksDetails } from 'app/modules/admin/tasks/tasks.guards';
import { TasksResolver, TasksTaskResolver } from 'app/modules/admin/tasks/tasks.resolvers';
import { TasksComponent } from 'app/modules/admin/tasks/tasks.component';
import { TasksListComponent } from 'app/modules/admin/tasks/list/list.component';
import { TasksDetailsComponent } from 'app/modules/admin/tasks/details/details.component';

export const tasksRoutes: Route[] = [
    {
        path     : '',
        component: TasksComponent,
        children : [
            {
                path     : '',
                component: TasksListComponent,
                // resolve  : {
                //     tasks: TasksResolver
                // },
                children : [
                    {
                        path         : 'add-notes',
                        component    : TasksDetailsComponent,
                        canDeactivate: [CanDeactivateTasksDetails]
                    },
                    {
                        path         : ':id',
                        component    : TasksDetailsComponent,
                        canDeactivate: [CanDeactivateTasksDetails]
                    }
                ]
            }
        ]
    }
];

import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Inject, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { DOCUMENT } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { MatDrawer } from '@angular/material/sidenav';
import { filter, fromEvent, Subject, takeUntil } from 'rxjs';
import { FuseMediaWatcherService } from '@fuse/services/media-watcher';
import { FuseNavigationService, FuseVerticalNavigationComponent } from '@fuse/components/navigation';
import { Tag, Task } from 'app/modules/admin/tasks/tasks.types';
import { TasksService } from 'app/modules/admin/tasks/tasks.service';
import { GlobalVariable } from 'app/shared/global';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
    selector: 'tasks-list',
    templateUrl: './list.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
    styles: [
        `
        .notification-tabs .mat-ink-bar{
            background-color: #01A37E !important;
        }
        `
    ]
})

export class TasksListComponent implements OnInit, OnDestroy {

    @ViewChild('matDrawer', { static: true }) matDrawer: MatDrawer;
    drawerMode: 'side' | 'over';
    selectedTask: Task;
    tasks: any = [];
    sents: any = [];
    tasksCount: any = {
        completed: 0,
        incomplete: 0,
        total: 0
    };
    adminId
    private _unsubscribeAll: Subject<any> = new Subject<any>();

    constructor(
        private _activatedRoute: ActivatedRoute,
        private _changeDetectorRef: ChangeDetectorRef,
        @Inject(DOCUMENT) private _document: any,
        private _router: Router,
        private _tasksService: TasksService,
        private _fuseMediaWatcherService: FuseMediaWatcherService,
        private _fuseNavigationService: FuseNavigationService,
        private global: GlobalVariable,
        private toast: ToastServiceService
    ) {}

    ngOnInit(): void {
        this.getTasks()
        // Subscribe to media query change
        this._fuseMediaWatcherService.onMediaQueryChange$('(min-width: 1440px)')
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe((state) => {
                this.drawerMode = state.matches ? 'side' : 'over';
                this._changeDetectorRef.markForCheck();
            });

        // Listen for shortcuts
        fromEvent(this._document, 'keydown')
            .pipe(
                takeUntil(this._unsubscribeAll),
                filter<KeyboardEvent>(event =>
                    (event.ctrlKey === true || event.metaKey) // Ctrl or Cmd
                    && (event.key === '/' || event.key === '.') // '/' or '.' key
                )
            )
            .subscribe((event: KeyboardEvent) => {
                // If the '/' pressed
                if (event.key === '/') {
                    this.createTask('task');
                }
                // If the '.' pressed
                if (event.key === '.') {
                    this.createTask('section');
                }
            });
    }

    ngOnDestroy(): void {
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
    }

    onBackdropClicked(): void {
        this._router.navigate(['./'], { relativeTo: this._activatedRoute });
        this._changeDetectorRef.markForCheck();
    }

    createTask(type: 'task' | 'section'): void {
        this._router.navigate(['add-notes'], { relativeTo: this._activatedRoute });
        this._changeDetectorRef.markForCheck();
    }

    toggleCompleted(task): void {
        if (task.is_read == 0) {
            let data = {
                id: [task.id],
                admin_id: this.global.admin.admin_id
            }
            
            this._tasksService.readTask(data).subscribe(({ success, msg }) => {
                if (success) {
                    this.toast.toastNotification(msg, 'Operation Notes!')
                    this.getTasks()
                }
            },
            (error => {
                this.toast.toastNotification1('Something went wrong. Please try again', 'Request Incomplete!')
                this.getTasks()
            }))
            this._changeDetectorRef.markForCheck();
        }
    }

    dropped(event: CdkDragDrop<Task[]>): void {
        moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        this._tasksService.updateTasksOrders(event.container.data).subscribe();
        this._changeDetectorRef.markForCheck();
    }

    trackByFn(index: number, item: any): any {
        return item.id || index;
    }

    getTasks() {
        this._tasksService.getTasks().subscribe(({ success, inbox, send_msg }) => {
            this.global.unreadNotes = inbox.length
            if (success) {
                localStorage.setItem('unreadNotes', inbox.length)
                if(inbox){
                    this.tasks = inbox.map(task => {
                        const [date, time] = task.created_at.split(' ');
                        return {
                            ...task,
                            date: date,
                            time: time
                        };
                    });
                }
                if(send_msg){
                    this.sents = send_msg.map(task => {
                        const [date, time] = task.created_at.split(' ');
                        return {
                            ...task,
                            date: date,
                            time: time
                        };
                    });
                    // this.sents = send_msg
                }
                this.global.unreadNotes = inbox.filter(task => task.is_read === 0).length
                this.tasksCount.incomplete = inbox.filter(task => task.is_read === 0).length;
                this.tasksCount.completed = inbox.filter(task => task.is_read === 1).length;
                this.tasksCount.total = inbox.length + send_msg.length;
                this._changeDetectorRef.markForCheck();
            }
        })
    }
}



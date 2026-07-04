import { AfterViewInit, ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, OnDestroy, OnInit, TemplateRef, ViewChild, ViewContainerRef, ViewEncapsulation } from '@angular/core';
import { ActivatedRoute, NavigationEnd, Router } from '@angular/router';
import { FormControl, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { Overlay, OverlayRef } from '@angular/cdk/overlay';
import { MatDrawerToggleResult } from '@angular/material/sidenav';
import { FuseConfirmationService } from '@fuse/services/confirmation';
import { filter, Subject, takeUntil } from 'rxjs';
import * as moment from 'moment';
import { Tag, Task } from 'app/modules/admin/tasks/tasks.types';
import { TasksListComponent } from 'app/modules/admin/tasks/list/list.component';
import { TasksService } from 'app/modules/admin/tasks/tasks.service';
import { MatOption } from '@angular/material/core';
import { MatSelect } from '@angular/material/select';
import { ServiceService } from 'app/services/service.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
interface Admin {
    id: string;
    name: string;
}

@Component({
    selector: 'tasks-details',
    templateUrl: './details.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class TasksDetailsComponent implements OnInit, AfterViewInit, OnDestroy {

    adminId = new FormControl(null, Validators.required)
    allSelected = false;
    adminList: Admin[] = [];
    filteredAdminList: Admin[] = [];
    @ViewChild('select') select: MatSelect;
    task: Task;
    taskForm: UntypedFormGroup;
    tasks: Task[];
    private _tagsPanelOverlayRef: OverlayRef;
    private _unsubscribeAll: Subject<any> = new Subject<any>();
    id
    viewData
    viewOnly: boolean = false
    adminsNameSent

    constructor(
        private _activatedRoute: ActivatedRoute,
        private _changeDetectorRef: ChangeDetectorRef,
        private _formBuilder: UntypedFormBuilder,
        private _fuseConfirmationService: FuseConfirmationService,
        private _tasksListComponent: TasksListComponent,
        private _tasksService: TasksService,
        private service: ServiceService,
        private toast: ToastServiceService,
        private sanitizer: DomSanitizer
    ) {}

    ngOnInit(): void {
        this.getAllAdmins()
        this.taskForm = this._formBuilder.group({
            id: [''],
            note: [''],
            title: ['', Validators.required],
            completed: [false],
            dueDate: [null],
            priority: [0],
            send_to: this.adminId
        });
        this._activatedRoute.params.subscribe(params => {
            this.id = params['id'];
            if (this.id) {
                this.viewNotes()
            }
            else {
                this.viewOnly = false
                this._tasksListComponent.matDrawer.open();
            }
        });
    }

    viewNotes() {
        this.taskForm.reset()
        this._tasksService.viewNotes(this.id).subscribe(({ success, data }) => {
            if (success) {
                this.viewData = data
                this.viewOnly = true
                this.getAllAdmins()
                this._tasksListComponent.matDrawer.open();
                this._changeDetectorRef.markForCheck();
            }
        })
    }

    toggleAllSelection() {
        if (this.allSelected) {
            this.select.options.forEach((item: MatOption) => item.select());
        } else {
            this.select.options.forEach((item: MatOption) => item.deselect());
        }
    }

    ngAfterViewInit(): void {
        this._tasksListComponent.matDrawer.openedChange.pipe(
            takeUntil(this._unsubscribeAll),
            filter(opened => opened)
        )
        .subscribe(() => {});
    }

    ngOnDestroy(): void {
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
        if (this._tagsPanelOverlayRef) {
            this._tagsPanelOverlayRef.dispose();
        }
    }

    closeDrawer(): Promise<MatDrawerToggleResult> {
        return this._tasksListComponent.matDrawer.close();
    }

    toggleCompleted(): void {
        // Get the form control for 'completed'
        const completedFormControl = this.taskForm.get('completed');
        // Toggle the completed status
        completedFormControl.setValue(!completedFormControl.value);
    }

    setTaskPriority(priority): void {
        // Set the value
        this.taskForm.get('priority').setValue(priority);
    }

    isOverdue(): boolean {
        return moment(this.task.dueDate, moment.ISO_8601).isBefore(moment(), 'days');
    }

    /**
     * Delete the task
     */
    deleteTask(): void {
        // Open the confirmation dialog
        const confirmation = this._fuseConfirmationService.open({
            title: 'Delete task',
            message: 'Are you sure you want to delete this task? This action cannot be undone!',
            actions: {
                confirm: {
                    label: 'Delete'
                }
            }
        });

        confirmation.afterClosed().subscribe((result) => {
            if (result === 'confirmed') {
                const id = this.task.id;
                const currentTaskIndex = this.tasks.findIndex(item => item.id === id);
                const nextTaskIndex = currentTaskIndex + ((currentTaskIndex === (this.tasks.length - 1)) ? -1 : 1);
                // this._tasksService.deleteTask(id)
                //     .subscribe((isDeleted) => {
                //         if (!isDeleted) {
                //             return;
                //         }

                //         if (nextTaskId) {
                //             this._router.navigate(['../', nextTaskId], { relativeTo: this._activatedRoute });
                //         }
                //         else {
                //             this._router.navigate(['../'], { relativeTo: this._activatedRoute });
                //         }
                //     });
                this._changeDetectorRef.markForCheck();
            }
        });
    }

    trackByFn(index: number, item: any): any {
        return item.id || index;
    }

    onFormSubmit(): void {
        if (this.taskForm.valid) {
            const formData = this.taskForm.value;
            console.log(formData);
            this._tasksService.saveNotes(formData).subscribe(({ success, msg }) => {
                if (success) {
                    this.toast.toastNotification(msg, 'Operation Notes!')
                    this.closeDrawer()
                    this.taskForm.reset()
                    this._tasksListComponent.getTasks()
                }
                this._changeDetectorRef.markForCheck();
            },
            (error => {
                console.log(error);
            }));
        } else {}
    }

    getAllAdmins() {
        this.service.getAllAdmins().subscribe(({ success, data }) => {
            if (success) {
                this.adminList = data;
                this.filteredAdminList = data;
                if (this.viewData && this.viewData.send_to.length > 0) {
                    const sendToIds = JSON.parse(this.viewData.send_to);
                    this.adminsNameSent = this.adminList.filter(admin => sendToIds.includes(admin.id));
                    this.allSelected = true; // Check the "Select All" checkbox
                    this.adminId.setValue(this.viewData.send_to); // Set selected options
                    this.allSelected = true; // Check the "Select All" checkbox
                    this._changeDetectorRef.markForCheck();
                }
                else {
                    this.adminList = data;
                    this.filteredAdminList = data;
                }
            }
        });
    }

    sanitizeHtml(html: string): SafeHtml {
        return this.sanitizer.bypassSecurityTrustHtml(html);
    }
    
    filterAdminList(searchTerm: string) {
        if(searchTerm){
            this.filteredAdminList = this.adminList.filter(admin => 
                admin.name.toLowerCase().includes(searchTerm.toLowerCase())
            );
        }
        else{
            this.filteredAdminList = this.adminList;
        }
    }
}

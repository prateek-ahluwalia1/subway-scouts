import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Inject, OnDestroy, OnInit, TemplateRef, ViewChild, ViewContainerRef, ViewEncapsulation } from '@angular/core';
import { Overlay, OverlayRef } from '@angular/cdk/overlay';
import { TemplatePortal } from '@angular/cdk/portal';
import { MatButton } from '@angular/material/button';
import { Subject, interval, takeUntil, timer } from 'rxjs';
import { Notification } from 'app/layout/common/notifications/notifications.types';
import { NotificationsService } from 'app/layout/common/notifications/notifications.service';
import { ToasterService } from 'app/modules/admin/models/toaster/toaster.service';
import { GlobalVariable } from 'app/shared/global';
import { Router } from '@angular/router';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
    selector: 'notifications',
    templateUrl: './notifications.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
    exportAs: 'notifications',
    styles: [
        `
        .notification-tabs .mat-ink-bar{
            background-color: #01A37E !important;
        }
        `
    ]
})

export class NotificationsComponent implements OnInit, OnDestroy {

    @ViewChild('notificationsOrigin') private _notificationsOrigin: MatButton;
    @ViewChild('notificationsPanel') private _notificationsPanel: TemplateRef<any>;
    notifications: Notification[];
    unreadCount: number = 0;
    private _overlayRef: OverlayRef;
    private _unsubscribeAll: Subject<any> = new Subject<any>();
    selectedTabIndex = 0;
    greenWelfareCount = 0
    incidentCount = 0
    leaveCount = 0
    tabs = [
        { label: 'Green & Welfare', content: 'Content for Green & Welfare tab', value: 0 },
        { label: 'Incident', content: 'Content for Incident tab', value: 0 },
        { label: 'Staff Leave', content: 'Content for Staff Leave tab', value: 0 },
        { label: 'Jobs', content: 'Content for Jobs tab', value: 0 },
    ];

    constructor(
        private _changeDetectorRef: ChangeDetectorRef,
        private _notificationsService: NotificationsService,
        private _overlay: Overlay,
        private _viewContainerRef: ViewContainerRef,
        @Inject(ToasterService) private toasterService,
        @Inject(ViewContainerRef) viewContainerRef,
        private global: GlobalVariable,
        private router: Router,
        private toast: ToastServiceService
    ) {
        this.toasterService.setRootViewContainerRef(viewContainerRef)
    }

    ngOnInit(): void {
        this._notificationsService.notifications$.pipe(takeUntil(this._unsubscribeAll)).subscribe((notifications: any) => {
            let index = 0
            let typesToFilter = [];

            if (index === 3) {
                typesToFilter = ['job_confirm', 'job_signin'];
            } else if (index === 1) {
                typesToFilter = ['incident_report'];
            } else if (index === 0) {
                typesToFilter = ['green_call', 'welfare_call'];
            } else {
                typesToFilter = ['leave_location'];
            }
            this.unreadCount = notifications?.data.length
            this.notifications = notifications?.data?.filter((obj) => typesToFilter.includes(obj.type));
            this.notifications.forEach((notification) => {
                // notification.icon = this.getRandomIconName();
                notification.image = 'https://apis.thescouts.com.au/guard/' + notification?.profile_image;
            });
            this.calculateTabCounts(notifications?.data);
            this.getNotification()
            this._changeDetectorRef.markForCheck();
        });
        this.scheduleApiCall()
    }

    async scheduleApiCall() {
        setInterval(() => {
            this.getNotification()
        }, 35000);
        this._changeDetectorRef.markForCheck()
    }

    ngOnDestroy(): void {
        // Unsubscribe from all subscriptions
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
        this.notifications = []
        // Dispose the overlay
        if (this._overlayRef) {
            this._overlayRef.dispose();
        }
    }

    openPanel(): void {
        if (!this._notificationsPanel || !this._notificationsOrigin) {
            return;
        }
        if (!this._overlayRef) {
            this._createOverlay();
        }
        this._overlayRef.attach(new TemplatePortal(this._notificationsPanel, this._viewContainerRef));
    }

    closePanel(): void {
        this._overlayRef.detach();
    }

    markAllAsRead(): void {
        this._notificationsService.markAllAsRead().subscribe(({ success }) => {
            if (success) {
                this.getTabsData(this.selectedTabIndex)
                this.getNotification()
                this._changeDetectorRef.markForCheck();
            }

        });
    }

    toggleRead(notification: Notification): void {
        notification.read = !notification.read;
        this._notificationsService.update(notification.id).subscribe(({ success }) => {
            if (success) {
                this.getTabsData(this.selectedTabIndex)
                this._changeDetectorRef.markForCheck();

            }
        });
    }

    trackByFn(index: number, item: any): any {
        return item.id || index;
    }

    private _createOverlay(): void {
        // Create the overlay
        this._overlayRef = this._overlay.create({
            hasBackdrop: true,
            backdropClass: 'fuse-backdrop-on-mobile',
            scrollStrategy: this._overlay.scrollStrategies.block(),
            positionStrategy: this._overlay.position()
            .flexibleConnectedTo(this._notificationsOrigin._elementRef.nativeElement)
            .withLockedPosition(true)
            .withPush(true)
            .withPositions([
                {
                    originX: 'start',
                    originY: 'bottom',
                    overlayX: 'start',
                    overlayY: 'top'
                },
                {
                    originX: 'start',
                    originY: 'top',
                    overlayX: 'start',
                    overlayY: 'bottom'
                },
                {
                    originX: 'end',
                    originY: 'bottom',
                    overlayX: 'end',
                    overlayY: 'top'
                },
                {
                    originX: 'end',
                    originY: 'top',
                    overlayX: 'end',
                    overlayY: 'bottom'
                }
            ])
        });

        // Detach the overlay from the portal on backdrop click
        this._overlayRef.backdropClick().subscribe(() => {
            this._overlayRef.detach();
        });
    }

    getNotification() {
        this._notificationsService.notFication().subscribe(({ unseen, success, count }) => {
            this.unreadCount = count
            if (success && unseen) {
                this.getTabsData(this.selectedTabIndex)
                this.showToast(unseen)
                setTimeout(() => {
                    this.global.playBeepSound();
                }, 0);
            }
            this._changeDetectorRef.markForCheck()
        })
    }

    showToast(obj) {
        console.log(obj);
        this.toasterService.dispatchToaster(obj.message, obj.title);
        let toasterWaitFor$ = timer(10000);
        let toasterInterval$ = interval(1000).pipe(
            takeUntil(toasterWaitFor$)
        );

        if (obj?.type != 'incident_report') {
            let subscription = toasterInterval$.subscribe(
                res => console.log('Toaster wait for ', res),
                err => console.log('Toaster wait for ', err),
                () => {
                    this.toasterService.dismissToaster()
                }
            );
        }
    }

    tabClick(event) {
        this.selectedTabIndex = event.index;
        this.notifications = []
        this.getTabsData(this.selectedTabIndex)
    }

    getTabsData(index) {
        let typesToFilter = [];

        if (index === 3) {
            typesToFilter = ['job_confirm', 'job_signin'];
        } else if (index === 1) {
            typesToFilter = ['incident_report'];
        } else if (index === 0) {
            typesToFilter = ['green_call', 'welfare_call'];
        } else {
            typesToFilter = ['leave_location'];
        }

        this._notificationsService.getAll().subscribe(({ success, data }) => {
            if (success && data) {
                this.notifications = data?.filter((obj) => typesToFilter.includes(obj.type));
                this.notifications.forEach((notification) => {
                    // notification.icon = this.getRandomIconName();
                    notification.image = 'https://apis.thescouts.com.au/guard/' + notification?.profile_image;
                });
                this.calculateTabCounts(data)
            }
        }, (error => {
            this.toast.toastNotification('Something went wrong. Please contact with support team.', 'Request Incomplete!')
        }));
        this._changeDetectorRef.markForCheck()
    }


    navigate(notification) {
        if (notification.roster && notification.guard_id && notification.record_id) {
            this.router.navigate(['/job-roster', notification?.roster], {
                queryParams: {
                    staff_id: notification?.guard_id,
                    shift_id: notification?.record_id,
                },
            });
            this.closePanel()
        }
        else {
            this.toast.toastNotification1('ID not founds. Maybe someone else delete roster/shift/staff', 'Request Incomplete!')
        }
    }

    // Function to calculate the count for each tab
    calculateTabCounts(notifications) {
        const counts = {
            'Green & Welfare': 0,
            'Incident': 0,
            'Staff Leave': 0,
            'Jobs': 0,
        };
        notifications.forEach((notification) => {
            if (notification.status === 'unseen') {
                if (notification.type === 'green_call' || notification.type === 'welfare_call') {
                    counts['Green & Welfare']++;
                } else if (notification.type === 'incident_report') {
                    counts['Incident']++;
                } else if (notification.type === 'leave_location') {
                    counts['Staff Leave']++;
                } else {
                    counts['Jobs']++;
                }
            }
        });
        this.tabs.forEach((tab) => {
            tab.value = counts[tab.label];
        });
        this._changeDetectorRef.markForCheck();
    }
}

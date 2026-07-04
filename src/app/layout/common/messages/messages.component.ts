import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Inject, OnDestroy, OnInit, TemplateRef, ViewChild, ViewContainerRef, ViewEncapsulation } from '@angular/core';
import { Overlay, OverlayRef } from '@angular/cdk/overlay';
import { TemplatePortal } from '@angular/cdk/portal';
import { MatButton } from '@angular/material/button';
import { Subject, interval, takeUntil, timer } from 'rxjs';
import { Message } from 'app/layout/common/messages/messages.types';
import { MessagesService } from 'app/layout/common/messages/messages.service';
import { ToasterService } from 'app/modules/admin/models/toaster/toaster.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import Pusher from 'pusher-js';
import { Router } from '@angular/router';

@Component({
    selector: 'messages',
    templateUrl: './messages.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
    exportAs: 'messages'
})

export class MessagesComponent implements OnInit, OnDestroy {

    @ViewChild('messagesOrigin') private _messagesOrigin: MatButton;
    @ViewChild('messagesPanel') private _messagesPanel: TemplateRef<any>;
    messages: Message[];
    unreadCount: number = 0;
    private _overlayRef: OverlayRef;
    private _unsubscribeAll: Subject<any> = new Subject<any>();
    newData: any = {};

    constructor(
        private _changeDetectorRef: ChangeDetectorRef,
        private _messagesService: MessagesService,
        private _overlay: Overlay,
        private _viewContainerRef: ViewContainerRef,
        @Inject(ToasterService) private toasterService,
        private toast: ToastServiceService,
        private global: GlobalVariable,
        private router: Router
    ) {}

    ngOnInit(): void {
        this.initializePusher();
        this.adminPusher()
        this._messagesService.messages$.pipe(takeUntil(this._unsubscribeAll)).subscribe((messages: any) => {
            if (messages.success && messages.data) {
                messages?.data.forEach((notification) => {
                    // notification.icon = this.getRandomIconName();
                    notification.image = 'https://apis.thescouts.com.au/guard/' + notification?.profile_image;
                });
            }
            this.messages = messages.data;
            this._calculateUnreadCount();
            this._changeDetectorRef.markForCheck();
        });
    }

    initializePusher(): void {
        const pusher = new Pusher('a1a91e8e3217abe50dee', {
            cluster: 'ap2',
        });

        const channel = pusher.subscribe('chat-channel');
        channel.bind('chat-channel', (data) => {
            if (this.global.admin.admin_id == data.admin_id && data.send_by != this.global.admin.chat_type) {
                console.log(data);
                this.handlePusherData(data);
            }
        });
    }

    adminPusher(): void {
        const pusher = new Pusher('a1a91e8e3217abe50dee', {
            cluster: 'ap2',
        });

        const channel = pusher.subscribe('admin-chat-channel');
        channel.bind('admin-chat-channel', (data) => {
            console.log(data);
            if (data?.receiver_id == this.global.admin.admin_id) {
                this.handlePusherData(data);
            }
        });
    }

    handlePusherData(data: any): void {
        this.newData.admin_id = this.global.admin.admin_id;
        if (data.contractor_id) {
            this.newData.contractor_id = data.contractor_id;
        } else if (data.customer_id) {
            this.newData.customer_id = data.customer_id;
        } else if (data.guard_id) {
            this.newData.staff_id = data.guard_id;
        }
        let obj = {
            title: 'New Message!',
            message: data.send_by ? `${data.send_by} send you a message.` : 'Some one send you a message.'
        }
        this._messagesService.getAll().subscribe()
        this.showToast(obj)
    }

    getNotification() {
        this._messagesService.notFication().subscribe(({ unseen, success, count }) => {
            this.unreadCount = count
            if (success && unseen) {
                this.showToast(unseen)
                setTimeout(() => {
                    this.global.playBeepSound();
                }, 0);
            }
            this._changeDetectorRef.markForCheck()
        })
    }


    ngOnDestroy(): void {
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
        if (this._overlayRef) {
            this._overlayRef.dispose();
        }
    }

    openPanel(): void {
        if (!this._messagesPanel || !this._messagesOrigin) {
            return;
        }

        if (!this._overlayRef) {
            this._createOverlay();
        }

        this._overlayRef.attach(new TemplatePortal(this._messagesPanel, this._viewContainerRef));
    }

    closePanel(): void {
        this._overlayRef.detach();
    }

    markAllAsRead(): void {
        this._messagesService.markAllAsRead().subscribe(({ success }) => {
            if (success) {
                this._messagesService.getAll().subscribe()
                this._changeDetectorRef.markForCheck();
            }
        });
    }

    toggleRead(message: Message): void {
        message.read = !message.read;
        this._messagesService.update(message.id).subscribe(({ success }) => {
            if (success) {
                this._messagesService.getAll().subscribe()
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
                .flexibleConnectedTo(this._messagesOrigin._elementRef.nativeElement)
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


    private _calculateUnreadCount(): void {
        let count = 0;
        if (this.messages && this.messages.length) {
            count = this.messages.filter(message => !message.read).length;
        }
        this.unreadCount = count;
    }

    showToast(obj) {

        this.toasterService.dispatchToaster(obj?.message, obj?.title);
        let toasterWaitFor$ = timer(10000);
        let toasterInterval$ = interval(1000).pipe(
            takeUntil(toasterWaitFor$)
        );
        let subscription = toasterInterval$.subscribe(
            res => console.log('Toaster wait for ', res),
            err => console.log('Toaster wait for ', err),
            () => {
                this.toasterService.dismissToaster()
            }
        );
    }

    navigate(message) {
        if (message.chat_between) {
            let user
            let name = message.send_by_name
            if (message.contractor_id) {
                user = message.contractor_id;
            } else if (message.customer_id) {
                user = message.customer_id;
            } else if (message.guard_id) {
                user = message.guard_id;
            }
            else if (message.record_id) {
                user = message.admins_id;
            }
            this.router.navigate(['/chat-app', message.chat_between], {
                queryParams: {
                    user: user,
                    type: message.chat_between,
                    name: name
                },
            });
            this.closePanel()
        }
        else {
            this.toast.toastNotification1('ID not founds. Maybe someone else delete record', 'Request Incomplete!')
        }
    }

    handleBrokenImage(event: Event) {
        const imgElement = event.target as HTMLImageElement;
        imgElement.src = '../../../../assets/images/logo/scou_1.png';
    }

}

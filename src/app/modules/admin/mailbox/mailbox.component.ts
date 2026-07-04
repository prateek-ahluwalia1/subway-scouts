import { Component, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { MatDrawer } from '@angular/material/sidenav';
import { Subject, Subscription } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { FuseMediaWatcherService } from '@fuse/services/media-watcher';
import { GraphService } from 'app/services/graph.service';
import { PusherService } from 'app/services/pusher.service';
import { MailboxService } from './mailbox.service';
import { Router } from '@angular/router';

@Component({
    selector: 'mailbox',
    templateUrl: './mailbox.component.html',
    encapsulation: ViewEncapsulation.None
})
export class MailboxComponent implements OnInit, OnDestroy {
    @ViewChild('drawer') drawer: MatDrawer;

    drawerMode: 'over' | 'side' = 'side';
    drawerOpened: boolean = true;
    private _unsubscribeAll: Subject<any> = new Subject<any>();
    private notificationSubscription: Subscription;
    private previousNotification: any = null;

    constructor(
        private _fuseMediaWatcherService: FuseMediaWatcherService,
        private _graphService: GraphService,
        private _pusherService: PusherService,
        private _mailboxService: MailboxService,
        private _router: Router
    ) { }

    ngOnInit(): void {
        this._fuseMediaWatcherService.onMediaChange$
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe(({ matchingAliases }) => {
                this.drawerMode = matchingAliases.includes('md') ? 'side' : 'over';
                this.drawerOpened = matchingAliases.includes('md');
            });

        this._graphService.createSubscription().subscribe();

        this.notificationSubscription = this._pusherService.notification$.subscribe(data => {
            this.handleNewData(data);
        });

    }

    ngOnDestroy(): void {
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
        if (this.notificationSubscription) {
            this.notificationSubscription.unsubscribe();
        }
    }

    handleNewData(newData: any): void {
        if (newData && newData.value && Array.isArray(newData.value)) {
            newData.value.forEach(notification => {
                if (notification.resourceData && notification.resourceData.id) {
                    if (!this.isDuplicateNotification(notification)) {
                        this.previousNotification = notification;
                        this.checkAndFetchMails();
                    } else {
                        console.log('Duplicate notification detected, skipping API call.');
                    }
                } else {
                    console.error('Invalid notification structure:', notification);
                }
            });
        } else {
            console.error('Invalid data structure:', newData);
        }
    }

    checkAndFetchMails(): void {
        this._mailboxService.getFolders().subscribe()
        if (this._router.url.includes('/mailbox/inbox')) {
            this._mailboxService.getMailsByFolder('inbox', '1').subscribe()
        }
    }

    isDuplicateNotification(newData: any): boolean {
        return this.previousNotification && this.previousNotification.resourceData.id === newData.resourceData.id;
    }
}

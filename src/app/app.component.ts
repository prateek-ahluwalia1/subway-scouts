import { Component, Inject, OnInit, OnDestroy } from '@angular/core';
import AircallPhone from 'aircall-everywhere';
import { GlobalVariable } from './shared/global';
import { Router } from '@angular/router';
import Pusher from 'pusher-js';
import { ToasterService } from './modules/admin/models/toaster/toaster.service';
import { Subscription, interval, takeUntil, timer } from 'rxjs';
import { DateAdapter } from '@angular/material/core';
import { MailboxService } from './modules/admin/mailbox/mailbox.service';
import { MsalService } from '@azure/msal-angular';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit, OnDestroy {
    private aircallPhone: AircallPhone;
    shouldDisplayMobile: boolean = true;

    subdomain: string;
    isAuthenticated: boolean;
    private notificationSubscription: Subscription;
    private previousNotification: any = null;

    constructor(
        private global: GlobalVariable,
        public dateAdapter: DateAdapter<Date>,
        private router: Router,
        @Inject(ToasterService) private toasterService,
        private mailboxService: MailboxService,
        private msalService: MsalService
    ) {
        this.dateAdapter.setLocale('en-AU');
        this.subdomain = this.extractSubdomain(window.location.hostname);
    }

    extractSubdomain(hostname: string): string {
        const parts = hostname.split('.');
        return parts[0];
    }

    shouldDisplayPhoneComponent(): boolean {
        const currentRoute = this.router.url;
        const excludedRoutes = ['sign-in', 'sign-up', 'forgot-password', 'reset-password'];
        return !excludedRoutes.some(route => currentRoute.includes(route));
    }

    ngOnInit(): void {
        this.loadPhone();
        this.initializePusher();
    }

    ngOnDestroy(): void {
        if (this.notificationSubscription) {
            this.notificationSubscription.unsubscribe();
        }
    }

    loadPhone(): void {
        this.aircallPhone = new AircallPhone({
            domToLoadPhone: '#phone',
            onLogin: (settings) => { },
            onLogout: () => { }
        });

        this.aircallPhone.on('incoming_call', (callInfos) => {
            console.log(`Call from ${callInfos.from} to ${callInfos.to}`);
            this.shouldDisplayMobile = false;
        });

        this.aircallPhone.on('call_ended', (callInfos) => {
            this.shouldDisplayMobile = true;
        });

        this.aircallPhone.on('call_end_ringtone', (callInfos) => {
            console.log(`Call from ${callInfos}`);
            this.shouldDisplayMobile = true;
        });
    }

    initializePusher(): void {
        const pusher = new Pusher('a1a91e8e3217abe50dee', {
            cluster: 'ap2',
        });

        const channel = pusher.subscribe('ticket-management');
        channel.bind('ticket-management', (pusherData) => {
            if (this.global.admin.is_received_support == 1) {
                const toastData = {
                    title: "Support Ticket",
                    message: "You have a new ticket"
                };
                this.showToast(toastData);
            }
        });

        const crm = pusher.subscribe('crm-notification');
        crm.bind('crm-notification', (pusherData) => {
            if (pusherData.saleperson_id == this.global.admin.admin_id) {
                const toastData = {
                    title: "CRM Notification",
                    message: pusherData.admin_name + " assigned you a lead."
                };
                this.showToast(toastData);
            }
        });

        const mail = pusher.subscribe('new-mail-notification');
        mail.bind('new-mail-notification', (mail) => {
            console.log(mail);
            this.isAuthenticated = this.msalService.instance.getAllAccounts().length > 0;
            if (this.isAuthenticated) {
                this.handleNewData(mail.message);
            }
        });
    }

    handleNewData(newData: any): void {
        if (newData && newData.value && Array.isArray(newData.value)) {
            newData.value.forEach(notification => {
                if (notification.changeType === 'created' && notification.resourceData && notification.resourceData.id) {
                    if (!this.isDuplicateNotification(notification)) {
                        this.previousNotification = notification;
                        this.fetchAndShowMail(notification.resourceData.id);
                    } else {
                    }
                } else {
                }
            });
        } else {
            if (newData.title && newData.message) {
                this.showToast(newData);
            } else {
                console.error('Invalid data structure:', newData);
            }
        }
    }

    isDuplicateNotification(newData: any): boolean {
        return this.previousNotification && this.previousNotification.resourceData.id === newData.resourceData.id;
    }

    fetchAndShowMail(mailId: string): void {
        this.mailboxService.getMailById(mailId).subscribe(mail => {
            if (mail) {
                const mailBody = this.extractTextFromHTML(mail.body.content);
                const truncatedBody = mailBody.length > 100 ? mailBody.substring(0, 100) + '...' : mailBody;
                this.showToast({ title: mail.subject, message: truncatedBody });
            }
        });
    }

    extractTextFromHTML(html: string): string {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        return tempDiv.textContent || tempDiv.innerText || '';
    }

    showToast(obj: { title: string, message: string }): void {
        this.toasterService.dispatchToaster(obj.message, obj.title);

        const toasterWaitFor$ = timer(10000);
        const toasterInterval$ = interval(1000).pipe(
            takeUntil(toasterWaitFor$)
        );

        toasterInterval$.subscribe(
            res => console.log('Toaster wait for ', res),
            err => console.log('Toaster wait for ', err),
            () => {
                this.toasterService.dismissToaster();
            }
        );
    }
}

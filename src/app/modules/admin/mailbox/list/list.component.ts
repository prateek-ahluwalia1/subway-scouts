import { Component, ElementRef, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { Subject } from 'rxjs';
import { takeUntil, distinctUntilChanged, map } from 'rxjs/operators';
import { MailboxService } from 'app/modules/admin/mailbox/mailbox.service';
import { MailboxComponent } from 'app/modules/admin/mailbox/mailbox.component';
import { Mail, MailCategory } from 'app/modules/admin/mailbox/mailbox.types';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
    selector: 'mailbox-list',
    templateUrl: './list.component.html',
    encapsulation: ViewEncapsulation.None
})
export class MailboxListComponent implements OnInit, OnDestroy {
    @ViewChild('mailList') mailList: ElementRef;

    mails: Mail[];
    mailsLoading: boolean = false;
    pagination: any;
    selectedMail: Mail;
    currentPage: number = 1;
    private _unsubscribeAll: Subject<any> = new Subject<any>();
    params: any;

    // Scroll position variable
    private _scrollPosition = 0;

    constructor(
        public mailboxComponent: MailboxComponent,
        private _mailboxService: MailboxService,
        private _activatedRoute: ActivatedRoute,
        private _router: Router) { }

    ngOnInit(): void {
        this._activatedRoute.params.pipe(
            map(params => (
                { folder: params['folder'], page: params['page'] })),
            distinctUntilChanged((prev, curr) => prev.folder === curr.folder && prev.page === curr.page),
            takeUntil(this._unsubscribeAll)
        ).subscribe(params => {
            if (params.folder) {
                this.params = params.folder;
                const page = isNaN(params.page) ? 1 : parseInt(params.page, 10);
                this.currentPage = page;
                this._mailboxService.setFolder(params.folder);
                this._mailboxService.getMailsByFolder(params.folder, page.toString()).subscribe();
            }
        });

        this._mailboxService.mails$
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe((mails: Mail[]) => {
                this.mails = mails;
            });

        this._mailboxService.mailsLoading$
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe((mailsLoading: boolean) => {
                this.mailsLoading = mailsLoading;
                if (this.mailList && !mailsLoading) {
                    this.restoreScrollPosition();
                }
            });

        this._mailboxService.pagination$
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe((pagination) => {
                this.pagination = pagination;
            });

        this._mailboxService.mail$
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe((mail: Mail) => {
                this.selectedMail = mail;
            });
    }

    ngOnDestroy(): void {
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
    }

    onMailSelected(mail: Mail): void {
        if (!mail.isRead) {
            this._mailboxService.updateMail(mail.id, { isRead: true }).subscribe();
        }
        this._mailboxService.selectedMailChanged.next(mail);
        localStorage.setItem('folder', this.params);
        this._scrollPosition = this.mailList.nativeElement.scrollTop;
    }

    trackByFn(index: number, item: any): any {
        return item.id || index;
    }

    navigateToPage(page: number): void {
        if (page) {
            this._router.navigate(['../', page], { relativeTo: this._activatedRoute });
            this._scrollPosition = 0
        }
    }

    // Restore the scroll position
    private restoreScrollPosition(): void {
        if (this.mailList && this._scrollPosition) {
            setTimeout(() => {
                this.mailList.nativeElement.scrollTop = this._scrollPosition;
            });
        }
    }
}

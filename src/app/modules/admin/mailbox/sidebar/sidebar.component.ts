import { Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { FuseNavigationItem, FuseNavigationService, FuseVerticalNavigationComponent } from '@fuse/components/navigation';
import { MailboxService } from 'app/modules/admin/mailbox/mailbox.service';
import { MailboxComposeComponent } from 'app/modules/admin/mailbox/compose/compose.component';
import { MailFilter, MailFolder, MailLabel } from 'app/modules/admin/mailbox/mailbox.types';

@Component({
    selector: 'mailbox-sidebar',
    templateUrl: './sidebar.component.html',
    styleUrls: ['./sidebar.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class MailboxSidebarComponent implements OnInit, OnDestroy {
    filters: MailFilter[];
    folders: MailFolder[];
    labels: MailLabel[];
    menuData: FuseNavigationItem[] = [];
    private _foldersMenuData: FuseNavigationItem[] = [];
    private _otherMenuData: FuseNavigationItem[] = [];
    private _unsubscribeAll: Subject<any> = new Subject<any>();

    // List of folder names to hide
    private _hiddenFolders: string[] = ['Sync Issues', 'Outbox', 'Conversation History'];

    constructor(
        private _mailboxService: MailboxService,
        private _matDialog: MatDialog,
        private _fuseNavigationService: FuseNavigationService,
    ) { }

    ngOnInit(): void {
        this._mailboxService.folders$
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe((folders: MailFolder[]) => {
                this.folders = folders;
                this._generateFoldersMenuLinks();
                this._updateNavigationBadge(folders);
            });
    }

    ngOnDestroy(): void {
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
    }

    openComposeDialog(): void {
        const dialogRef = this._matDialog.open(MailboxComposeComponent, {
            data: { email: '', readonly: false }
        });
        dialogRef.afterClosed().subscribe((result) => {
            console.log('Compose dialog was closed!', result);
        });
    }

    private _generateFoldersMenuLinks(): void {
        this._foldersMenuData = [];
        const predefinedOrder = ['Inbox', 'Sent Items', 'Drafts', 'Outbox', 'Archive', 'Junk Email', 'Deleted Items'];

        // Create a map of folder names to folder objects for easy lookup
        const folderMap = new Map(this.folders.map(folder => [folder.displayName, folder]));

        // Iterate over the predefined order to ensure all relevant folders are handled
        predefinedOrder.forEach(folderName => {
            const folder = folderMap.get(folderName) || {
                displayName: folderName,
                id: '',
                unreadItemCount: 0,
                totalItemCount: 0,
                isHidden: false,
                parentFolderId: '',
                sizeInBytes: 0,
                childFolderCount: 0
            };

            // Check if the folder should be hidden
            if (this._hiddenFolders.includes(folder.displayName)) {
                return;
            }

            const menuItem: FuseNavigationItem = {
                id: folder.id,
                title: folder.displayName,
                type: 'basic',
                link: '/mailbox/' + this.createSlug(folder.displayName),
                icon: this.getIconClass(folder.displayName),
                permission: true,
            };
            if (folder.unreadItemCount && folder.unreadItemCount > 0) {
                menuItem['badge'] = {
                    title: folder.unreadItemCount + ''
                };
            }
            this._foldersMenuData.push(menuItem);
        });

        this.folders.forEach(folder => {
            if (!predefinedOrder.includes(folder.displayName) && !this._hiddenFolders.includes(folder.displayName)) {
                const menuItem: FuseNavigationItem = {
                    id: folder.id,
                    title: folder.displayName || folder.title,
                    type: 'basic',
                    link: '/mailbox/' + this.createSlug(folder.displayName),
                    icon: this.getIconClass(folder.displayName),
                    permission: true,
                };
                if (folder.unreadItemCount && folder.unreadItemCount > 0) {
                    menuItem['badge'] = {
                        title: folder.unreadItemCount + ''
                    };
                }
                this._foldersMenuData.push(menuItem);
            }
        });

        this._updateMenuData();
    }

    private _updateMenuData(): void {
        this.menuData = [
            {
                title: 'MAILBOXES',
                type: 'group',
                children: [
                    ...this._foldersMenuData
                ]
            },
            {
                type: 'spacer'
            },
            ...this._otherMenuData
        ];
    }

    private _updateNavigationBadge(folders: MailFolder[]): void {
        const inboxFolder = this.folders.find(folder => folder.displayName?.toLowerCase() === 'inbox');
        const mainNavigationComponent = this._fuseNavigationService.getComponent<FuseVerticalNavigationComponent>('mainNavigation');

        if (mainNavigationComponent) {
            const mainNavigation = mainNavigationComponent.navigation;
            const menuItem = this._fuseNavigationService.getItem('apps.mailbox', mainNavigation);

            if (menuItem && inboxFolder) {
                menuItem.badge.title = inboxFolder.unreadItemCount + '';
            }

            mainNavigationComponent.refresh();
        }
    }

    createSlug(displayName: string): string {
        return displayName
            .toLowerCase()
            .replace(/ /g, '')
            .replace(/[^\w-]+/g, '');
    }

    getIconClass(value: string): string {
        switch (value) {
            case 'Inbox': return 'heroicons_outline:inbox';
            case 'Sent Items': return 'send';
            case 'Drafts': return 'heroicons_outline:document';
            case 'Archive': return 'heroicons_outline:archive';
            case 'Junk Email': return 'heroicons_outline:exclamation-circle';
            case 'Deleted Items': return 'heroicons_outline:trash';
            default: return 'heroicons_outline:mail';
        }
    }
}

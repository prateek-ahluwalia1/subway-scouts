import { AfterViewInit, ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { MatDrawer } from '@angular/material/sidenav';
import { Subject, takeUntil } from 'rxjs';
import { FuseMediaWatcherService } from '@fuse/services/media-watcher';
import { GlobalVariable } from 'app/shared/global';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
export type Menu = {
    name: string,
    iconClass: string,
    active: boolean,
    submenu: { name: string }[]
}
export type Config = {
    // selector?: String,
    multi?: boolean
};
@Component({
    selector: 'settings',
    templateUrl: './settings.component.html',
    styleUrls: ['./settings.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class SettingsComponent implements OnInit, OnDestroy {
    @ViewChild('drawer') drawer: MatDrawer;
    drawerMode: 'over' | 'side' = 'side';
    drawerOpened: boolean = true;
    panels: any[] = [];
    selectedPanel: string = 'Open';
    private _unsubscribeAll: Subject<any> = new Subject<any>();
    @Input() options;
    menus: Menu[] = [];

    config: Config;
    receivedData: string;
    chatObj;


    constructor(
        private _changeDetectorRef: ChangeDetectorRef,
        private _fuseMediaWatcherService: FuseMediaWatcherService,
        public global: GlobalVariable, private trackAdmin: TrackAdminActivityService
    ) {
        // if (this.global.admin?.admin_user_type === 'guard') {
        this.menus.push(
            {
                name: 'Tickets',
                iconClass: 'fa fa-font-awesome',
                active: true,
                submenu: [
                    { name: 'Open' },
                    { name: 'Answered' },
                    { name: 'Closed' }
                ]
            },
            {
                name: 'Support',
                iconClass: 'fa fa-globe',
                active: false,
                submenu: [
                    // { name: 'My support Tickets' },
                    { name: 'Open Ticket' },
                ]
            }
        );
        // }
        //  else {
        //     // Add objects for other users
        //     this.menus.push(
        //         {
        //             name: 'Tickets',
        //             iconClass: 'fa fa-font-awesome',
        //             active: true,
        //             submenu: [
        //                 { name: 'Open' },
        //                 { name: 'Answered' },
        //                 { name: 'Closed' }
        //             ]
        //         },
        //     );
        // }
    }


    ngOnInit(): void {
        this.config = this.mergeConfig(this.options);
        // Subscribe to media changes
        this._fuseMediaWatcherService.onMediaChange$
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe(({ matchingAliases }) => {

                // Set the drawerMode and drawerOpened
                if (matchingAliases.includes('lg')) {
                    this.drawerMode = 'side';
                    this.drawerOpened = true;
                }
                else {
                    this.drawerMode = 'over';
                    this.drawerOpened = false;
                }

                if (!localStorage.getItem('routerId')) {
                    this.activity()
                }
                // Mark for check
                this._changeDetectorRef.markForCheck();
            });
    }

    ngOnDestroy(): void {
        // Unsubscribe from all subscriptions
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
        this.trackAdmin.storeActivity('Support', 'Exit Support Page', localStorage.getItem('routerId')).subscribe(res => {
            if (res.success) {
                localStorage.removeItem('routerId');
            }
        })
    }


    receiveDataFromChild(data: string) {
        console.log(data);

        this.receivedData = data;
        this.selectedPanel = 'team';
        this._changeDetectorRef.markForCheck()
    }

    goToPanel(panel: string): void {
        this.selectedPanel = panel;
        // Close the drawer on 'over' mode
        if (this.drawerMode === 'over') {
            this.drawer.close();
        }
        this._changeDetectorRef.markForCheck()
    }

    getPanelInfo(id: string): any {
        return this.panels.find(panel => panel.id === id);
    }

    trackByFn(index: number, item: any): any {
        return item.id || index;
    }

    mergeConfig(options: Config) {
        const config = {
            multi: true
        };

        return { ...config, ...options };
    }

    toggle(index: number) {
        if (!this.config.multi) {
            this.menus
                .filter((menu, i) => i !== index && menu.active)
                .forEach(menu => (menu.active = !menu.active));
        }

        this.menus[index].active = !this.menus[index].active;
    }

    receiveDataFromSecurity(data) {
        this.chatObj = data
        this.selectedPanel = 'notifications';
        this._changeDetectorRef.markForCheck();
    }

    activity() {
        this.trackAdmin.storeActivity('Support', `Enter in Support Page`).subscribe(({ success, id }) => {
            if (success) {
                localStorage.setItem('routerId', id)
            }
        })
    }

}

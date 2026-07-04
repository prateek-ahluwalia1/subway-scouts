import { Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { Router } from '@angular/router';
import { Subject, takeUntil } from 'rxjs';
import { FuseMediaWatcherService } from '@fuse/services/media-watcher';
import { FuseNavigationService, FuseVerticalNavigationComponent } from '@fuse/components/navigation';
import { Navigation } from 'app/core/navigation/navigation.types';
import { NavigationService } from 'app/core/navigation/navigation.service';
import { GlobalVariable } from 'app/shared/global';
import { PermissionsService } from 'app/services/permissions.service';
import { ServiceService } from 'app/services/service.service';

@Component({
    selector: 'compact-layout',
    templateUrl: './compact.component.html',
    encapsulation: ViewEncapsulation.None
})

export class CompactLayoutComponent implements OnInit, OnDestroy {
    isScreenSmall: boolean;
    navigation: Navigation;
    private _unsubscribeAll: Subject<any> = new Subject<any>();
    lead
    task
    quotation
    businessName
    logo
    allLeads

    constructor(
        private _router: Router,
        private _navigationService: NavigationService,
        private _fuseMediaWatcherService: FuseMediaWatcherService,
        private _fuseNavigationService: FuseNavigationService, private services: ServiceService,
        public global: GlobalVariable, private permissionService: PermissionsService
    ) {
        const activatedUrl = this._router.url;
        this.global.ActivateUrl = activatedUrl
        const logo = JSON.parse(localStorage.getItem('admin'))
        this.logo = logo?.apiKeys?.logo
        this.global.selectTabCrm = localStorage.getItem('selectedTab') ?? 'dashboard'
    }

    activeTab(type) {
        localStorage.setItem('selectedTab', type)
        this.global.selectTabCrm = type;
    }

    get currentYear(): number {
        return new Date().getFullYear();
    }

    ngOnInit(): void {
        this.services.leadsCount$.pipe(takeUntil(this._unsubscribeAll))
            .subscribe((res: any) => {
                if (res && res.success) {
                    this.allLeads = res.data
                }
            });
        const per = this.permissionService.getPermissionsByTitle('CRM');
        this.task = per?.childPage?.find(item => item.title === 'Tasks');
        this.quotation = per?.childPage?.find(item => item.title === 'Financials');
        this.lead = per?.childPage?.find(item => item.title === 'Leads');

        this._navigationService.navigation$
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe((navigation: Navigation) => {
                this.navigation = navigation;
            });

        this._fuseMediaWatcherService.onMediaChange$.pipe(takeUntil(this._unsubscribeAll))
            .subscribe(({ matchingAliases }) => {
                this.isScreenSmall = !matchingAliases.includes('md');
            });
    }

    ngOnDestroy(): void {
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
        localStorage.removeItem('selectedTab')
    }

    toggleNavigation(name: string): void {
        const navigation = this._fuseNavigationService.getComponent<FuseVerticalNavigationComponent>(name);
        if (navigation) {
            navigation.toggle();
        }
    }

    handleImageError() {
        this.logo = 'assets/images/logo/scouts.png';
    }

}

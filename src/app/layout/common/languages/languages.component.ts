import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { Subject, take, takeUntil } from 'rxjs';
import { AvailableLangs, TranslocoService } from '@ngneat/transloco';
import { FuseNavigationService, FuseVerticalNavigationComponent } from '@fuse/components/navigation';
import { Router } from '@angular/router';
import { AuthService } from 'app/core/auth/auth.service';
import { GlobalVariable } from 'app/shared/global';
import { ServiceService } from 'app/services/service.service';
import { HttpClient } from '@angular/common/http';
@Component({
    selector: 'languages',
    templateUrl: './languages.component.html',
    styleUrls: ['./languages.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
    exportAs: 'languages'
})

export class LanguagesComponent implements OnInit, OnDestroy {

    availableLangs: AvailableLangs;
    activeLang: string;
    flagCodes: any;
    userInfo: any
    avatar = false
    unreadNotes
    private _unsubscribeAll: Subject<any> = new Subject<any>();
    ticketCount: any;

    constructor(
        private _changeDetectorRef: ChangeDetectorRef,
        private _fuseNavigationService: FuseNavigationService,
        private _translocoService: TranslocoService,
        private _router: Router,
        public userData: AuthService,
        public global: GlobalVariable,
        private services: ServiceService,
    ) {
        this.userInfo = this.userData._user
        if (this.userData._user.avatar) {
            this.avatar = true
        }
    }

    ngOnInit(): void {
        this.services.todos$.pipe(takeUntil(this._unsubscribeAll)).subscribe((res: any) => {
            if (res && res.mark) {
                this.global.unreadNotes = res.mark.length
            }
            this._changeDetectorRef.markForCheck();
        });
        // Get the available languages from transloco
        this.availableLangs = this._translocoService.getAvailableLangs();

        // Subscribe to language changes
        this._translocoService.langChanges$.subscribe((activeLang) => {

            // Get the active lang
            this.activeLang = activeLang;

            // Update the navigation
            this._updateNavigation(activeLang);
        });

        this.ticketCount = this.global.admin.ticket_count
        // Set the country iso codes for languages for flags
        this.flagCodes = {
            'en': 'us',
            'tr': 'tr'
        };
    }

    ngOnDestroy(): void {
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
    }

    /**
     * Set the active lang
     *
     * @param lang
     */
    setActiveLang(lang: string): void {
        // Set the active lang
        this._translocoService.setActiveLang(lang);
    }

    /**
     * Track by function for ngFor loops
     *
     * @param index
     * @param item
     */
    trackByFn(index: number, item: any): any {
        return item.id || index;
    }

    /**
     * Update the navigation
     *
     * @param lang
     * @private
     */
    private _updateNavigation(lang: string): void {
        // For the demonstration purposes, we will only update the Dashboard names
        // from the navigation but you can do a full swap and change the entire
        // navigation data.
        //
        // You can import the data from a file or request it from your backend,
        // it's up to you.

        // Get the component -> navigation data -> item
        const navComponent = this._fuseNavigationService.getComponent<FuseVerticalNavigationComponent>('mainNavigation');

        // Return if the navigation component does not exist
        if (!navComponent) {
            return null;
        }

        // Get the flat navigation data
        const navigation = navComponent.navigation;

        // Get the Project dashboard item and update its title
        const projectDashboardItem = this._fuseNavigationService.getItem('dashboards.project', navigation);
        if (projectDashboardItem) {
            this._translocoService.selectTranslate('Project').pipe(take(1)).subscribe((translation) => {
                // Set the title
                projectDashboardItem.title = translation;

                // Refresh the navigation component
                navComponent.refresh();
            });
        }

        // Get the Analytics dashboard item and update its title
        const analyticsDashboardItem = this._fuseNavigationService.getItem('dashboards.analytics', navigation);
        if (analyticsDashboardItem) {
            this._translocoService.selectTranslate('Analytics').pipe(take(1)).subscribe((translation) => {
                // Set the title
                analyticsDashboardItem.title = translation;

                // Refresh the navigation component
                navComponent.refresh();
            });
        }
    }

    signOut(): void {
        this._router.navigate(['/sign-out'])
    }
    
    getInitials(name: string): string {
        return name.split(' ').map(n => n[0]).join('').toUpperCase();
    }

    adminProfile() {
        this._router.navigate(['/profile'], { queryParams: { id: this.userInfo.id } });
    }

    navigateToTasks() {
        this._router.navigate(['/tasks']);
    }

    support() {
        this._router.navigate(['/support']);
    }
}

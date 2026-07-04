import { GlobalVariable } from 'app/shared/global';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy, OnInit } from '@angular/core';
import { IsActiveMatchOptions } from '@angular/router';
import { Subject, takeUntil } from 'rxjs';
import { FuseVerticalNavigationComponent } from '@fuse/components/navigation/vertical/vertical.component';
import { FuseNavigationService } from '@fuse/components/navigation/navigation.service';
import { FuseNavigationItem } from '@fuse/components/navigation/navigation.types';
import { FuseUtilsService } from '@fuse/services/utils/utils.service';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
    selector: 'fuse-vertical-navigation-basic-item',
    templateUrl: './basic.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class FuseVerticalNavigationBasicItemComponent implements OnInit, OnDestroy {
    @Input() item: FuseNavigationItem;
    @Input() name: string;
    userRole
    isActiveMatchOptions: IsActiveMatchOptions;
    private _fuseVerticalNavigationComponent: FuseVerticalNavigationComponent;
    private _unsubscribeAll: Subject<any> = new Subject<any>();

    /**
     * Constructor
     */
    constructor(
        private _changeDetectorRef: ChangeDetectorRef,
        private _fuseNavigationService: FuseNavigationService,
        private _fuseUtilsService: FuseUtilsService,
        public globals: GlobalVariable,
        private toast: ToastServiceService
    ) {

        // Set the equivalent of {exact: false} as default for active match options.
        // We are not assigning the item.isActiveMatchOptions directly to the
        // [routerLinkActiveOptions] because if it's "undefined" initially, the router
        // will throw an error and stop working.
        this.isActiveMatchOptions = this._fuseUtilsService.subsetMatchOptions;
        const userRole = JSON.parse(localStorage.getItem('admin'));
        this.userRole = userRole && userRole.admin_user_type ? userRole.admin_user_type : '';
        
    }

    // -----------------------------------------------------------------------------------------------------
    // @ Lifecycle hooks
    // -----------------------------------------------------------------------------------------------------

    /**
     * On init
     */
    ngOnInit(): void {
        // Set the "isActiveMatchOptions" either from item's
        // "isActiveMatchOptions" or the equivalent form of
        // item's "exactMatch" option
        this.isActiveMatchOptions =
            this.item.isActiveMatchOptions ?? this.item.exactMatch
                ? this._fuseUtilsService.exactMatchOptions
                : this._fuseUtilsService.subsetMatchOptions;

        // Get the parent navigation component
        this._fuseVerticalNavigationComponent = this._fuseNavigationService.getComponent(this.name);


        // Mark for check
        this._changeDetectorRef.markForCheck();

        // Subscribe to onRefreshed on the navigation component
        this._fuseVerticalNavigationComponent.onRefreshed.pipe(
            takeUntil(this._unsubscribeAll)
        ).subscribe(() => {

            // Mark for check
            this._changeDetectorRef.markForCheck();
        });
        const shouldRender = this.shouldRenderItem(this.item);


    }

    /**
     * On destroy
     */
    ngOnDestroy(): void {
        // Unsubscribe from all subscriptions
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
    }



    /**
     * Check if an item should be rendered based on permissions.
     */
    shouldRenderItem(item: FuseNavigationItem) {

        const mainPermission = JSON.parse(localStorage.getItem('role_permissions'));
        const permissions = mainPermission?.permissions ? JSON.parse(mainPermission.permissions) : null;
        if (!permissions) {
            // this.toast.toastNotification1('We have not found permission for your role','Permissions Not Found!')
            // item.permission = false;
            return false; // No permissions available, do not render the item.

        }
        // Find the permission object for the current item by its title
        const itemPermission = permissions?.find(perm => perm.title === item.title);


        if (itemPermission && itemPermission.parentEnabled === true) {
            // If the item has permission to be rendered, you can add elements from itemPermission to the item
            item.permission = itemPermission.parentEnabled;
        }
    }

}

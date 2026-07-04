import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { MatDrawer } from '@angular/material/sidenav';
import { Subject, takeUntil } from 'rxjs';
import { FuseMediaWatcherService } from '@fuse/services/media-watcher';
import { FileManagerService } from 'app/modules/admin/file-manager/file-manager.service';
import { Item, Items } from 'app/modules/admin/file-manager/file-manager.types';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
    selector: 'file-manager-list',
    templateUrl: './list.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
    styles: [`  .input[type="text"] {
        display: block;
        color: rgb(34, 34, 34);
        background: linear-gradient(142.99deg, rgba(217, 217, 217, 0.63) 15.53%, rgba(243, 243, 243, 0.63) 88.19%);
        box-shadow: 0px 12px 24px -1px rgba(0, 0, 0, 0.18);
        border-color: rgba(7, 4, 14, 0);
        border-radius: 50px;
        block-size: 20px;
        padding: 18px 15px;
        outline: none;
        text-align: center;
        width: 200px;
        transition: 0.5s;
    }`]
})
export class FileManagerListComponent implements OnInit, OnDestroy {
    searchText: string;
    @ViewChild('matDrawer', { static: true }) matDrawer: MatDrawer;
    drawerMode: 'side' | 'over';
    selectedItem: Item;
    items: any[] = [];
    filteredItems: any[] = [];
    private _unsubscribeAll: Subject<any> = new Subject<any>();
    userType: string;
    constructor(
        private _activatedRoute: ActivatedRoute,
        private _changeDetectorRef: ChangeDetectorRef,
        private _router: Router,
        private _fileManagerService: FileManagerService,
        private _fuseMediaWatcherService: FuseMediaWatcherService,
        private route: ActivatedRoute,
        private spinner: NgxSpinnerService,
    ) {
        this.searchText = '';
    }

    // -----------------------------------------------------------------------------------------------------
    // @ Lifecycle hooks
    // -----------------------------------------------------------------------------------------------------

    /**
     * On init
     */
    ngOnInit(): void {
        this.route.params.subscribe(params => {
            const type = params['userType'];
            this.userType = params['userType'];

            if (type == 'customer') {
                this.fetchCustomers()
            }
            else if (type == 'admin') {
                this.fetchAdmins()
            }
            else if (type == 'contractor') {
                this.fetchContractor()
            }
            else {
                this.fetchItems()
            }
        });
        // this.fetchItems();
        this.subscribeToMediaQueryChanges();
    }


    ngOnDestroy(): void {
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
    }
    fetchCustomers(): void {
        this._fileManagerService.getCustomer().subscribe(
            ({ success, data }) => {
                if (success) {
                    this.items = data || [];
                    this.filteredItems = [...this.items]; // Initialize filteredItems with all items
                }
                this._changeDetectorRef.markForCheck();
            },
            (error) => {
                console.error('Error fetching items:', error);
            }
        );
    }
    fetchAdmins(): void {
        this._fileManagerService.getAdmins().subscribe(
            ({ success, data }) => {
                if (success) {
                    data?.forEach((element) => {
                        element.name = [element.first_name, element.middle_name, element.last_name]
                            .filter(Boolean)
                            .join(' ');
                    });
                }
                this.items = data || [];
                this.filteredItems = [...this.items]; // Initialize filteredItems with all items
                this._changeDetectorRef.markForCheck();
            },
            (error) => {
                console.error('Error fetching items:', error);
            }
        );
    }
    fetchContractor(): void {
        this._fileManagerService.getContractor().subscribe(
            ({ success, data }) => {
                if (success) {
                    this.items = data || [];
                    this.filteredItems = [...this.items]; // Initialize filteredItems with all items
                }

                this._changeDetectorRef.markForCheck();
            },
            (error) => {
                console.error('Error fetching items:', error);
            }
        );
    }

    fetchItems(): void {
        this._fileManagerService.getAllStaff().subscribe(
            ({ success, data }) => {
                if (success) {
                    data?.forEach((element) => {
                        element.name = [element.first_name, element.middle_name, element.last_name]
                            .filter(Boolean)
                            .join(' ');
                    });
                }
                this.items = data || [];
                this.filteredItems = [...this.items]; // Initialize filteredItems with all items
                this._changeDetectorRef.markForCheck();
            },
            (error) => {
                console.error('Error fetching items:', error);
            }
        );
    }

    subscribeToMediaQueryChanges(): void {
        this._fuseMediaWatcherService
            .onMediaQueryChange$('(min-width: 1440px)')
            .pipe(takeUntil(this._unsubscribeAll))
            .subscribe((state) => {
                this.drawerMode = state.matches ? 'side' : 'over';
                this._changeDetectorRef.markForCheck();
            });
    }
    onBackdropClicked(): void {
        this._router.navigate(['./'], { relativeTo: this._activatedRoute });
        this._changeDetectorRef.markForCheck();
    }

    trackByFn(index: number, item: Item): any {
        return item.id || index;
    }

    performSearch(): void {
        if (this.searchText.trim() === '') {
            this.filteredItems = [...this.items]; // Reset filteredItems if search text is empty
        } else {
            // Implement your search logic here based on this.searchText
            // For example, filter items by name containing searchText
            this.filteredItems = this.items.filter((item) =>
                item.name.toLowerCase().includes(this.searchText.toLowerCase())
            );
        }
        this._changeDetectorRef.markForCheck();
    }

    goBack() {
        window.history.back();
    }
}


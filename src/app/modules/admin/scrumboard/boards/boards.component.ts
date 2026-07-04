import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { Subject } from 'rxjs';
import * as moment from 'moment';
import { ScrumboardService } from '../scrumboard.service';
import { AddNewBusinessComponent } from '../add-new-business/add-new-business.component';
import { MatDialog } from '@angular/material/dialog';
import { FuseConfirmationService } from '@fuse/services/confirmation';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { AgentService } from 'app/services/crm/agent.service';
import { MatTableDataSource } from '@angular/material/table';
import { GlobalVariable } from 'app/shared/global';
@Component({
    selector: 'scrumboard-boards',
    templateUrl: './boards.component.html',
    styles: [`
    /* Add the custom styles here */
    thead {
      color: #337AB7;
    }

    .title-spacer {
      flex: 1 1 auto;
    }

    .search-form-fileld {
      margin-right: 20px;
    }
  `],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ScrumboardBoardsComponent implements OnInit, OnDestroy {
    boards: any = []
    selectedOption: string;
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;

    imageWidth: number = 30;
    imageMargin: number = 2;
    showImage: boolean = false;
    listFilter: any = {};
    errorMessage: string;
    customers: any = [];
    displayedColumns = ["name", "email", "phone", "company", "agent_name"];
    dataSource: any = null;
    pager: any = {};
    pagedItems: any[];
    searchFilter: any = {
        firstname: "",
        lastname: "",
        email: ""
    };
    selectedOptions: string;
    // Private
    private _unsubscribeAll: Subject<any> = new Subject<any>();

    /**
     * Constructor
     */
    constructor(
        private global: GlobalVariable,
        private _changeDetectorRef: ChangeDetectorRef,
        private _scrumboardService: ScrumboardService,
        private service: AgentService,
        public dialog: MatDialog,
        private _fuseConfirmationService: FuseConfirmationService,
    ) {
        this.global.showCrmTab = true
        this.getBoards()
        this.selectedOption = localStorage.getItem('selectedOption') || 'modern';
    }

    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    freshDataList(customers) {
        this.customers = customers;

        this.dataSource = new MatTableDataSource(this.customers.data);
        this.dataSource.paginator = this.paginator;
        this.dataSource.sort = this.sort;
    }


    getBoards() {
        this._scrumboardService.getBoards().subscribe(res => {
            if (res.success) {
                this.boards = res.data
                this._changeDetectorRef.markForCheck();
            }
        })
    }


    ngOnInit(): void {
        this.service.getList('customer')
            .subscribe(customers => {
                this.freshDataList(customers);
            },
                error => this.errorMessage = <any>error);

        this.searchFilter = {};
        this.listFilter = {};

    }

    /**
     * On destroy
     */
    ngOnDestroy(): void {
        this.global.showCrmTab = false
        // Unsubscribe from all subscriptions
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
    }

    // -----------------------------------------------------------------------------------------------------
    // @ Public methods
    // -----------------------------------------------------------------------------------------------------

    /**
     * Format the given ISO_8601 date as a relative date
     *
     * @param date
     */
    formatDateAsRelative(date: string): string {
        return moment(date, moment.ISO_8601).fromNow();
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

    newTaskBoard(event) {
        const dialogRef = this.dialog.open(AddNewBusinessComponent, {
            width: '350px', // Set the width to 'auto' to adjust based on content
            height: '100%', // Set the height to '100%'
            position: {
                right: '0', // Position the dialog on the right side
                top: '0', // Align the dialog to the top
            },
            panelClass: 'add-new-stagging', // Add your custom CSS class
        });

        // Apply transform style using Renderer2
        dialogRef.afterOpened().subscribe(() => {
            const dialogContainer = document.querySelector('.add-new-stagging') as HTMLElement;
            dialogContainer.style.transform = 'translateX(0px) translateY(-20px)';
        });

        dialogRef.afterClosed().subscribe(res => {
            console.log('model closed', res);
            if (res == 'save') {
                this.getBoards()
            }
        });
    }


    delBoard(id) {
        const confirmation = this._fuseConfirmationService.open({
            title: 'Delete list',
            message: 'Are you sure you want to delete this list and its cards? This action cannot be undone!',
            actions: {
                confirm: {
                    label: 'Delete'
                }
            }
        });

        // Subscribe to the confirmation dialog closed action
        confirmation.afterClosed().subscribe((result) => {

            // If the confirm button pressed...
            if (result === 'confirmed') {
                this._scrumboardService.deleteBoard(id).subscribe();
                this._changeDetectorRef.markForCheck();
                this.getBoards()
            }
        });
    }

    saveSelectedOption(selectedOption: string): void {
        localStorage.setItem('selectedOption', selectedOption);
    }


    searchCustomers(filters: any) {
        if (filters) {
            this.customers = this.dataSource; // Replace 'myDataSource' with your own data source

            console.log(this.customers.length);

            this.customers = this.customers.filter((customer) => {
                let match = true;

                Object.keys(filters).forEach((k) => {
                    match = match && filters[k] ?
                        customer[k].toLocaleLowerCase().indexOf(filters[k].toLocaleLowerCase()) > -1 : match;
                });

                return match;
            });

            this.freshDataList(this.customers);
        }
    }

    getCustomers(pageNum?: number) {
        this.service.getList('customer')
            .subscribe(customers => {
                this.freshDataList(customers);
            },
                error => this.errorMessage = <any>error);
    }

    resetListFilter() {
        this.listFilter = {};
        this.getCustomers();
    }

    reset() {
        this.listFilter = {};
        this.searchFilter = {};
        this.getCustomers();

    }

    resetSearchFilter(searchPanel: any) {
        searchPanel.toggle();
        this.searchFilter = {};
        this.getCustomers();
    }
}

import { ChangeDetectionStrategy, Component, OnInit, ViewChild } from '@angular/core';
import * as _ from 'lodash';
import { MatDialog } from '@angular/material/dialog'
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ConfirmDialog } from 'app/shared/dialog.component';
import { AgentService } from 'app/services/crm/agent.service';
import { MatTableDataSource } from '@angular/material/table';
import { GlobalVariable } from 'app/shared/global';
import { ToastServiceService } from 'app/services/toast-service.service';
import { PermissionsService } from 'app/services/permissions.service';
import { ButtonLayoutDisplay, ButtonMaker, DialogInitializer, DialogLayoutDisplay } from '@costlydeveloper/ngx-awesome-popup';
import { ViewFinacialComponent } from '../view-finacial/view-finacial.component';
import { CustomeLoaderComponent } from '../../custome-loader/custome-loader.component';


@Component({
    selector: 'quotation-list',
    templateUrl: './quotation-list.component.html',
    styleUrls: ['./customer-list.component.css'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class QuotationListComponent implements OnInit {
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;


    pageTitle: string = 'Financials';
    imageWidth: number = 30;
    imageMargin: number = 2;
    showImage: boolean = false;
    listFilter: any = {};
    errorMessage: string;
    customers: any = [];
    displayedColumns = ["invoice_no", "contact_name", "qut_owner", "financial_type", "created_at", "id"];
    dataSource: any = null;
    pager: any = {};
    pagedItems: any[];
    searchFilter: any = {
        firstname: "",
        lastname: "",
        email: ""
    };
    selectedOption: string;

    routeId
    leadType
    adminPermissions: any;
    filterOption: string = 'all';
    constructor(
        public dialog: MatDialog,
        private service: AgentService,
        public snackBar: MatSnackBar,
        private global: GlobalVariable,
        private permissionService: PermissionsService,
        private toast: ToastServiceService) {
        this.global.showCrmTab = true
    }


    ngOnInit(): void {
        const per = this.permissionService.getPermissionsByTitle('CRM');
        this.adminPermissions = per?.childPage?.find(item => item.title === 'Financials');
        this.getQut()
    }

    data
    getQut() {
        if (this.global.admin.userType === 'super-admin') {
            this.data = {
                userType: this.global.admin.userType,
                type: this.filterOption
            }
        }
        else {
            this.data = {
                id: this.global.admin.admin_id,
                type: this.filterOption
            }
        }
        this.service.getQuotes(this.data)
            .subscribe(customers => {
                this.freshDataList(customers);
            },
                error => this.errorMessage = <any>error);

        this.searchFilter = {};
        this.listFilter = {};
    }
    freshDataList(customers) {
        this.customers = customers;
        this.dataSource = new MatTableDataSource(this.customers.data);
        this.dataSource.paginator = this.paginator;
        this.dataSource.sort = this.sort;
    }
    applyFilter(filterValue: string) {
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
    }

    getCustomers() {
        if (this.global.admin.userType === 'super-admin') {
            this.data = {
                userType: this.global.admin.userType,
                type: this.filterOption
            }
        }
        else {
            this.data = {
                id: this.global.admin.admin_id,
                type: this.filterOption
            }
        }
        this.service.getQuotes(this.data)
            .subscribe(customers => {

                this.freshDataList(customers);
            },
                error => this.errorMessage = <any>error);
    }

    searchCustomers(filters: any) {
        if (filters) {
            this.customers = this.dataSource; // Replace 'myDataSource' with your own data source
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

    openDialog(id: number, financialType: any) {
        let dialogRef = this.dialog.open(ConfirmDialog,
            { data: { title: 'Confirmation', message: `Are you sure to delete this ${financialType}?` } });
        dialogRef.disableClose = true;

        dialogRef.afterClosed().subscribe(result => {
            this.selectedOption = result;

            if (this.selectedOption === dialogRef.componentInstance.ACTION_CONFIRM) {
                this.service.delQuotation(id).subscribe(
                    () => {
                        if (this.global.admin.userType === 'super-admin') {
                            this.data = {
                                userType: this.global.admin.userType,
                                type: this.filterOption
                            }
                        }
                        else {
                            this.data = {
                                id: this.global.admin.admin_id,
                                type: this.filterOption
                            }
                        }
                        this.service.getQuotes(this.data)
                            .subscribe(customers => {
                                this.freshDataList(customers);
                            },
                                error => this.errorMessage = <any>error);
                        this.toast.toastNotification('The quotation has been deleted successfully.', 'Financials Operation!')
                    },
                    (error: any) => {
                        this.errorMessage = <any>error;
                        this.toast.toastNotification('This quotation has not been deleted successfully. Please try again.', 'Financials Operation!')
                    }
                );
            }
        });
    }

    onOptionChange() {
        this.getQut()
    }

    ngOnDestroy(): void {
        this.global.showCrmTab = false
    }


    onSlideToggleChange(data) {
        data.admin_id = this.global.admin.admin_id
        let status = 'Financial Operation!'
        this.service.quoteToInvoice(data).subscribe(({ message, success }) => {
            if (success) {
                this.getQut()
                this.toast.toastNotification(message, status)
            }
            else {
                this.toast.toastNotification1(message, status)
            }
        }, (() => {
            this.toast.toastNotification1(this.global.apiError, status)
        }))
    }

    openLg(id) {
        const dialogPopup = new DialogInitializer(ViewFinacialComponent);
        dialogPopup.setConfig({
            width: '800px',
            layoutType: DialogLayoutDisplay.INFO // SUCCESS | INFO | NONE | DANGER | WARNING

        });
        dialogPopup.setCustomData({ id: id, });
        dialogPopup.setConfig({
            width: '800px',
            loaderComponent: CustomeLoaderComponent,
            layoutType: DialogLayoutDisplay.NONE // SUCCESS | INFO | NONE | DANGER | WARNING
        });
        dialogPopup.setButtons([
            new ButtonMaker('Download PDF', 'pdf', ButtonLayoutDisplay.SUCCESS),
            new ButtonMaker('Close', 'close', ButtonLayoutDisplay.DARK),
        ]);
        dialogPopup.openDialog$().subscribe(resp => {
            console.log('dialog response: ', resp);
        });
        // this.title = title;
        // this.modalService.open(longContent, { scrollable: true });
    }

}

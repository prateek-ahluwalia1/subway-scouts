import { Component, OnInit, ViewChild, OnDestroy } from '@angular/core';

import * as _ from 'lodash';

import { MatDialog } from '@angular/material/dialog'
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTableDataSource } from '@angular/material/table';
import { ConfirmDialog } from 'app/shared/dialog.component';
import { AgentService } from 'app/services/crm/agent.service';
import { ServiceService } from 'app/services/service.service';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { CreateAdminComponent } from '../../models/create-admin/create-admin.component';
import { GlobalVariable } from 'app/shared/global';
import { AdminService } from 'app/services/admin.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';


@Component({
    selector: 'customer-list',
    templateUrl: './sale-person-list.component.html',
    styleUrls: ['../crm-client/customer-list.component.css'],
    providers: [ConfirmDialog]
})
export class SalePersonListComponent implements OnInit, OnDestroy {
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;

    pageTitle: string = 'Sale Person';
    imageWidth: number = 30;
    imageMargin: number = 2;
    showImage: boolean = false;
    listFilter: any = {};
    errorMessage: string;
    customers: any = [];
    displayedColumns = ["name", "email", "phone", "id"];
    dataSource: any = null;
    pager: any = {};
    pagedItems: any[];
    searchFilter: any = {
        name: "",
        email: "",
        phone: ""
    };
    selectedOption: string;

    id;
    type;
    data;
    routeId


    constructor(
        public dialog: MatDialog,
        private modalService: NgbModal,
        private service: AgentService,
        public server: ServiceService, private adminservice: AdminService,
        public global: GlobalVariable,
        private toast: ToastServiceService,
        private trackAdmin: TrackAdminActivityService) {
        this.id = this.global.admin.admin_id
        this.type = this.global.admin.admin_user_type
        console.log(this.id, this.type);
        this.data = {
            action_by: this.id,
            page: window.location.href,
        }
    }
    ngOnDestroy() {
        this.data = {
            action_by: this.id,
            page: window.location.href,
            route_leave: this.routeId,
        }
        this.trackAdmin.storeActivity(this.data).subscribe((res) => {
            if (res.success) { }
        })
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

    ngOnInit(): void {
        this.server.getAdmin('active')
            .subscribe(customers => {
                this.freshDataList(customers);
            },
                error => this.errorMessage = <any>error);

        this.searchFilter = {};
        this.listFilter = {};
    }

    getCustomers(pageNum?: number) {
        this.service.getList('sub-admins')
            .subscribe(customers => {
                this.freshDataList(customers);
            },
                error => this.errorMessage = <any>error);
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

    openDialog(id: number) {
        let dialogRef = this.dialog.open(ConfirmDialog,
            { data: { title: 'Confirmation', message: 'Are you sure to delete this item?' } });
        dialogRef.disableClose = true;
        dialogRef.afterClosed().subscribe(result => {
            this.selectedOption = result;
            if (this.selectedOption === dialogRef.componentInstance.ACTION_CONFIRM) {
                this.service.deleteUser(id, 'agent').subscribe(
                    () => {
                        this.service.getList('sub-admins')
                            .subscribe(customers => {
                                this.freshDataList(customers);
                            },
                                error => this.errorMessage = <any>error);
                        this.toast.toastNotification('The sale person has been deleted successfully', 'Sales Person')
                    },
                    (error: any) => {
                        this.errorMessage = <any>error;
                        console.log(this.errorMessage);
                        this.toast.toastNotification('This sale person has not been deleted successfully', 'Sales Person')
                    }
                );
            }
        });
    }


    createAdmin(id?) {
        if (id == 'new') {
            const modelRef = this.modalService.open(CreateAdminComponent, { windowClass: "create-admin", size: 'lg' })
            modelRef.componentInstance.fromParent = 'new';
            modelRef.result.then((result) => {
                console.log("Modal Result", `Closed with: ${result}`)
            }, (reason) => {
                this.server.getAdmin('active')
                    .subscribe(customers => {
                        this.freshDataList(customers);
                    },
                        error => this.errorMessage = <any>error);
                this.global.selectedCustomers = []
                console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)
                    }`)

            });
        }
        else {
            this.adminservice.getAdminData(id).subscribe(res => {
                if (res.success == true) {
                    const modalRef = this.modalService.open(CreateAdminComponent, {
                        windowClass: "create-admin", size: 'lg'
                    })
                    modalRef.componentInstance.fromParent = res;
                    modalRef.result.then((result) => {
                        console.log("Modal Result", `Closed with: ${result}`)
                    }, (reason) => {
                        this.server.getAdmin('active')
                            .subscribe(customers => {
                                this.freshDataList(customers);
                            },
                                error => this.errorMessage = <any>error);

                        console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)

                    });
                }
            }, (error) => {
                console.log(error);
            });
        }
    }

    getDismissReason(reason: any): string {
        if (reason === ModalDismissReasons.ESC) {
            return 'by pressing ESC';
        } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
            return 'by clicking on a backdrop';
        } else if (reason === 'updated') {
            // this.getAdmin_data();

        }
    }

}

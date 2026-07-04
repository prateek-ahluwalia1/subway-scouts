import { Component, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { SupportService } from '../support.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
    selector: 'settings-security',
    templateUrl: './security.component.html',
    styleUrls: ['./security.component.scss'],
})
export class SettingsSecurityComponent implements OnInit {
    @ViewChild(MatPaginator) paginator: MatPaginator;
    @ViewChild(MatSort) sort: MatSort;
    @Input() ticketName: string;
    @Output() chatObjet = new EventEmitter<string>();

    isShowSkelton = false;
    pageTitle = 'Support Tickets';
    imageWidth = 30;
    imageMargin = 2;
    showImage = false;
    listFilter: any = {};
    errorMessage: string;
    tickets: any[] = [];
    displayedColumns = ['subject', 'status', 'priority', 'name', 'sender_name', 'updated_at'];
    dataSource: MatTableDataSource<any> = new MatTableDataSource<any>();
    searchFilter: any = {
        firstname: '',
        lastname: '',
        email: ''
    };
    routeId: any;

    constructor(
        public dialog: MatDialog,
        private global: GlobalVariable,
        public snackBar: MatSnackBar,
        private service: SupportService) { }

    ngOnInit(): void {
        this.isShowSkelton = true;
        const data = {
            is_received_support: this.global.admin.is_received_support,
            [this.global.admin.admin_user_type === 'guard' ? 'guard_id' : 'admins_id']: this.global.admin.admin_id
        };

        this.service.getTickets(data).subscribe(
            (res) => {
                this.freshDataList(res.data);
                this.isShowSkelton = false;
            },
            (error) => {
                this.errorMessage = error;
                this.isShowSkelton = false;
            }
        );

        this.searchFilter = {};
        this.listFilter = {};
    }

    applyFilter(filterValue: string): void {
        filterValue = filterValue.trim().toLowerCase();
        this.dataSource.filter = filterValue;
    }

    freshDataList(data: any[]): void {
        this.tickets = data.filter((ticket) => ticket.status.toLowerCase() === this.ticketName.toLowerCase());
        this.dataSource = new MatTableDataSource(this.tickets);
        this.dataSource.paginator = this.paginator;
        this.dataSource.sort = this.sort;
    }

    getCustomers(): void {
        const data = { id: this.global.admin.admin_id };
        this.service.getTickets(data).subscribe(
            (tickets) => {
                this.freshDataList(tickets.data);
            },
            (error) => {
                this.errorMessage = error;
            }
        );
    }

    searchCustomers(filters: any): void {
        if (filters) {
            this.tickets = this.dataSource.data.filter((customer) =>
                Object.keys(filters).every((k) =>
                    customer[k].toLowerCase().includes(filters[k].toLowerCase())
                )
            );

            this.freshDataList(this.tickets);
        }
    }

    resetListFilter(): void {
        this.listFilter = {};
        this.getCustomers();
    }

    reset(): void {
        this.listFilter = {};
        this.searchFilter = {};
        this.getCustomers();
    }

    resetSearchFilter(searchPanel: any): void {
        searchPanel.toggle();
        this.searchFilter = {};
        this.getCustomers();
    }

    getStatusClass(leadStatus: string): string {
        return leadStatus === 'medium' ? 'won-status' : leadStatus === 'high' ? 'lost-status' : 'low-priority';
    }

    goToChat(type: string): void {
        this.chatObjet.emit(type);
    }
}

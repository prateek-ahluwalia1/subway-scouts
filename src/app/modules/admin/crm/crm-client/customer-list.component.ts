import { Component, OnDestroy, OnInit, ViewChild } from "@angular/core";
import * as _ from "lodash";
import { MatDialog } from "@angular/material/dialog";
import { MatPaginator } from "@angular/material/paginator";
import { MatSort } from "@angular/material/sort";
import { MatSnackBar } from "@angular/material/snack-bar";
import { ConfirmDialog } from "app/shared/dialog.component";
import { AgentService } from "app/services/crm/agent.service";
import { MatTableDataSource } from "@angular/material/table";
import { GlobalVariable } from "app/shared/global";
import { TrackAdminActivityService } from "app/services/track-admin-activity.service";
import { ActivatedRoute } from "@angular/router";
import { ToastServiceService } from "app/services/toast-service.service";
import { PermissionsService } from "app/services/permissions.service";
import { Subscription, interval } from "rxjs";
import { NgbPopover } from "@ng-bootstrap/ng-bootstrap";
import { FormControl, FormGroup } from "@angular/forms";
import moment from "moment";
import { ServiceService } from "app/services/service.service";

@Component({
  selector: "customer-list",
  templateUrl: "./customer-list.component.html",
  styleUrls: ["./customer-list.component.css"],
  providers: [ConfirmDialog],
})
export class CustomerListComponent implements OnInit, OnDestroy {
  @ViewChild(MatPaginator) paginator: MatPaginator;
  @ViewChild(MatSort) sort: MatSort;

  pageTitle: string = "Leads";
  imageWidth: number = 30;
  imageMargin: number = 2;
  showImage: boolean = false;
  listFilter: any = {};
  errorMessage: string;
  customers: any = [];
  displayedColumns = [];
  dataSource: any = null;
  pager: any = {};
  pagedItems: any[];
  searchFilter: any = {
    firstname: "",
    lastname: "",
    email: "",
  };
  selectedOption: string;

  leadType;
  adminPermissions: any;
  private pollingSubscription: Subscription;
  filterOption: string = "won";

  allLeads: any[] = [];
  userBusiness: any;

  range = new FormGroup({
    start: new FormControl<Date | null>(null),
    end: new FormControl<Date | null>(null),
  });

  admins: any[] = [];
  leadObject: any;
  activePopover: NgbPopover | null = null;
  won_status;
  dateRange: any;
  agents: any[] = [];
  companylist: any[] = [];
  selectedCompanies: string[] = []; // Assuming companylist contains string values
  selectedAgents: any[] = []; // Assuming agents array contains string values
  user_type: any
  start
  end
  constructor(
    public dialog: MatDialog,
    private service: AgentService,
    public snackBar: MatSnackBar,
    private global: GlobalVariable,
    private route: ActivatedRoute,
    private toast: ToastServiceService,
    public server: ServiceService,
    private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService
  ) {

    const data = JSON.parse(localStorage.getItem('admin'))
    this.user_type = data?.admin_user_type

    this.global.showCrmTab = true;
    let routerId = localStorage.getItem("routerId");

    const leadTypeMapping = {
      lead: { leadType: "lead", pageTitle: "Leads" },
      contacted: { leadType: "contacted", pageTitle: "Contacted Leads" },
      won: { leadType: "won", pageTitle: "Won Leads" },
    };

    const urlSegments = this.route.snapshot.url.map((segment) => segment.path);
    const leadTypeData = leadTypeMapping[urlSegments[0]] || {
      leadType: "lost",
      pageTitle: "Lost Leads",
    };

    this.leadType = leadTypeData.leadType;
    this.pageTitle = leadTypeData.pageTitle;

    if (!routerId) {
      this.activity(this.pageTitle);
    }
  }

  applyFilter(filterValue: string) {
    filterValue = filterValue.trim(); // Remove whitespace
    filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
    this.dataSource.filter = filterValue;
  }

  ngOnInit(): void {
    const per = this.permissionService.getPermissionsByTitle("CRM");
    this.adminPermissions = per?.childPage?.find(
      (item) => item.title === "Leads"
    );

    // Start polling every 20 seconds
    // this.pollingSubscription = interval(30000).subscribe(() => {
    //   this.fetchData();
    // });

    let storedValue = localStorage.getItem('currentMonth');
    let isCurrentMonth = (storedValue === 'true');
    let today = moment();
    if (isCurrentMonth) {
      this.start = today.startOf('month').toDate();
      this.end = today.endOf('month').toDate();
    }
    else if (this.leadType == 'lost' || this.leadType == 'won') {
      this.start = today.clone().subtract(1, 'month').startOf('month').toDate();
      this.end = today.clone().endOf('month').toDate();
    }

    this.searchFilter = {};
    this.listFilter = {};
    const business = JSON.parse(localStorage.getItem("business"));
    this.userBusiness = business.id;
    if (
      (this.leadType == "lost" || this.leadType == "contacted") &&
      this.userBusiness === 87
    ) {
      this.displayedColumns = [
        "leaad_client_name",
        "email",
        "phone",
        "travel_date",
        "sub_company",
        "saleperson_name",
        "lead_status",
        "created_at",
        "updated_at",
        "id",
      ];
    } else if (this.leadType == "won" && this.userBusiness === 87) {
      this.displayedColumns = [
        "leaad_client_name",
        "email",
        "phone",
        "travel_date",
        "sub_company",
        "saleperson_name",
        "assign_operation_name",
        "lead_status",
        "created_at",
        "updated_at",
        "id",
      ];
    } else if (this.leadType == "lead" && this.userBusiness === 87) {
      this.displayedColumns = [
        "leaad_client_name",
        "email",
        "phone",
        "travel_date",
        "sub_company",
        "saleperson_name",
        "lead_status",
        "created_at",
        "id",
      ];
    }
    else if (this.leadType == "won" || this.leadType == "lost" || this.leadType == "contacted") {
      this.displayedColumns = [
        "name",
        "email",
        "phone",
        "saleperson_name",
        "lead_status",
        "created_at",
        "updated_at",
        "id",
      ];
    }
    else {
      this.displayedColumns = [
        "name",
        "email",
        "phone",
        "saleperson_name",
        "lead_status",
        "created_at",
        "id",
      ];
    }

    this.getleadsCount();

    this.getSalePerson();

    this.getCompanyList();
  }

  fetchData(): void {
    this.service
      .getContactList(
        this.leadType,
        this.selectedCompanies,
        this.selectedAgents,
        this.dateRange.start ? moment(this.dateRange?.start).format("MM-DD-YYYY") : '',
        this.dateRange.end ? moment(this.dateRange?.end).format("MM-DD-YYYY") : ''
      )
      .subscribe(
        (customers) => {
          this.freshDataList(customers);
        },
        (error) => (this.errorMessage = <any>error)
      );
  }

  freshDataList(customers) {
    this.customers = customers;
    this.allLeads = this.customers.data;
    this.dataSource = new MatTableDataSource(this.customers.data);
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }

  getCustomers(pageNum?: number) {
    this.service
      .getContactList(
        this.leadType,
        this.selectedCompanies,
        this.selectedAgents,
        this.dateRange.start ? moment(this.dateRange?.start).format("MM-DD-YYYY") : '',
        this.dateRange.end ? moment(this.dateRange?.end).format("MM-DD-YYYY") : ''
      )
      .subscribe(
        (customers) => {
          this.freshDataList(customers);
        },
        (error) => (this.errorMessage = <any>error)
      );
  }

  searchCustomers(filters: any) {
    if (filters) {
      this.customers = this.dataSource; // Replace 'myDataSource' with your own data source
      this.customers = this.customers.filter((customer) => {
        let match = true;
        Object.keys(filters).forEach((k) => {
          match =
            match && filters[k]
              ? customer[k]
                .toLocaleLowerCase()
                .indexOf(filters[k].toLocaleLowerCase()) > -1
              : match;
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
    let dialogRef = this.dialog.open(ConfirmDialog, {
      data: {
        title: "Confirmation",
        message: "Are you sure to delete this lead?",
      },
    });
    dialogRef.disableClose = true;

    dialogRef.afterClosed().subscribe((result) => {
      this.selectedOption = result;

      if (this.selectedOption === dialogRef.componentInstance.ACTION_CONFIRM) {
        this.service.deleteUser(id, "customer").subscribe(
          (res) => {
            this.service
              .getContactList(
                this.leadType,
                this.selectedCompanies,
                this.selectedAgents,
                this.dateRange.start ? moment(this.dateRange?.start).format("MM-DD-YYYY") : '',
                this.dateRange.end ? moment(this.dateRange?.end).format("MM-DD-YYYY") : ''
              )
              .subscribe(
                (customers) => {
                  if (customers.success) {
                    this.toast.toastNotification(
                      res.message,
                      "Lead Operation!"
                    );
                    this.freshDataList(customers);
                  }
                },
                (error) => (this.errorMessage = <any>error)
              );
          },
          (error: any) => {
            this.errorMessage = <any>error;
            this.toast.toastNotification1(
              "This lead has not been deleted successfully. Please try again.",
              "Lead Operation!"
            );
          }
        );
      }
    });
  }

  ngOnDestroy(): void {
    localStorage.removeItem('currentMonth');
    this.closeActivePopover();

    if (this.pollingSubscription) {
      this.pollingSubscription.unsubscribe();
    }
    this.global.showCrmTab = false;
    localStorage.removeItem("routerId");
    this.trackAdmin
      .storeActivity(
        `Exit ${this.pageTitle}`,
        `Exit ${this.pageTitle}`,
        localStorage.getItem("routerId")
      )
      .subscribe((res) => {
        if (res.success) {
          localStorage.removeItem("routerId");
        }
      });
  }

  getStatusClass(leadStatus: string): string {
    return leadStatus === "Won"
      ? "won-status"
      : leadStatus === "Lost Lead"
        ? "lost-status"
        : "";
  }

  activity(page) {
    this.trackAdmin
      .storeActivity(`${page}`, `Enter in ${page}`)
      .subscribe(({ success, id }) => {
        if (success) {
          localStorage.setItem("routerId", id);
        }
      });
  }

  onSlideToggleChange(data) {
    data.admin_id = this.global.admin.admin_id;
    let status = "Leads Operation!";
    this.service.archiveCustomer(data).subscribe(
      ({ message, success }) => {
        if (success) {
          this.fetchData();
          this.toast.toastNotification(message, status);
        } else {
          this.toast.toastNotification1(message, status);
        }
      },
      () => {
        this.toast.toastNotification1(this.global.apiError, status);
      }
    );
  }

  onOptionChange(value) {
    if (value.value === "archive") {
      this.dataSource = this.allLeads.filter((item) => item.is_archived === 1);
    } else {
      this.fetchData();
    }
  }

  generateLeadsReport() {
    const startDate = moment(this.range.value.start).format("MM-DD-YYYY");
    const endDate = moment(this.range.value.end).format("MM-DD-YYYY");
    let data = {
      date: startDate + " - " + endDate,
    };
    this.service.getLeadsReport(data).subscribe((res) => {
      console.log(res);
      if (res.success) {
        this.downloadExcelFile(res.path, "Leads Report");
      }
    });
  }

  downloadExcelFile(fileUrl: string, fileName: string) {
    const link = document.createElement("a");
    link.href = fileUrl;
    link.target = "_blank";
    link.download = fileName;
    link.click();
  }

  receiveDataFromChild(data: any) {
    this.leadObject.assign_operation = data.value.id;
  }

  saveLead(): void {
    this.leadObject.admin_id = this.global.admin.admin_id;
    this.service.saveCustomer(this.leadObject, "Update Lead").subscribe(
      (response: any) => {
        if (response.success) {
          this.fetchData();
          this.toast.toastNotification(response.message, "Leads Operation");
        }
      },
      (error: any) => (this.errorMessage = <any>error)
    );
  }

  trackById(index: number, item: any): any {
    return item.id;
  }

  updateWonStatus() {
    if (this.won_status) {
      this.leadObject.admin_id = this.global.admin.admin_id;
      this.leadObject.won_status = this.won_status;
      this.service.saveCustomer(this.leadObject, "Update Lead").subscribe(
        (response: any) => {
          if (response.success) {
            this.fetchData();
            this.toast.toastNotification(response.message, "Leads Operation");
          }
        },
        (error: any) => (this.errorMessage = <any>error)
      );
    } else {
      this.toast.toastNotification("Select value to continue", "Warning");
    }
  }

  openPopover(newPopover: NgbPopover, customer: any, type: string) {
    if (this.activePopover && this.activePopover !== newPopover) {
      this.activePopover.close();
    }
    this.activePopover = newPopover;

    if (type == "assign") {
      this.leadObject = customer;
      this.server.getAdmin("active").subscribe(({ data, success }) => {
        if (success) {
          this.admins = data;
        }
      });
    } else if (type == "status") {
      this.leadObject = customer;
      this.won_status = customer?.won_status;
    }
  }

  closeActivePopover() {
    if (this.activePopover) {
      this.activePopover.close();
      this.activePopover = null;
    }
  }

  getleadsCount() {
    this.server.leadsCount().subscribe();
  }

  getSalePerson() {
    const data = { type: "saleperson" };
    this.server.getAdmin("active", data).subscribe(
      (users) => {
        this.agents = users.data;
      },
      (error) => (this.errorMessage = <any>error)
    );
  }
  getStartEnd(event: any) {
    this.dateRange = event;
  }

  getCompanyList() {
    this.service.getCompanyList().subscribe((response: any) => {
      this.companylist = response.data.map((site_name: string, index: number) => {
        return { id: index + 1, site_name };
      });
    });
  }

  onAgentSelectionChange(selectedAgents) {
    this.selectedAgents = this.getAgentIds(selectedAgents.value);
    this.fetchData();
  }

  onDateRangeChange(dateRange) {
    this.dateRange = dateRange;
    this.fetchData();
  }

  getAgentIds(agents: any[]): number[] {
    return agents.map((agent) => agent.id);
  }


  receiveDataFromChildSite(data) {
    this.selectedCompanies = data?.map(item => item.site_name);
    this.fetchData();
  }
}

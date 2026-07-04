import { SelectionModel } from "@angular/cdk/collections";
import { ChangeDetectorRef, Component, OnDestroy, OnInit } from "@angular/core";
import { MatTableDataSource } from "@angular/material/table";
import { NgbModal, ModalDismissReasons, NgbModalRef } from "@ng-bootstrap/ng-bootstrap";
import { MatDialog, MatDialogRef } from "@angular/material/dialog";
import { PersonDetailComponent } from "../models/person-detail/person-detail.component";
import { OnBoardingStaffComponent } from "../models/on-boarding-staff/on-boarding-staff.component";
import { UserMenuComponent } from "../models/user-menu/user-menu.component";
import { StaffService } from "app/services/staff.service";
import { ToastServiceService } from "app/services/toast-service.service";
import { EmployeeDetailsComponent } from "../employee-details/employee-details.component";
import { UniformFormComponent } from "../uniform-form/uniform-form.component";
import { EmergencyContactComponent } from "../emergency-contact/emergency-contact.component";
import { RefrencesFormComponent } from "../refrences-form/refrences-form.component";
import { PersonalRefrenceFormComponent } from "../personal-refrence-form/personal-refrence-form.component";
import { CheckListFormComponent } from "../check-list-form/check-list-form.component";
import { ProfileCommentComponent } from "../profile-comment/profile-comment.component";
import AircallPhone from 'aircall-everywhere';
import { GlobalVariable } from "app/shared/global";
import { PageEvent } from "@angular/material/paginator";
import { TrackAdminActivityService } from "app/services/track-admin-activity.service";
import { FormBuilder, FormControl, FormGroup, Validators } from "@angular/forms";
import { PermissionsService } from "app/services/permissions.service";
import { NgxSpinnerService } from "ngx-spinner";
import { PdfComponent } from "../component/pdf/pdf.component";

export interface PeriodicElement { }
const ELEMENT_DATA: PeriodicElement[] = [];
@Component({
  selector: "app-users",
  templateUrl: "./users.component.html",
  styleUrls: ["./users.component.scss"],
})

export class UsersComponent implements OnInit, OnDestroy {

  displayComment = [];
  feedbackArrayLength: number;
  staffList = [];
  guardsData

  // Notice that 'Sec_Lic_Number' and 'Sec_Lic_Expiry' are removed from here
  displayedColumns: string[] = [
    'profile_image',
    'first_name',
    'state',
    'admin_approved',
    'guard_status',
    'Phone_Number',
    'status',
    'action'
  ];

  private aircallPhone: AircallPhone;

  dataSource = new MatTableDataSource<PeriodicElement>(ELEMENT_DATA);
  selection = new SelectionModel<PeriodicElement>(true, []);
  closeAdminCreateModal: NgbModalRef;

  // Set default tab to 'active' here
  userType: string = "active";

  idleState = "Not started.";
  timedOut = false;
  min: any;
  sec: any;
  dialogRef: MatDialogRef<any>;
  closeCreateModal: NgbModalRef;
  new_staff_dropdown = false;
  createPersonalModal: NgbModalRef;
  searchTerm: string;
  filteredItems = [];
  response: any;
  length: number = 0;
  pageSize: number = 100;
  pageIndex: number = 0;
  pageSizeOptions: number[] = [100];
  hidePageSize = false;
  showPageSizeOptions = true;
  showFirstLastButtons = true;
  disabled = false;
  pageEvent: PageEvent;
  id;
  type;
  data;
  routeId
  stafffPermissions
  interval: NodeJS.Timeout;
  modalRef;
  elementId;
  element
  AddDelStaffReason: FormGroup;
  feedbackArray: string[] = [];
  showRedIcon = false;

  constructor(
    private modalService: NgbModal,
    private staffService: StaffService,
    public dialog: MatDialog,
    private toastService: ToastServiceService,
    public global: GlobalVariable,
    private trackAdmin: TrackAdminActivityService,
    private fb: FormBuilder,
    private permissionService: PermissionsService,
    private _changeDetectorRef: ChangeDetectorRef,
    private spinner: NgxSpinnerService
  ) {
    const per = this.permissionService.getPermissionsByTitle('Onboarding');
    this.stafffPermissions = per?.childPage?.find(item => item.title === 'Other Staff');
    this.getAllStaff();

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
    this.dataSource.data = this.staffList;
    this.AddDelStaffReason = this.fb.group({
      reason: new FormControl('', Validators.required),
    })

    this.polling()
  }

  ngOnDestroy() {
    clearInterval(this.interval)
    this.trackAdmin.storeActivity('Staff', 'Exit Staff Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  getAllStaff() {
    const eventValue = this.pageEvent;
    let data = {
      length: eventValue ? eventValue.length : 0,
      pageIndex: eventValue ? eventValue.pageIndex : 0,
      pageSize: eventValue ? eventValue.pageSize : this.pageSize,
      previousPageIndex: eventValue ? eventValue.previousPageIndex : 0,
      document_type: 'security_license',
      guard_status: this.userType
    }
    this.staffService.getStaff(data).subscribe(
      (res) => {
        if (res.success) {
          res.data.forEach((element) => {
            element.first_name = [
              element.first_name,
              element.middle_name,
              element.last_name,
            ]
              .filter(Boolean)
              .join(" ");
            element.guard_doc = element.guard_doc || [];
          });

          this.staffList = res.data;
          this.length = res.length;
          this.guardsData = res;
          this.dataSource.data = this.staffList;
        }
        this._changeDetectorRef.markForCheck()
      },

      error => {
        console.log(error);
      }
    );
  }

  getDocumentProgress(element: any): number {
    let progress = 0;

    // Check residential_status (20%)
    if (element.residential_status && element.residential_status.trim() !== '') {
      progress += 20;
    }

    // Check guard_doc array for specific documents
    const guardDocs = element.guard_doc || [];
    const requiredDocs = ['passport', 'visa', 'security_license', 'first_aid'];

    requiredDocs.forEach(docType => {
      const doc = guardDocs.find((d: any) => d.type === docType);
      if (doc && (doc.number || doc.expiry)) {
        progress += 20;
      }
    });

    // Ensure progress does not exceed 100
    return Math.min(progress, 100);
  }

  getUserType(userType: string) {
    this.searchTerm = ''
    this.userType = userType;
    if (this.pageEvent) {
      this.pageEvent.pageIndex = 0;
      this.pageEvent.pageSize = 100;
      this.pageEvent.length = 0;
    }
    // Reset the paginator to its initial state
    this.length = 0;
    this.pageSize = 100;
    this.pageIndex = 0;
    this.pageSizeOptions = [100];
    this.getAllStaff();
  }

  openModel(id) {
    this.staffService.getSpecificStaffData(id).subscribe(
      (res) => {
        if (res.success == true) {
          const modalRef = this.modalService.open(UserMenuComponent, {
            windowClass: "createModalClass",
            fullscreen: true,
            scrollable: true,
          });
          modalRef.componentInstance.fromParent = res.data;
          modalRef.componentInstance.GuardID = id;
          modalRef.result.then(
            (result) => {
              console.log("Modal Result", `Closed with: ${result}`);
            },
            (reason) => {
              let rea = this.getDismissReason(reason)
              if (rea == 'update') {
                this.getAllStaff()
              }
            }
          );
        }
      },

      (error) => {
        let status = "Staff Operation";
        let body = "Something went wrong";
        this.toastService.toastNotification1(body, status);
        console.log(error);
      }
    );
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return "by pressing ESC";
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return "by clicking on a backdrop";
    } else {
      return `${reason}`;
    }
  }

  /**Add new Staff Model */
  addNewStaff() {
    this.new_staff_dropdown = !this.new_staff_dropdown;
  }

  createNewStaff(staff) {
    this.new_staff_dropdown = false;
    if (staff == "newStaff") {
      const modalRef = this.modalService.open(PersonDetailComponent, {
        windowClass:
          "personalDetailOpenModalClass ",
        size: "xl",
        animation: false,
      });
      modalRef.componentInstance.fromParent = staff;
      modalRef.result.then(
        (result) => {
          console.log("Modal Result", `Closed with: ${result}`);
        },
        (reason) => {
          let status = this.getDismissReason(reason);
          console.log(status);

          if (status == "QuickStaff") {
            this.getAllStaff();
          }
        }
      );
    }
    else {
      const modalRef = this.modalService.open(OnBoardingStaffComponent, {
        centered: true,
        size: "lg",
        windowClass: "onboarding-model",
        animation: true,
      });
      modalRef.componentInstance.fromParent = staff;
      modalRef.result.then(
        (result) => {
          console.log("Modal Result", `Closed with: ${result}`);
        },
        (reason) => {
          let status = this.getDismissReason(reason);
          if (status == "QuickStaff") {
            this.getAllStaff();
          }
        }
      );
    }
  }

  ////change staff status///////
  updateActiveStatus(element) {
    let data = {
      id: element.id,
      admin_id: this.global.admin.admin_id
    }
    this.staffService.changeAdminApprovalStatus(data).subscribe((res) => {
      if (res.success == true) {
        this.getAllStaff();
        let status = "Staff Operation";
        this.toastService.toastNotification(res.message, status);
        this.trackAdmin.storeActivity('Staff', `Change a staff admin approve status of Name: ${element?.name} - Response Message: ${res.message}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
        })
      } else {
        this.getAllStaff();
        let status = "Staff Operation";
        this.toastService.toastNotification1(res.message, status);
      }
    });
  }

  restore(element) {
    this.staffService.restoreGuard(element.id).subscribe((res) => {
      if (res.success == true) {
        this.getAllStaff();
        let status = "Staff Operation";
        this.toastService.toastNotification(res.message, status);
        this.trackAdmin.storeActivity('Staff', `Restore a staff of Name: ${element?.name}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
        })
      } else {
        this.getAllStaff();
        let status = "Staff Operation";
        this.toastService.toastNotification1(res.message, status);
      }
    });
  }

  guard_status(element) {
    let data = {
      admin_id: this.global.admin.admin_id,
      id: element.id
    }
    this.staffService.changeStaffStatus(data).subscribe(({ success, message }) => {
      if (success) {
        this.getAllStaff();
        let status = "Staff Operation";
        this.toastService.toastNotification(message, status);
        this.trackAdmin.storeActivity('Staff', `Change a staff active status of Name: ${element?.name} - Response Message: ${message}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
        })
      } else {
        this.getAllStaff();
        let status = "Staff Operation";
        this.toastService.toastNotification1(message, status);
      }
    });
  }

  openEmployee(guard_id) {
    const modalRef = this.modalService.open(EmployeeDetailsComponent, { size: "xl" });
    modalRef.componentInstance.fromParent = guard_id;
  }

  openUniform(guard_id) {
    const modalRef = this.modalService.open(UniformFormComponent, { size: "xl" });
    modalRef.componentInstance.fromParent = guard_id;
  }

  openEmergency(guard_id) {
    const modalRef = this.modalService.open(EmergencyContactComponent, { size: "xl" });
    modalRef.componentInstance.fromParent = guard_id;
  }

  openRefrences(guard_id) {
    const modalRef = this.modalService.open(RefrencesFormComponent, { size: "xl" });
    modalRef.componentInstance.fromParent = guard_id;
  }

  openPersonalrefrences(guard_id) {
    const modalRef = this.modalService.open(PersonalRefrenceFormComponent, { size: "xl" });
    modalRef.componentInstance.fromParent = guard_id;
  }

  openChecklist(guard_id) {
    const modalRef = this.modalService.open(CheckListFormComponent, { size: "xl" });
    modalRef.componentInstance.fromParent = guard_id;
  }

  ////search admins
  search() {
    if (this.searchTerm) {
      clearInterval(this.interval)
      const eventValue = this.pageEvent;
      let data = {
        status: this.userType,
        searchTerm: this.searchTerm,
        length: eventValue ? eventValue.length : 0,
        pageIndex: eventValue ? eventValue.pageIndex : 0,
        pageSize: eventValue ? eventValue.pageSize : this.pageSize,
        previousPageIndex: eventValue ? eventValue.previousPageIndex : 0,
        document_type: 'security_license',
        guard_status: this.userType
      }
      this.staffService.searchStaff(data).subscribe(({ success, data, length }) => {
        if (success) {
          data.forEach((element) => {
            element.first_name = [
              element.first_name,
              element.middle_name,
              element.last_name,
            ]
              .filter(Boolean)
              .join(" ");
          });
          this.staffList = data;
          this.length = length;
          this.dataSource.data = this.staffList;
          this._changeDetectorRef.markForCheck()
        }
      })
    }
    else {
      this.getAllStaff()
      this.polling()
    }
  }

  openVerticallyCentered(element, delstaffreason) {
    this.elementId = element.id;
    this.element = element;
    this.modalRef = this.modalService.open(delstaffreason, { centered: true });
  }

  delstaffdata(id) {
    this.elementId = id;
    this.staffService.delStaff(this.elementId, this.AddDelStaffReason.value.reason).subscribe(({ message, success }) => {
      if (success) {
        this.toastService.toastNotification1(message, 'Staff Operation!')
        this.getAllStaff();
        this.AddDelStaffReason.reset();
        this.element = ''
        this.elementId = null
        this.trackAdmin.storeActivity('Staff', `Delete a staff of name: ${this.element?.name} with reason: ${this.AddDelStaffReason.value?.reason}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
        })
      }
    },
      (error) => {
        console.log(error);
      });
    this.modalRef.close();
  }

  openComment(id, type) {
    const modalRef = this.modalService.open(ProfileCommentComponent, {
      centered: true,
    });
    modalRef.componentInstance.guardId = id;
    modalRef.componentInstance.type = type;
    modalRef.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        console.log(reason);
        if (reason != 'dismiss') {
          this.getAllStaff()
        }
        console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)
      }
    );
  }

  handlePageEvent(e: PageEvent) {
    this.pageEvent = e;
    this.length = e.length;
    this.pageSize = e.pageSize;
    this.pageIndex = e.pageIndex;
    this.getAllStaff()
  }

  setPageSizeOptions(setPageSizeOptionsInput: string) {
    if (setPageSizeOptionsInput) {
      this.pageSizeOptions = setPageSizeOptionsInput.split(',').map(str => +str);
    }
  }

  handleBrokenImage(event: Event) {
    const imgElement = event.target as HTMLImageElement;
    imgElement.src = '../../../../assets/images/logo/scou_1.png';
  }

  polling() {
    this.interval = setInterval(() => {
      this.getAllStaff()
    }, 40000);
  }

  getAllTabsCount(): number {
    return (
      (this.guardsData?.active_guard_count || 0) +
      (this.guardsData?.inactive_guard_count || 0) +
      (this.guardsData?.pending_guard_count || 0) +
      (this.guardsData?.new_guard_count || 0) +
      (this.guardsData?.deleted_guard_count || 0)
    );
  }

  statusRecord(user) {
    this.spinner.show()
    this.staffService.statusRecord(user.id).subscribe(res => {
      const modalRef = this.modalService.open(PdfComponent, {
        size: 'lg'
      });
      modalRef.componentInstance.res = res;
      modalRef.componentInstance.staff = user;
      this.spinner.hide();
    })
  }

  sendEmailAgain(data) {
    this.staffService.sendEmailAgain(data).subscribe(res => {
      if (res.success) {
        this.toastService.toastNotification(res.message, 'Email Verification!')
      }
      else {
        this.toastService.toastNotification1(res.message, 'Email Verification!')
      }
    }, error => {
      this.toastService.toastNotification1(this.global.apiError, 'Email Verification!')
    })
  }

  trackByFn(index: number, item: any): any {
    return item.id;
  }

  activity() {
    this.trackAdmin.storeActivity('Staff', `Enter in Staff Page`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }
}
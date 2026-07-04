import { GlobalVariable } from "app/shared/global";
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit } from "@angular/core";
import { ModalDismissReasons } from "@ng-bootstrap/ng-bootstrap";
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";
import { StaffService } from "app/services/staff.service";
import { ToastServiceService } from "app/services/toast-service.service";
import { HttpClient, HttpHeaders } from "@angular/common/http";
import { DateAdapter } from "@angular/material/core";
import moment from "moment";
import { NgxSpinnerService } from "ngx-spinner";
import { NgForm } from "@angular/forms";
import { PermissionsService } from "app/services/permissions.service";
import { DomSanitizer } from "@angular/platform-browser";
@Component({
  selector: "app-add-document",
  templateUrl: "./add-document.component.html",
  styleUrls: ["./add-document.component.scss"],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AddDocumentComponent implements OnInit {
  @Input() fromUserMenu;
  @Input() documentList;
  @Input() DocList;
  @Input() guardDocumentType;

  document_name;
  document_expire;
  notes;
  file;
  namePattern = /^[a-zA-Z\s]+$/;
  description = "";
  isNumber: boolean;
  isDocComplusory: boolean = false;
  @Input() public action;
  search;
  addDocumentButtonActive: boolean = false;
  fileToUpload: File[] = [];
  selectedfile: any;
  base64String: string;
  enableExpiryDate: boolean = false;
  document_expiry;
  roomsFilter: any;
  expiryDate = "";
  c_f_roster;
  c_f_profile;
  document_type;
  document_no;
  documentDetail: any;

  isCheckedNum;
  isCheckedExp;

  docNamesObj;
  pass;
  visa;
  ct;
  vc;
  sl;
  dlf;
  dlb;
  placeholderText: string = "Add Document Number";
  stafffPermissions: any;
  ipAddress: any;
  constructor(
    public activeModal: NgbActiveModal,
    public globals: GlobalVariable,
    private staffDoc: StaffService,
    private toast: ToastServiceService,
    private spinner: NgxSpinnerService,
    public dateAdapter: DateAdapter<Date>,
    private permissionService: PermissionsService,
    private sanitizer: DomSanitizer,
    private cdr: ChangeDetectorRef
  ) {
    const per = this.permissionService.getPermissionsByTitle("Onboarding");
    this.stafffPermissions = per?.childPage?.find(
      (item) => item.title === "Current Staff"
    );
    console.log(this.stafffPermissions);
    this.dateAdapter.setLocale("en-AU");
  }

  ngOnInit() {
    this.getIPAddress();
    if (this.documentList) {
      const {
        id,
        file,
        document_type,
        document_name,
        c_f_profile,
        c_f_roster,
        notes,
        document_no,
        document_expire,
      } = this.documentList;
      const data = { id };
      this.spinner.show();
      this.staffDoc.editGuardDoc(data).subscribe(
        ({ success, data }) => {
          if (success) {
            this.document_type = data.document_type;
            this.document_name = data.document_name;
            this.c_f_profile = data.c_f_profile === "1";
            this.c_f_roster = data.c_f_roster === "1";
            if (data.is_deleteable == 1) {
              this.document_type = "other";
            }
            if (data.document_type == "visa") {
              this.placeholderText = "Add Visa Grant Number";
            }

            this.notes = notes;

            this.isCheckedNum = !!document_no;
            this.document_no = document_no;

            this.isCheckedExp = !!document_expire;
            this.document_expire = this.isCheckedExp
              ? moment(document_expire, "DD-MM-YYYY").format()
              : null;
            if (document_expire == "current, pending renewal") {
              this.document_expire = document_expire;
            }

            if (
              this.documentList.file &&
              this.documentList.file.includes("pdf")
            ) {
              this.pdfView = true;
              this.isimage = false;
              this.previewUrl = this.sanitizer.bypassSecurityTrustResourceUrl(
                this.documentList.file
              );
              console.log("showing pdf");

              // this.previewUrl = this.documentList.file;
            } else {
              this.isimage = true;
              this.pdfView = false;
              this.imgView = this.documentList.file;
            }
            this.spinner.hide();
            this.cdr.markForCheck()

          }
        },
        (error) => {
          this.spinner.hide();
          this.toast.toastNotification1(
            "Something went wrong. Please try again later",
            "Request Incomplete!"
          );
        }
      );
    }

    this.cdr.markForCheck()
  }

  close(value?) {
    this.activeModal.close(value); //It closes successfully
  }

  // save form

  submitStaffDoc(addDocForm: NgForm) {
    if (addDocForm.invalid) {
      // Form is invalid, show error messages for all invalid fields
      Object.keys(addDocForm.controls).forEach((controlName) => {
        addDocForm.controls[controlName].markAsDirty();
        addDocForm.controls[controlName].markAsTouched();
      });
      return;
    }
    // debugger;
    const value = addDocForm.value;
    value.admin_id = this.globals.admin.admin_id;
    if (!this.previewUrl && !this.imgView) {
      this.toast.toastNotification1("Please Upload File", "Form Incomplete");
      this.isDocComplusory = true;
      return;
    }
    if (this.isCheckedNum && !this.document_no) {
      return;
    }
    if (
      this.isCheckedNum &&
      this.document_no &&
      value.document_type == "visa"
    ) {
      const documentNoValue = value.document_no;
      const isValidDocumentNo = /^\d{13}$/.test(documentNoValue);
      if (!isValidDocumentNo) {
        this.toast.toastNotification1(
          "Visa grant number must be 13 digits and without letters",
          "Form Incomplete"
        );
        return;
      }
    }
    if (this.document_expire && this.isCheckedExp) {
      value.document_expire = moment(this.document_expire).format("MM-DD-YYYY");
    }
    if (!this.document_expire && this.isCheckedExp) {
      this.toast.toastNotification1(
        "Expiration Date is required",
        "Form Incomplete"
      );
      return;
    }
    if (this.document_expire == "current, pending renewal") {
      value.document_expire = this.document_expire;
    }

    this.spinner.show();
    if (this.documentList) {
      value.id = this.documentList.id;
      value.guard_id = this.documentList.guard_id;
      value.document_type = this.documentList.document_type;
      if (this.document_type === "other") {
        value.document_name = this.document_name;
      } else if (this.document_type === "birth_certificate") {
        value.document_name = "Birth Certificate";
      } else if (this.document_type === "citizen_ship") {
        value.document_name = "Citizenship";
      } else if (this.document_type === "wwc") {
        value.document_name = "WWC";
      } else if (this.document_type === "rsa") {
        value.document_name = "RSA";
      } else if (this.document_type === "medicare") {
        value.document_name = "Medicare";
      } else if (this.document_type === "driver_license_back") {
        value.document_name = "Driver Licence Back";
      } else if (this.document_type === "driver_license_front") {
        value.document_name = "Driver Licence Front";
      } else if (this.document_type === "security_license") {
        value.document_name = "Security Licence";
      } else if (this.document_type === "vaccination") {
        value.document_name = "Vaccination";
      } else if (this.document_type === "passport") {
        value.document_name = "Passport";
      } else if (this.document_type === "visa") {
        value.document_name = "Visa";
      }
      else if (this.document_type === "application_form") {
        value.document_name = "Application Form"
      }
      else if (this.document_type === "casual_contract_form") {
        value.document_name = "Casual Contract Form"
      }
      else if (this.document_type === "part_time_contract_form") {
        value.document_name = "Part Time Contract Form"
      }
      else if (this.document_type === "sec_certidicate") {
        value.document_name = "Security Certificate"
      }
      else if (this.document_type === "work_with_child") {
        value.document_name = "Working with Children"
      }
      else {
        value.document_name = this.documentList.document_name;
      }
      // value.document_name = this.document_type == 'other' ? this.document_name : this.documentList.document_name
      value.file = this.file;
      this.staffDoc.staffUpdateDoc(value).subscribe(
        (res) => {
          if (res.success) {
            this.globals.documentViewEnable = false;
            let status = "Staff Operation!";
            this.toast.toastNotification(res.message, status);
            this.close("document");
            this.spinner.hide();
          }
        },
        (error) => {
          this.toast.toastNotification1(
            "Something went wrong",
            "Request Incomplete"
          );
          this.spinner.hide();
        }
      );

      this.cdr.markForCheck()

    } else {
      value.id = this.fromUserMenu.id;
      value.file = this.file;
      if (this.document_type === "other") {
        value.document_name = this.document_name;
      } else if (this.document_type === "birth_certificate") {
        value.document_name = "Birth Certificate";
      } else if (this.document_type === "citizen_ship") {
        value.document_name = "Citizenship";
      } else if (this.document_type === "wwc") {
        value.document_name = "WWC";
      } else if (this.document_type === "rsa") {
        value.document_name = "RSA";
      } else if (this.document_type === "medicare") {
        value.document_name = "Medicare";
      } else if (this.document_type === "driver_license_back") {
        value.document_name = "Driver Licence Back";
      } else if (this.document_type === "driver_license_front") {
        value.document_name = "Driver Licence Front";
      } else if (this.document_type === "security_license") {
        value.document_name = "Security Licence";
      } else if (this.document_type === "vaccination") {
        value.document_name = "Vaccination";
      } else if (this.document_type === "passport") {
        value.document_name = "Passport";
      } else if (this.document_type === "visa") {
        value.document_name = "Visa";
      } else if (this.document_type === "application_form") {
        value.document_name = "Application Form"
      } else if (this.document_type === "casual_contract_form") {
        value.document_name = "Casual Contract Form"
      } else if (this.document_type === "part_time_contract_form") {
        value.document_name = "Part Time Contract Form"
      }
      else {
        value.document_name =
          this.document_type === "other"
            ? this.document_name
            : this.documentList
              ? this.documentList.document_name
              : undefined;
      }
      this.staffDoc.staffAddDoc(value).subscribe(
        (res) => {
          if (res.success) {
            let status = "Staff Operation!";
            this.toast.toastNotification(res.message, status);
            this.close("document");
            this.spinner.hide();
          }
        },
        (error) => {
          this.toast.toastNotification1(
            error.error.message,
            "Request Incomplete"
          );
          this.spinner.hide();
        }
      );
      this.cdr.markForCheck()

    }
  }

  //  add document new
  previewUrl: any;
  pdfView: boolean = false;
  imgView: any;
  isimage = true;
  base64Strings = "";
  uploadFile: any;
  add() {
    let input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*,application/pdf";
    input.onchange = (_) => {
      let files = Array.from(input.files);
      if (!files || files.length === 0) return;
      this.uploadFile = files[0];
      const myFormData = new FormData();
      const headers = new HttpHeaders({
        AuthorizationToken: "Bearer " + localStorage.getItem("accessToken")
      });
      myFormData.append("file", this.uploadFile, this.uploadFile.name);
      myFormData.append("folder", "guard_documents");
      this.spinner.show();
      this.staffDoc
        .uploadImgPdf(myFormData, {
          headers: headers,
        })
        .subscribe(
          (response) => {
            if (response.success) {
              this.file = response.path;
              this.isDocComplusory = false;
              if (this.uploadFile.type.startsWith("image/")) {
                this.isimage = true;
                this.pdfView = false;
                this.previewUrl = "";
                this.imgView = this.sanitizer.bypassSecurityTrustResourceUrl(
                  response.url
                );
              } else if (this.uploadFile.type === "application/pdf") {
                this.pdfView = true;
                this.isimage = false;
                this.imgView = "";
                this.previewUrl = this.sanitizer.bypassSecurityTrustResourceUrl(
                  response.url
                );
              }
              this.spinner.hide();
              this.cdr.markForCheck()

            }
          },
          (error) => {
            this.toast.toastNotification1(
              "Something went wrong. Please try again later",
              "Request Incomplete!"
            );
            console.error(error);
            this.spinner.hide();
          }
        );
    };
    input.click();
  }

  // save created documents
  saveDocument() {
    console.log(this.documentDetail.id);
    console.log(this.selectedfile ? "true" : "false");
    this.globals.documentsList[this.documentDetail.id].base64 =
      this.selectedfile;
    this.close();
  }

  // download file
  downloadFile() {
    const downloadLink = document.createElement("a");
    downloadLink.href = this.documentList.file;
    downloadLink.download = this.documentList?.document_type ?? "document"; // Set the desired filename
    downloadLink.target = "_blank";
    downloadLink.click();
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return "by pressing ESC";
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return "by clicking on a backdrop";
    } else {
      return `with: ${reason}`;
    }
  }

  verifyLicense() {
    let Id;
    if (this.documentList) {
      Id = this.documentList.guard_id;
    } else {
      Id = this.fromUserMenu.id;
    }
    this.spinner.show();
    this.staffDoc.documentsOnlineVerification(Id, this.document_no).subscribe(
      ({ expiry, message, success }) => {
        if (success) {
          if (expiry == "current, pending renewal") {
            this.document_expire = expiry;
          } else {
            this.isCheckedExp = true;
            let exp = moment(expiry, "DD-MM-YYYY");
            this.document_expire = exp.format();
          }
          this.spinner.hide();
          this.toast.toastNotification(message, "Record Found!");
        } else if (!success) {
          this.spinner.hide();
          this.toast.toastNotification1(message, "Not Found");
          this.document_no = "";
          this.document_expire = "";
        }
      },
      (error) => {
        this.toast.toastNotification1(
          this.globals.apiError,
          "Request Incomplete"
        );
        this.spinner.hide();
      }
    );
  }

  getIPAddress() {
    this.getUserLocation();
  }
  getUserLocation() {
    this.staffDoc.getLocation().subscribe((res) => {
      this.ipAddress = res;
    });
  }


  checkEnableStatus() {
    if (this.globals.documentViewEnable) {
      return true
    }
    else if (this.document_type == 'security_license' && this.fromUserMenu.state === 'Victoria') {
      return true
    }
    else {
      return false
    }
  }

  delFile() {
    console.log(this.documentList);
    if (this.documentList.file) {
      let data = { path: this.documentList.file, id: this.documentList.id }
      this.staffDoc.delStaffDoc(data).subscribe(({ success, message }) => {
        if (success) {
          this.imgView = ''
          this.previewUrl = ''
          this.toast.toastNotification(message, "Staff Document!");
          this.cdr.markForCheck()
        }
      })
    }
  }
}

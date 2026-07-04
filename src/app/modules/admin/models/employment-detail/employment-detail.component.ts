import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";
import { ChangeDetectorRef, Component, HostListener, Input, OnInit} from "@angular/core";
import { MatDatepickerInputEvent } from "@angular/material/datepicker";
import moment from "moment";
import { DateAdapter } from "@angular/material/core";
import { AbstractControl, FormArray, FormBuilder, FormGroup, ValidationErrors, ValidatorFn, Validators } from "@angular/forms";
import { SiteService } from "app/services/site.service";
import { StaffService } from "app/services/staff.service";
import { CustomerService } from "app/services/customer.service";
import { ToastServiceService } from "app/services/toast-service.service";
import { HttpHeaders } from "@angular/common/http";
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from "@costlydeveloper/ngx-awesome-popup";
import { NgxSpinnerService } from "ngx-spinner";
import { TrackAdminActivityService } from "app/services/track-admin-activity.service";
import { PermissionsService } from "app/services/permissions.service";

@Component({
  selector: "app-employment-detail",
  templateUrl: "./employment-detail.component.html",
  styleUrls: ["./employment-detail.component.scss"],
})

export class EmploymentDetailComponent implements OnInit {
  states = [
    { name: "Victoria" },
    { name: "Queensland" },
    { name: "New South Wales" },
    { name: "South Australia" },
    { name: "ACT" },
    { name: "Tasmania" },
  ];
  payrate_level = [
    { value: 1 },
    { value: 2 },
    { value: 3 },
    { value: 4 },
    { value: 5 },
  ];
  tfnfileuploaded: boolean = false
  suppfileuploaded: boolean = false
  fileuploaded: boolean = false
  filelimituploaded: boolean = false
  suppFileName
  tfnFileName
  educational_letter_name
  limit_exceed_file_name
  payrates = [];
  @Input() fromParentUserMenu;
  @Input() fromParentEmployeeDetail;
  hiredDate: string;
  tabType: string = "Staff Type";
  srcResult: any;
  trainingList: any = [];
  sectionId;
  li;
  uploadStaffDocument: FormGroup;
  customerList: any;
  letter_from_educational_institute;
  letter_url
  tfn_file;
  superannutation_file;
  tfn_file_img: any;
  superannutation_file_img: any;
  letter_from_educational_institute_img: any;
  previewUrl: string;
  pdfView: boolean;
  educationalFile: any;
  uploadFile: any;
  indFileuploaded: any[] = [];
  routeId: any;
  stafffPermissions: any;

  constructor(private cdr: ChangeDetectorRef, private toast: ToastServiceService, private cutomerService: CustomerService,
    public cus: SiteService, public userService: StaffService, public ngbActiveModal: NgbActiveModal,
    public dateAdapter: DateAdapter<Date>, private fb: FormBuilder, private spinner: NgxSpinnerService,
    private trackAdmin: TrackAdminActivityService, private permissionService: PermissionsService) 
  {
    const per = this.permissionService.getPermissionsByTitle('Onboarding');
    this.stafffPermissions = per?.childPage?.find(item => item.title === 'Current Staff');
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
    this.dateAdapter.setLocale("en-AU");
    this.sectionId = document.querySelectorAll("section");
    this.getCustomer();
  }

  onScroll(event) {
    console.log(" aaa", event);
  }

  @HostListener("window:scroll", ["$event"]) 
  ngOnInit(): void {
    this.uploadStaffDocument = this.fb.group({
      guard_document_type: ["", Validators.required],
      id: [""],
      hired_on: ["", this.dateValidator()],
      job_level: [""],
      payrate_state: [""],
      payrate: [""],
      tfn_file: [""],
      tfn_file_no: ["", this.digitValidator],
      superannutation_file: [""],
      superannutation_no: ["", this.digitValidator],
      superannutation_name: [""],
      // abn_name: ["", this.alphabetValidator],
      abn_no: ["", this.digitValidator],
      bank_name: [""],
      bsb: [""],
      bank_account_no: [""],
      work_hours_limitation_status: [true],
      weekly_work_hours_limitation: [""],
      authorized: [""],
      authorized_by: [""],
      letter_from_educational_institute: [""],
      letter_url: [""],
      other_name: [""],
      account_holder: [""],
      induction: this.fb.array([]),
      limit_exceed: [false],          
      start_time: [null],
      end_time:   [null],
      otp: [""]
    });
    if (this.fromParentEmployeeDetail) {
      this.getPayrate(this.fromParentEmployeeDetail?.job_level)
      this.uploadStaffDocument
        .get("guard_document_type")
        .setValue(this.fromParentEmployeeDetail?.guard_document_type);
      this.uploadStaffDocument
        .get("other_name")
        .setValue(this.fromParentEmployeeDetail?.other_name);
      this.uploadStaffDocument
        .get("payrate_state")
        .setValue(this.fromParentEmployeeDetail?.payrate_state);
      this.uploadStaffDocument
        .get("job_level")
        .setValue(parseInt(this.fromParentEmployeeDetail?.job_level));
      this.uploadStaffDocument
        .get("payrate")
        .setValue(parseInt(this.fromParentEmployeeDetail?.payrate));
      this.uploadStaffDocument
        .get("bank_account_no")
        .setValue(this.fromParentEmployeeDetail?.bank_account_no);
      this.uploadStaffDocument
        .get("bank_name")
        .setValue(this.fromParentEmployeeDetail?.bank_name);
      this.uploadStaffDocument
        .get("bsb")
        .setValue(this.fromParentEmployeeDetail?.bsb);
      this.uploadStaffDocument
        .get("abn_no")
        .setValue(this.fromParentEmployeeDetail?.abn_no);
      // this.uploadStaffDocument
      //   .get("abn_name")
      //   .setValue(this.fromParentEmployeeDetail?.abn_name);
      this.uploadStaffDocument
        .get("tfn_file_no")
        .setValue(this.fromParentEmployeeDetail?.tfn_file_no);
      if (this.fromParentEmployeeDetail?.tfn_file) {
        const extension = this.fromParentEmployeeDetail?.tfn_file.split(".").pop().toLowerCase();
        this.tfn_file_img = this.fromParentEmployeeDetail.tfn_file;
        // Check if it's a PDF
        if (extension === "pdf") {
          this.tfnfileuploaded = true
          this.tfnFileName = 'TFN File'
        } else {
          this.tfn_file = this.fromParentEmployeeDetail?.tfn_file;
        }
        this.uploadStaffDocument
          .get("tfn_file")
          .setValue(this.fromParentEmployeeDetail?.tfn_file);
      }

      if (this.fromParentEmployeeDetail?.superannutation_file) {
        const extension = this.fromParentEmployeeDetail?.superannutation_file.split(".").pop().toLowerCase();
        this.superannutation_file = this.fromParentEmployeeDetail.superannutation_file;
        // Check if it's a PDF
        if (extension === "pdf") {
          this.suppfileuploaded = true
          this.suppFileName = 'Superannutation File'
        } else {
          this.superannutation_file =
            this.fromParentEmployeeDetail?.superannutation_file;
        }
        this.uploadStaffDocument
          .get("superannutation_file")
          .setValue(this.fromParentEmployeeDetail?.superannutation_file);
      }

      this.uploadStaffDocument
        .get("superannutation_no")
        .setValue(this.fromParentEmployeeDetail?.superannutation_no);
      this.uploadStaffDocument
        .get("superannutation_name")
        .setValue(this.fromParentEmployeeDetail?.superannutation_name);
      this.uploadStaffDocument
        .get("account_holder")
        .setValue(this.fromParentEmployeeDetail?.account_holder);
      this.uploadStaffDocument
        .get("work_hours_limitation_status")
        .setValue(this.fromParentEmployeeDetail?.work_hours_limitation_status);
      this.uploadStaffDocument
        .get("weekly_work_hours_limitation")
        .setValue(this.fromParentEmployeeDetail?.weekly_work_hours_limitation);
      this.uploadStaffDocument
        .get("authorized")
        .setValue(this.fromParentEmployeeDetail?.authorized);
      if (this.fromParentEmployeeDetail?.letter_from_educational_institute) {
        this.fileuploaded = true
        this.educational_letter_name = 'Educational Letter'
      }
      let start = moment(this.fromParentEmployeeDetail.hired_on, "DD-MM-YYYY");
      this.uploadStaffDocument.get("hired_on").setValue(start.format());
      if (this.fromParentEmployeeDetail?.induction) {
        this.fromParentEmployeeDetail?.induction.forEach((element, index) => {
          this.getSelectedValue(element.customer.id, index)
          this.uploadStaffDocument.value.induction.site = element.site;
          if (element.induction_file) {
            const extension = element.induction_file.split(".").pop().toLowerCase();
            if (extension === "pdf") {
              this.indFileuploaded[index] = true;
              this.indFileName = 'Induction File';
            } else {
              this.indFileuploaded[index] = false;
            }
          }
          this.induction.push(
            this.fb.group({
              id: [element.id],
              induction_file: [element.induction_file],
              customer_site_status: [element.customer_site_status],
              customer: [element.customer.id],
              site: [element.site.id],
              my_path: [element.induction_file],
            })
          );
        });
      }
      const exceed = this.fromParentEmployeeDetail.limit_exceed;
      this.uploadStaffDocument.patchValue({
        limit_exceed: !!exceed,
        start_time:    this.parseApiDate(this.fromParentEmployeeDetail.start_time),
        end_time:      this.parseApiDate(this.fromParentEmployeeDetail.end_time),
      });
    }
  }

  private parseApiDate(dateStr: string | null | undefined): Date | null {
  if (!dateStr) return null;

  // API returns "2026-01-20" or similar
  const parts = dateStr.split('-');
  if (parts.length !== 3) return null;

  const year  = Number(parts[0]);
  const month = Number(parts[1]) - 1; // JS months are 0-based
  const day   = Number(parts[2]);

  const d = new Date(year, month, day);
  return isNaN(d.getTime()) ? null : d;
}

  getCustomer() {
    this.cus.getCustomer().subscribe(
      ({ success, data }) => {
        if (success) {
          this.customerList = data;
        }
      },
      (error) => {
        console.log(error);
      }
    );
  }

  activeMenu() {
    let num = Number(130);
    let length = this.sectionId.length;
    while (
      --length &&
      window.scrollY + 130 < this.sectionId[length].offsetTop
    ) { }
    this.li.forEach((element) => {
      element.classList.remove("active");
    });
    this.li[length].classList.add("active");
  }

  activeTab;
  selectedValue;

  close(data?) {
    this.ngbActiveModal.dismiss(data);
  }

  tabChanged(tabChangeEvent) {
    console.log("tabChangeEvent => ", tabChangeEvent);
    console.log("index => ", tabChangeEvent.index);
    this.activeTab = tabChangeEvent.index;
  }

  addEvent(type: string, event: MatDatepickerInputEvent<Date>) {
    console.log("value ", event.value);

    console.log(moment(event.value).format("DD/MM/YYYY"));
    this.hiredDate = moment(event.value).format("DD/MM/YYYY");
    console.log("date ", this.hiredDate);
  }

  getTabType(selected: string, el: HTMLElement) {
    this.tabType = selected;
    el.scrollIntoView();
  }

  onFileSelected() {
    const inputNode: any = document.querySelector("#file");

    if (typeof FileReader !== "undefined") {
      const reader = new FileReader();

      reader.onload = (e: any) => {
        this.srcResult = e.target.result;
      };

      reader.readAsArrayBuffer(inputNode.files[0]);
    }
  }

  get induction(): FormArray {
    return this.uploadStaffDocument.get("induction") as FormArray;
  }
  addTraining() {
    this.induction.push(
      this.fb.group({
        id: [""],
        customer: [""],
        induction_file: [""],
        customer_site_status: [""],
        my_path: [""],
        site: [""],
        loading: false,
      })
    );
  }
  // remove Text Area
  removeTextArea(value, index) {
    if (value.id) {
      const newConfirmBox = new ConfirmBoxInitializer();
      newConfirmBox.setTitle('Confirm Action');
      newConfirmBox.setMessage('Are you sure you want to confirm this action?');
      newConfirmBox.setConfig({
        layoutType: DialogLayoutDisplay.SUCCESS, // SUCCESS | INFO | NONE | DANGER | WARNING
        animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
        animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
        allowHtmlMessage: true,
        buttonPosition: 'right', // optional 
      });
      newConfirmBox.openConfirmBox$().subscribe(resp => {
        if (resp.success) {
          this.userService.delInduction(value.id).subscribe(({ success, message }) => {
            if (success) {
              (this.uploadStaffDocument.get("induction") as FormArray).removeAt(index);
              let status = 'Staff Induction!'
              this.toast.toastNotification1(message, status)
            }
            else {
              let status = 'Request Incomplete!'
              this.toast.toastNotification1('Something went wrong', status)
            }

          });
        }
      });
    }
    else {
      (this.uploadStaffDocument.get("induction") as FormArray).removeAt(index);
    }

  }

  // alphabetValidator(control: AbstractControl): ValidationErrors | null {
  //   const value = control.value;
  //   if (value && !/^[a-zA-Z\s]*$/.test(value)) {
  //     return { alphabet: true };
  //   }
  //   return null;
  // }

  digitValidator(control: AbstractControl): ValidationErrors | null {
    const value = control.value;
    if (value && !/^\d*$/.test(value)) {
      return { digit: true };
    }
    return null;
  }

  sixDigitValidator(control: AbstractControl): ValidationErrors | null {
    const value = control.value;
    if (value && !/^\d{6}$/.test(value)) {
      return { sixDigit: true };
    }
    return null;
  }
  //////////save User Employment details//////
  submitted = false;
  onFormSubmit(value) {
    this.submitted = true;
    Object.values(this.uploadStaffDocument.controls).forEach(control => control.markAsTouched());

    if (this.uploadStaffDocument.invalid) {
      this.toast.toastNotification1('Please check error on field and fill this field', 'Invalid Form!')
      return
    }
    if (value.induction && value.induction.some(ind => ind.site === null)) {
      this.toast.toastNotification1('Error: One or more inductions have a missing site.', 'Error');
      return;
    }

    if (this.uploadStaffDocument.value.guard_document_type == 'other' && !this.uploadStaffDocument.value.other_name) {
      this.toast.toastNotification1('Please write your Residential Status', 'Error');
      return;
    }
    if (this.tfn_file_img) {
      value.tfn_file = this.tfn_file_img;
    }
    if (this.superannutation_file_img) {
      value.superannutation_file = this.superannutation_file_img;
    }
    if (this.letter_from_educational_institute_img) {
      value.letter_from_educational_institute = this.letter_from_educational_institute_img;
    }
    if (this.fromParentUserMenu) {
      value.id = this.fromParentUserMenu.id;
    }
    const consoleData = {
    ...value,
    limit_exceed: this.uploadStaffDocument.get('limit_exceed')?.value ? 1 : 0,
    start_time: this.formatDateForConsole('start_time'),
    end_time:   this.formatDateForConsole('end_time'),
  };
  console.log("Value", consoleData)
    this.spinner.show()
    this.userService.uploadStaffDocument(consoleData).subscribe(
      (res) => {
        if (res.success) {
          this.trackAdmin.storeActivity('Employement Page', `Updated Staff`, this.routeId).subscribe(res => {
          })
          let status = "Staff Operation";
          this.toast.toastNotification(res.message, status);
          this.close("QuickStaff");
          this.spinner.hide()
        }
        else {
          this.toast.toastNotification1(res.message, 'Request Incomplete!')
          this.spinner.hide()
        }
      },
      (error) => {
        console.log(error);
      }
    );
  }

  siteList: any = [];
  /////get selected customer value and get site from api
  getSelectedValue(id, index) {
    if (id) {
      // this.uploadStaffDocument.value.induction[i].customer = id
      this.userService.getCusSite(id).subscribe(
        ({ success, data }) => {
          if (success) {
            if (data.length != 0) {
              this.siteList[index] = data;
            }
            else {
              this.uploadStaffDocument.value.induction[index].site = id
              this.siteList[index] = data;
            }
          }
        },
        (error) => {
          console.log(error);
        }
      );
    }
  }

  onStatusChange(value, i) {
    if (value) {
      this.uploadStaffDocument.value.induction[i].customer_site_status = value;
    }
  }
  ///////get Pay rate of against level
  payrate_level_id: any;
  state: any;
  getPayrate(value) {
    this.payrate_level_id = value;
    this.uploadStaffDocument.get('payrate').setValue('')
    this.payRates();
  }
  sendState(value) {
    this.state = value;
    this.payRates();
  }

  payRates() {
    this.userService.getPayrate(this.payrate_level_id, this.state).subscribe(
      (res) => {
        if (res.success) {
          this.payrates = res.data;
        }
      },
      (error) => {
        console.log(error);
      }
    );
  }
  ///////upload image////////
  base64String: any;
  induction_file;
  indFileName
  uploadImage(index) {
    this.uploadStaffDocument.value.induction[index].loading = true;

    let input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*,application/pdf";
    input.onchange = (_) => {
      let files = Array.from(input.files);
      if (!files || files.length === 0) return;
      this.uploadFile = files[0];
      const myFormData = new FormData();
      const headers = new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      })
      myFormData.append('file', this.uploadFile, this.uploadFile.name);
      myFormData.append('folder', 'guard_employment_details')
      this.cutomerService.uploadImgPdf(myFormData, {
        headers: headers
      }).subscribe(
        response => {
          this.uploadStaffDocument.value.induction[index].loading = false;
          if (response.success) {
            const extension = response.url.split(".").pop().toLowerCase();
            if (extension === "pdf") {
              this.indFileuploaded[index] = true
              this.indFileName = 'File Uploaded'
            } else {
              this.indFileuploaded[index] = false
            }
            this.uploadStaffDocument.value.induction[index].induction_file =
              response.path;
            this.uploadStaffDocument.value.induction[index].my_path = response.url;
            console.log(this.uploadStaffDocument.value);
          }
        },
        (error) => {
          this.uploadStaffDocument.value.induction[index].loading = false;
          console.error(error);
        }
      );

    };
    input.click();
    // console.log(this.uploadStaffDocument.value.induction);
    // this.base64String = "";
    // let input = document.createElement("input");
    // input.type = "file";
    // input.accept = "image/*";
    // input.onchange = (_) => {
    //   let files = Array.from(input.files);
    //   const file = files[0];
    //   const reader = new FileReader();
    //   reader.onloadend = () => {
    //     this.base64String = (<string>reader.result).split(",")[1];
    //     this.induction_file = "data:image/jpeg;base64," + this.base64String;
    //     this.cutomerService
    //       .uploadImage(this.base64String, "guard_employment_details")
    //       .subscribe(
    //         (res) => {
    //           console.log(res);
    //           if (res.success == true) {
    //             this.uploadStaffDocument.value.induction[index].induction_file =
    //               res.path;
    //             this.uploadStaffDocument.value.induction[index].my_path =
    //               this.induction_file;
    //           }
    //         },
    //         (error) => {
    //           console.log(error);
    //         }
    //       );
    //   };
    //   reader.readAsDataURL(file);
    // };
    // input.click();
  }

  // upload pdf and images
  uploadFileUrl

  tfnLoading: boolean = false;
  suppLoading: boolean = false;
  uploadImages(type) {
    if (type === 'tfn') {
      this.tfnLoading = true;
    } else if (type === 'supp') {
      this.suppLoading = true;
    }
    let input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*,application/pdf";
    input.onchange = (_) => {
      let files = Array.from(input.files);
      if (!files || files.length === 0) return;
      this.uploadFile = files[0];
      const myFormData = new FormData();
      const headers = new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      })
      myFormData.append('file', this.uploadFile, this.uploadFile.name);
      myFormData.append('folder', 'guard_employment_details')
      this.cutomerService.uploadImgPdf(myFormData, {
        headers: headers
      }).subscribe(
        response => {
          if (response.success) {
            const extension = response.url.split(".").pop().toLowerCase();
            if (type == 'supp') {
              if (extension === "pdf") {
                this.suppfileuploaded = true
                this.suppFileName = 'File Uploaded'
              } else {
                this.suppfileuploaded = false
              }
              this.superannutation_file_img = response.url;
              this.superannutation_file = response.url;
              this.suppLoading = false;
            }
            else if (type == 'tfn') {
              this.tfnLoading = true;
              // Check if it's a PDF
              if (extension === "pdf") {
                this.tfnfileuploaded = true
                this.tfnFileName = 'File Uploaded'
              } else {
                this.tfnfileuploaded = false
              }
              this.tfn_file_img = response.url;
              this.tfn_file = response.url;
              this.tfnLoading = false;
            }
          }
        },
        (error) => {
          console.error(error);
          this.tfnLoading = false;
          this.suppLoading = false;
        }
      );

    };
    input.click();
  }



  // educatinol file upload

  uploadEducationalFiles(event) {
    this.uploadFile = event.target.files[0];
    const myFormData = new FormData();
    const headers = new HttpHeaders({
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    })
    myFormData.append("file", this.uploadFile, this.uploadFile.name);
    myFormData.append("folder", "guard_employment_details");
    this.cutomerService
      .uploadImgPdf(myFormData, {
        headers: headers,
      })
      .subscribe(
        (response) => {
          if (response.success) {
            this.uploadStaffDocument.get("letter_from_educational_institute").setValue(response.url);
            this.educationalFile = response.url;
            this.letter_from_educational_institute_img = response.url;
          } else {
            this.toast.toastNotification1(
              "Something went wrong please check your file size or connection",
              "File Upload!"
            );
          }
        },
        (error) => {
          console.error(error);
        }
      );
  }

  uploadLimitFile(event) {
    this.uploadFile = event.target.files[0];
    const myFormData = new FormData();
    const headers = new HttpHeaders({
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    })
    myFormData.append("file", this.uploadFile, this.uploadFile.name);
    myFormData.append("folder", "guard_employment_details");
    this.cutomerService
      .uploadImgPdf(myFormData, {
        headers: headers,
      })
      .subscribe(
        (response) => {
          if (response.success) {
            this.uploadStaffDocument.get("letter_url").setValue(response.url);
            this.educationalFile = response.url;
            this.letter_url = response.url;
          } else {
            this.toast.toastNotification1(
              "Something went wrong please check your file size or connection",
              "File Upload!"
            );
          }
        },
        (error) => {
          console.error(error);
        }
      );
  }

  openPdf(file) {
    if (file) {
      window.open(file, '_blank');
    }
    else {
      return
    }

  }

  removeImg(name, i?, input?) {
    if (name == 'tfn') {
      this.tfn_file = ''
    }
    else if (name == 'supp') {
      this.superannutation_file = '';
    }
    else {
      this.uploadStaffDocument.value.induction[i].induction_file = '';
      this.uploadStaffDocument.value.induction[i].my_path = '';
    }
  }

  ////chaeck work limitation toggle
  isChecked = false;
  onToggleChange(event) {
    this.isChecked = event.checked;
    this.cdr.detectChanges();
    if (this.isChecked) {
      let user = JSON.parse(localStorage.getItem("admin"));
      this.uploadStaffDocument.get("authorized").setValue(user.admin_name);
      this.uploadStaffDocument.get("authorized_by").setValue(user.admin_id);
    } else {
      this.uploadStaffDocument.get("authorized").setValue("");
      this.uploadStaffDocument.get("authorized_by").setValue("");
    }
  }


  viewFile() {
    window.open(this.fromParentEmployeeDetail.letter_from_educational_institute, '_blank')
  }
  viewExceedFile() {
    window.open(this.fromParentEmployeeDetail.letter_url, '_blank')
  }
  viewFiles(file) {
    window.open(file, '_blank')
  }

  handleFileInput(type) {
    if (type == 'tfn') {
      this.tfnfileuploaded = false
    }
    else {
      this.suppfileuploaded = false
    }
  }

  handleFileInputs(i) {
    this.indFileuploaded[i] = false
  }
  ngOnDestroy() {
    this.trackAdmin.storeActivity('Employement Page', 'Exit Staff Employment Page', this.routeId).subscribe(res => {
    })
    localStorage.removeItem('routerId');
  }
  activity() {
    this.trackAdmin.storeActivity('Employement Page', 'Enter in Staff Employment Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  dateValidator(): ValidatorFn {
    return (control: AbstractControl): { [key: string]: any } | null => {
      const selectedDate = control.value;
      const currentDate = new Date();
      if (selectedDate && selectedDate > currentDate) {
        return { dateInvalid: true };
      }
      return null;
    };
  }

  private formatDateForConsole(controlName: string): string | null {
    const date = this.uploadStaffDocument.get(controlName)?.value;
    if (!date) return null;

    if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
      return date;
    }

    const d = new Date(date);
    if (isNaN(d.getTime())) return null;

    const year  = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day   = String(d.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
  }

  onExceedCheckboxChange(checked: boolean) {
    if (!checked) {
      this.uploadStaffDocument.patchValue({
        start_time: null,
        end_time:   null,
        otp: null,
      });
    }
  }
}

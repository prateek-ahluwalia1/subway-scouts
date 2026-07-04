import {
  Component,
  OnInit,
  ViewChild,
  ElementRef,
  Renderer2,
  Input,
  HostListener,
  ChangeDetectorRef,
} from "@angular/core";
import {
  FormBuilder,
  FormControl,
  FormGroup,
  Validators,
  AbstractControl,
  FormArray,
  ValidatorFn,
  ValidationErrors,
} from "@angular/forms";
import { NgbActiveModal, NgbModal } from "@ng-bootstrap/ng-bootstrap";
import moment from "moment";
import { SiteService } from "app/services/site.service";
import { Subject } from "rxjs";
import { MatTableDataSource } from "@angular/material/table";
import { SelectionModel } from "@angular/cdk/collections";
import { CustomerService } from "app/services/customer.service";
import { ToastServiceService } from "app/services/toast-service.service";
import { MatDatepickerInputEvent } from "@angular/material/datepicker";
import { DateAdapter } from "@angular/material/core";
import { MatSort } from "@angular/material/sort";
import { HttpHeaders } from "@angular/common/http";
import { GlobalVariable } from "app/shared/global";
import { TrackAdminActivityService } from "app/services/track-admin-activity.service";
import { NgxSpinnerService } from "ngx-spinner";
import { ButtonLayoutDisplay, ButtonMaker, DialogInitializer, DialogLayoutDisplay } from "@costlydeveloper/ngx-awesome-popup";
import { PayChargeHistoryComponent } from "app/modules/admin/models/pay-charge-history/pay-charge-history.component";
import { CustomeLoaderComponent } from "app/modules/admin/custome-loader/custome-loader.component";

export interface PeriodicElement {
  id: number;
  site_name: string;
  description: string;
  action: string;
}
declare var google;

const ELEMENT_DATA: PeriodicElement[] = [];
export class Customers {
  id: number;
  name: string;
}

@Component({
  selector: 'app-site-form',
  templateUrl: './site-form.component.html',
  styleUrls: ['./site-form.component.scss'],
})
export class SiteFormComponent implements OnInit {

  @Input() fromParent;
  @Input() routeId;
  @Input() patrolsiteId;

  selectedCardIndex: number = 0;
  selectedCardIndex$: Subject<number> = new Subject<number>();
  hideSiteTrack: boolean = false;
  searchTerm;
  dates;
  customer_id;
  siteEdit: any = [];
  displayedColumns: string[] = [
    "id",
    "site_name",
    "site_description",
    "action",
  ];
  customer: any = [];
  dataSource = new MatTableDataSource<PeriodicElement>(ELEMENT_DATA);
  selection = new SelectionModel<PeriodicElement>(true, []);
  @ViewChild(MatSort) sort: MatSort;
  toppings = new FormControl("");
  formFieldHelpers: string[] = [""];
  placeholder = "";
  siteType: string[] = ["Active", "Inactive"];
  autocompleteItems: any;
  autocomplete: any;
  temp: boolean = false;
  break_yes_no = false;
  break_ded_pay = false;
  break_ded_charg = false;
  walfare_call_type = false;
  green_call_type = false;
  first_green_call_boolean = false;
  second_green_call_boolean = false;
  new_site_section = false;
  isShowMap = false;
  signin_radius;
  radius_alert;
  date = new FormControl(new Date());
  serializedDate = new FormControl(new Date().toISOString());
  @ViewChild("map") mapElement: ElementRef;
  // lat = -37.8136;
  // lng = 144.9631;
  lat;
  lng;
  portal_setting;
  scanners_site_id: any[] = [];
  fileuploaded: boolean = false;
  alarm_dispatch: boolean = false;
  shift_file_name;
  alarm_dispatch_file_name;
  uploadFileUrl;
  selectedValue: string = "active";
  customers: Customers[] = [];
  protected _onDestroy = new Subject<void>();
  mycoordinates: string;
  lattitude: any;
  longitude: any;
  geo: any;
  addressSearch: boolean;
  // service: any;
  searchControl = new FormControl("");
  businessId;
  currentDate = new Date();
  minDate
  chargeRate;
  payRate;
  greenCallControl = new FormControl("no");
  AddCustomrSite: FormGroup;
  AddUpdateReason: FormGroup;
  submitted = false;
  modalRef;
  elementId;
  formattedAddress = "";
  map: any;
  options = {
    componentRestrictions: {
      country: ["AU"],
    },
  };
  inputList: any = [];
  uploadFile: any;
  sitetrack: any[] = [];
  showFullContent = false;
  uploadAlarmDispatch: any;
  qrCodeData: string[] = [];
  elementType = "url";
  randomId;
  siteId;
  uniqueId;
  @ViewChild("qrCodeImg", { static: false }) qrCodeImg: ElementRef;
  qrCodeImageAvailable = false;
  uploadScanFile: any;
  uploadKeyFile: any;
  scanFileName;
  keyFileName;
  scanner_file;
  key_file;

  //it is used to prevent page refresh if this modal is open
  @HostListener("window:beforeunload", ["$event"])
  beforeUnloadHandler(event: Event): void {
    if (this.patrolsiteId) {
      let sitedata = {
        id: this.patrolsiteId,
        reason: "",
        admin_id: "",
      };
      event.preventDefault();
      this.siteService.delSite(sitedata).subscribe(({ success }) => {
        if (success) {
          this.modalService.dismiss();
          this.fileuploaded = false;
          this.alarm_dispatch = false;
        }
      });
    }
  }

  constructor(public modalService: NgbActiveModal, private siteService: SiteService, private modal: NgbModal,
    private fb: FormBuilder, private toast: ToastServiceService, private cusService: CustomerService,
    public dateAdapter: DateAdapter<Date>, public renderer2: Renderer2,
    private global: GlobalVariable, private trackAdmin: TrackAdminActivityService,
    private spinner: NgxSpinnerService, private cdr: ChangeDetectorRef) {


    this.dateAdapter.setLocale('en-AU');
    this.autocompleteItems = [];
    this.autocomplete = {
      query: "",
    };

    let routerId = localStorage.getItem("routerId");
    if (!routerId) {
      this.activity();
    }
    this.minDate = new Date();

    const storedData = localStorage.getItem("business");
    if (storedData) {
      const parsedData = JSON.parse(storedData);
      this.businessId = parsedData.id;
    }
  }

  sideCards = [
    {
      text: "Branch Information",
      logo: "../../../../../assets/icons/createSite/Signpost.png",
      background: "#BFFFFB",
      color: " #027069",
    },
    // {
    //   text: "Branch",
    //   logo: "../../../../../assets/icons/createSite/placeholder 1.png",
    //   background: "#BFD1F3",
    //   color: " #4B689F",
    // },
    // {
    //   text: "Charge and Payrates",
    //   logo: "../../../../../assets/icons/createSite/wallet.png",
    //   background: "#F2CCC0",
    //   color: " #C20606",
    // },
    // {
    //   text: "Break",
    //   logo: "../../../../../assets/icons/jobshiftActivity/Group.png",
    //   background: "#FFFCCA",
    //   color: " #9C8928",
    // },
    // {
    //   text: "Task",
    //   logo: "../../../../../assets/icons/createSite/Group.png",
    //   background: "#D6D6D6",
    //   color: " #767676",
    // },
    // {
    //   text: "Welfare and Green Call",
    //   logo: "../../../../../assets/icons/createSite/operator 1.png",
    //   background: "#BBF5B1",
    //   color: " #105652",
    // },
    // {
    //   text: "Patrolling",
    //   logo: "../../../../../assets/icons/createSite/Group764.png",
    //   background: "#D7BDE2",
    //   color: " #512E5F",
    // },
  ];

  // lastObject = {
  //   text: "Location Tracker",
  //   logo: "../../../../../assets/icons/createSite/sitetracker.png",
  //   background: "#F5F5F5",
  //   color: " #767676",
  // };

  borderColors: string[] = [
    "3px solid #027069",
    "3px solid #4B689F",
    "3px solid #C20606",
    "3px solid #9C8928",
    "3px solid #767676",
    "3px solid #105652",
    "3px solid #767676",
    "3px solid #512E5F",
    "3px solid #027069",
  ];

  CallsNumber = [
    { name: "1", value: "1" },
    { name: "2", value: "2" },
    { name: "3", value: "3" },
    { name: "4", value: "4" },
    { name: "5", value: "5" },
  ];

  sitetype = [
    { name: "Internal", value: "internal" },
    { name: "External", value: "external" },
  ];

  ngOnInit(): void {
    // if (this.fromParent) {
    //   var type = typeof this.fromParent;
    //   if (type == "number") {
    //     this.sideCards.push(this.lastObject);
    //   }
    // }

    this.AddUpdateReason = this.fb.group({
      reason: new FormControl("", Validators.required),
    });
    this.AddCustomrSite = this.fb.group({
      customer_id: new FormControl("", Validators.required),
      // site_type: new FormControl("direct", Validators.required),
      site_budget: new FormControl(""),
      site_name: new FormControl("", [Validators.required]),
      site_description: new FormControl(""),
      // site_start_date: new FormControl("", Validators.required),
      site_end_date: new FormControl(""),
      job_instrcutions: new FormControl(""),
      staff_type: new FormControl("1"),
      adhoc_shift: new FormControl(""),
      unpublished_site: new FormControl("no"),
      // site_trained: new FormControl("", Validators.required),
      // sos_phone: new FormControl("", [
      //   Validators.required,
      //   this.onlyDigitsValidator(),
      // ]),
      sos_phone: new FormControl(""),
      site_state: new FormControl("", Validators.required),
      site_hours: new FormControl(""),
      po_wo: new FormControl(""),
      address: new FormControl(""),
      coordinates: new FormControl(""),
      signin_radius: new FormControl("", Validators.required),
      radius_alert: new FormControl("", Validators.required),
      // site_level: new FormControl("", Validators.required),
      // payrol: new FormControl("default", Validators.required),
      // site_payrate_level: new FormControl("", Validators.required),
      // site_payrate: new FormControl("", Validators.required),
      // site_chargerate_level: new FormControl(""),
      // site_charge_rate: new FormControl(""),
      // site_break: new FormControl("", Validators.required),
      site_break_payable: new FormControl(""),
      site_break_chargeable: new FormControl(""),
      break_deduction_payable: new FormControl(""),
      break_deduction_chargeable: new FormControl(""),
      welfare_call: new FormControl("no"),
      welfare_call_type: new FormControl(""),
      welfare_timing: new FormControl(""),
      green_call: new FormControl("no"),
      first_green_call: new FormControl(""),
      second_green_call: new FormControl(""),
      first_green_call_time: new FormControl(""),
      second_green_call_time: new FormControl(""),
      job_instruction_file: new FormControl(""),
      site_update_reason: new FormControl(""),
      type: new FormControl("metro"),
      site_tasks: this.fb.array([]),

      is_patrolling_site: new FormControl(false),
      is_alarm_patrol_site: new FormControl(false),
      monitoring_person: new FormControl(""),
      monitoring_contact: new FormControl(""),
      after_hours: new FormControl(""),
      internal_patrolling: new FormControl(false),
      external_patrolling: new FormControl(false),
      intermediate_patrolling: new FormControl(false),
      scanners: this.fb.array([]),
      keys: this.fb.array([]),
      alarm_dispatch_instruction: new FormControl(''),
      intern_no_calls: new FormControl(''),
      extern_no_calls: new FormControl(''),
      intermed_no_calls: new FormControl(''),
      intern_time_type: new FormControl(''),
      extern_time_type: new FormControl(''),
      intermed_time_type: new FormControl(''),
      payrate_affective_from: new FormControl(this.currentDate),
      chargerate_affective_from: new FormControl(this.currentDate),
      // payrate_affective_to: new FormControl(''),
      // chargerate_affective_to: new FormControl(''),
      dateSelectionOfPay: new FormControl("same_day"),
      dateSelectionOfCharge: new FormControl("same_day"),
      intern_particular_times: this.fb.array([]),
      extern_particular_times: this.fb.array([]),
      intermed_particular_times: this.fb.array([]),
      alarm_panels: this.fb.array([]),
    });

    this.signin_radius = 100;
    this.radius_alert = 100;
    this.siteService.getCustomer().subscribe((res) => {
      if (res.success == true) {
        this.customer = res.data;
        this.customers = res.data;
      }
    });

    // this.AddCustomrSite.get("site_payrate_level").valueChanges.subscribe(
    //   (value) => {
    //     this.siteService.getPayRate(value).subscribe(
    //       ({ success, data }) => {
    //         if (success) {
    //           this.payRate = data;
    //         }
    //       },
    //       (error) => {
    //         console.error(error);
    //       }
    //     );
    //   }
    // );

    // this.AddCustomrSite.get("site_chargerate_level").valueChanges.subscribe(
    //   (value) => {
    //     this.siteService.getChargeRate(value).subscribe(
    //       ({ success, data }) => {
    //         if (success) {
    //           this.chargeRate = data;
    //         }
    //       },
    //       (error) => {
    //         console.error(error);
    //       }
    //     );
    //   }
    // );

    console.log(this.fromParent, this.patrolsiteId);

    if (this.fromParent || this.patrolsiteId) {
      const parameterToPass = this.fromParent || this.patrolsiteId;
      this.editSite(parameterToPass);
    }

    this.AddCustomrSite.get("intern_no_calls").valueChanges.subscribe(
      (value) => {
        this.generateTimePickerFields(value);
      }
    );

    this.AddCustomrSite.get("extern_no_calls").valueChanges.subscribe(
      (value) => {
        this.generateTimePickerFields1(value);
      }
    );

    this.AddCustomrSite.get("intermed_no_calls").valueChanges.subscribe(
      (value) => {
        this.generateTimePickerFields2(value);
      }
    );

    this.cdr.markForCheck();
  }

  generateTimePickerFields(noOfCalls: number): void {
    const particularTimesArray = this.AddCustomrSite.get(
      "intern_particular_times"
    ) as FormArray;
    particularTimesArray.clear();
    for (let i = 0; i < noOfCalls; i++) {
      particularTimesArray.push(
        this.fb.group({
          particular_time: new FormControl(""),
        })
      );
    }
  }

  get internParticularTimes(): FormArray {
    return this.AddCustomrSite.get("intern_particular_times") as FormArray;
  }

  generateTimePickerFields1(noOfCalls: number): void {
    const exparticularTimesArray = this.AddCustomrSite.get(
      "extern_particular_times"
    ) as FormArray;
    exparticularTimesArray.clear();

    for (let i = 0; i < noOfCalls; i++) {
      exparticularTimesArray.push(
        this.fb.group({
          extern_particular_time: new FormControl(""),
        })
      );
    }
  }

  get externParticularTimes(): FormArray {
    return this.AddCustomrSite.get("extern_particular_times") as FormArray;
  }

  generateTimePickerFields2(noOfCalls: number): void {
    const intermedparticularTimesArray = this.AddCustomrSite.get(
      "intermed_particular_times"
    ) as FormArray;
    intermedparticularTimesArray.clear();

    for (let i = 0; i < noOfCalls; i++) {
      intermedparticularTimesArray.push(
        this.fb.group({
          intermed_particular_time: new FormControl(""),
        })
      );
    }
  }

  get intermedParticularTimes(): FormArray {
    return this.AddCustomrSite.get("intermed_particular_times") as FormArray;
  }

  onlyDigitsValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value: string = control.value || "";

      if (!value) {
        return null;
      }
      const pattern = /^\d+$/;

      if (!pattern.test(value)) {
        return { onlyDigits: true };
      }

      return null;
    };
  }

  submitReason(id, content) {
    if (this.fromParent) {
      this.elementId = id;
      this.modalRef = this.modal.open(content, { centered: true });
    } else if (this.patrolsiteId) {
      this.submitCustomerSite();
    } else {
    }
  }

  submitCustomerSite() {
    this.spinner.show();
    const reasonValue = this.AddUpdateReason.get("reason").value;
    this.AddCustomrSite.get("site_update_reason").setValue(reasonValue);
    // if (
    //   !this.AddCustomrSite.value.site_start_date ||
    //   this.AddCustomrSite.value.site_start_date == null ||
    //   this.AddCustomrSite.value.site_start_date == "Invalid date"
    // ) {
    //   this.AddCustomrSite.value.site_start_date = "";
    // } else {
    //   this.AddCustomrSite.value.site_start_date = moment(
    //     this.AddCustomrSite.value.site_start_date
    //   ).format("MM-DD-YYYY");
    // }

    if (
      !this.AddCustomrSite.value.site_end_date ||
      this.AddCustomrSite.value.site_end_date == null ||
      this.AddCustomrSite.value.site_end_date == "Invalid date"
    ) {
      this.AddCustomrSite.value.site_end_date = "";
    } else {
      this.AddCustomrSite.value.site_end_date = moment(
        this.AddCustomrSite.value.site_end_date
      ).format("MM-DD-YYYY");
    }

    this.submitted = true;
    if (this.AddCustomrSite.invalid) {
      this.spinner.hide();
      this.toast.toastNotification1(
        "Some fields are required please check tabs and fill data",
        "Invalid Form"
      );
      return;
    } 
    else {
      if (this.customer_id) {
        this.AddCustomrSite.value.id = this.customer_id;
      }
      this.AddCustomrSite.value.admin_id = this.global.admin.admin_id;
      this.siteService.addSite(this.AddCustomrSite.value).subscribe(
        (res) => {
          const status = "Location Operation!";
          if (res.success) {
            this.trackAdmin
              .storeActivity(
                "Location",
                this.customer_id
                  ? "Update Location"
                  : `Create Location with name ${this.AddCustomrSite.value.site_name}`,
                this.routeId
              )
              .subscribe(() => {
                this.new_site_section = false;
                this.modal.dismissAll("update");
                this.toast.toastNotification(res.message, status);
              });
          } else {
            this.toast.toastNotification(res.message, status);
          }
          this.spinner.hide();
        },
        () => {
          this.spinner.hide();
          this.toast.toastNotification(
            "Something went wrong. Please contact the support team.",
            "Request Incomplete!"
          );
        }
      );
    }
  }

  get f(): { [key: string]: AbstractControl } {
    return this.AddCustomrSite.controls;
  }

  public handleAddressChange(address: any) {
    let adminAreaLevel1Name = address.address_components.find((component) =>
      component.types.includes("administrative_area_level_1")
    )?.long_name;
    if (adminAreaLevel1Name) {
      if (adminAreaLevel1Name === this.AddCustomrSite.value?.site_state) {
        this.formattedAddress = address.formatted_address;
        this.AddCustomrSite.get("address").setValue(this.formattedAddress);
        this.lat = address.geometry.location.lat();
        this.lng = address.geometry.location.lng();
        var coordinate = this.lat + "," + this.lng;
        this.AddCustomrSite.get("coordinates").setValue(coordinate);
        this.isShowMap = true;
        setTimeout(() => this.initMap(), 0);
      } else {
        this.AddCustomrSite.get("address").setValue("");
        this.AddCustomrSite.get("coordinates").setValue("");
        this.toast.toastNotification1(
          "Current Address does not belong to the selected state",
          "Location Error!"
        );
      }
    } else {
      this.toast.toastNotification1(
        "Administrative area level 1 not found in address",
        "Location Error!"
      );
    }
  }

  breakYesNot(status: boolean) {
    this.break_yes_no = status;
  }

  breakchargPay(check: "payno" | "payyes" | "chargyes" | string): void {
    switch (check) {
      case "payno":
        this.break_ded_pay = false;
        break;
      case "payyes":
        this.break_ded_pay = true;
        break;
      case "chargyes":
        this.break_ded_charg = true;
        break;
      default:
        this.break_ded_charg = false;
        break;
    }
  }

  get site_tasks(): FormArray {
    return this.AddCustomrSite.get("site_tasks") as FormArray;
  }

  
  addNewTask() {
    this.site_tasks.push(
      this.fb.group({
        task: [""],
      })
    );
  }

  removeQuantity(i: number) {
    (this.AddCustomrSite.get("site_tasks") as FormArray).removeAt(i);
  }

  welfareYesNot(status: boolean) {
    this.walfare_call_type = status;
  }

  greenCallYesNot(status: boolean): void {
    this.green_call_type = status;
  }

  firstSecondGreenCall(status: boolean, type) {
    if (type == "first") {
      this.first_green_call_boolean = status;
    } else {
      this.second_green_call_boolean = status;
    }
  }

  initMap() {
    if (!this.mapElement || !this.mapElement.nativeElement) {
      return;
    }

    let coords = new google.maps.LatLng(this.lat, this.lng);

    let mapOptions = {
      center: coords,
      zoom: 15,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
    };
    this.map = new google.maps.Map(this.mapElement.nativeElement, mapOptions);

    let marker1 = new google.maps.Marker({
      map: this.map,
      position: coords,
      draggable: true,
      title: "Location Location",
    });

    var circle = new google.maps.Circle({
      map: this.map,
      radius: this.signin_radius, // 10 miles in metres
      fillColor: "#AA0000",
    });
    circle.bindTo("center", marker1, "position");

    var circle2 = new google.maps.Circle({
      map: this.map,
      radius: this.radius_alert, // 10 miles in metres
      fillColor: "#01A37E",
    });
    circle2.bindTo("center", marker1, "position");

    google.maps.event.addListener(marker1, "dragend", (res) => {
      this.lat = res.latLng.lat();
      this.lng = res.latLng.lng();
      let latLng = res.latLng.lat() + "," + res.latLng.lng();
      this.AddCustomrSite.get("coordinates").setValue(latLng);
      const latlng = {
        lat: parseFloat(res.latLng.lat()),
        lng: parseFloat(res.latLng.lng()),
      };
      var geocoder = new google.maps.Geocoder();
      geocoder.geocode({ location: latlng }, (results) => {
        this.AddCustomrSite.get("address").setValue(
          results[0].formatted_address
        );
      });
    });
  }

  getRadius(type, radius: any) {
    if (type == "signin") {
      var sign_radius = Number(radius.target.value);
      this.signin_radius = sign_radius;
      setTimeout(() => this.initMap(), 0);

    } else {
      var radius_alert = Number(radius.target.value);
      this.radius_alert = radius_alert;
      setTimeout(() => this.initMap(), 0);

    }
  }
  
  uploadFiles(event) {
    this.uploadFile = event.target.files[0];
    const myFormData = new FormData();
    const headers = new HttpHeaders({AuthorizationToken: "Bearer " + localStorage.getItem("accessToken")});
    myFormData.append("file", this.uploadFile, this.uploadFile.name);
    myFormData.append("folder", "site");
    this.cusService.uploadImgPdf(myFormData, { headers: headers,}).subscribe(
      (response) => {
        if (response.success) {
          this.AddCustomrSite.get("job_instruction_file").setValue(response.path);
        } else {
          this.toast.toastNotification1("Something went wrong please check your file size or connection","File Upload!");
        }
      },
      (error) => {
        console.error(error);
      }
    );
  }

  viewFile() {
    window.open(this.uploadFileUrl, "_blank");
  }

  handleFileInput() {
    this.fileuploaded = false;
    this.alarm_dispatch = false;
    // let input = document.createElement("input");
    // input.type = "file";
    // input.accept = "image/*,application/pdf";

    // input.onchange = (_) => {
    //   let files = Array.from(input.files);
    //   if (!files || files.length === 0) return;
    //   this.uploadFile = files[0];
    //   const myFormData = new FormData();
    //   const headers = new HttpHeaders();
    //   headers.append('Content-Type', 'multipart/form-data');
    //   headers.append('Accept', 'application/json');
    //   myFormData.append('file', this.uploadFile, this.uploadFile.name);
    //   myFormData.append('folder', 'site')
    //   this.cusService.uploadImgPdf(myFormData, {
    //     headers: headers
    //   }).subscribe(
    //     response => {
    //       if (response.success) {
    //         this.AddCustomrSite.get('job_instruction_file').setValue(response.path)
    //         this.fileuploaded = true
    //         this.shift_file_name = response.path
    //       }
    //       else {
    //         this.toast.toastNotification1('Something went wrong please check your file size or connection', 'File Upload!')
    //       }
    //     },
    //     (error) => {
    //       console.error(error);
    //     }
    //   );

    // };
    // input.click();
  }

  // addEvent(event: MatDatepickerInputEvent<Date>) {
  //   this.dates = moment(event.value).format("DD/MM/YYYY");
  //   this.AddCustomrSite.get("site_start_date").setValue(this.dates);
  // }

  editSite(id) {
    this.spinner.show();
    this.siteService.getSpecificSite(id).subscribe(({ success, data, site_traker }) => {
      if (success) {
        this.new_site_section = true;
        (this.customer_id = data.id),
        this.AddCustomrSite.get("customer_id").setValue(data.customer_id);
        // this.AddCustomrSite.get("site_type").setValue(data.site_type);
        this.AddCustomrSite.get("site_name").setValue(data.site_name);
        this.AddCustomrSite.get("site_budget").setValue(data.site_budget);
        this.AddCustomrSite.get("site_description").setValue(data.site_description);
        this.AddCustomrSite.get("type").setValue(data.type);
        if (data.end) {
          let end = moment(data?.end, "DD-MM-YYYY");
          this.AddCustomrSite.get("site_end_date").setValue(end.format());
        }
        let start = moment(data.start, "DD-MM-YYYY");
        // this.AddCustomrSite.get("site_start_date").setValue(start.format());
        this.AddCustomrSite.get("job_instrcutions").setValue(data.job_instrcutions);
        this.AddCustomrSite.get("staff_type").setValue(data.staff_type);
        this.AddCustomrSite.get("site_hours").setValue(data.site_hours);
        this.AddCustomrSite.get("po_wo").setValue(data.po_wo);
        // this.AddCustomrSite.get("site_trained").setValue(data.trained);
        this.AddCustomrSite.get("site_state").setValue(data.state);
        this.AddCustomrSite.get("sos_phone").setValue(data.sos_phone);
        this.AddCustomrSite.get("address").setValue(data.address);
        this.AddCustomrSite.get("coordinates").setValue(data.coordinates);
        this.AddCustomrSite.get("signin_radius").setValue(data.signin_radius);
        this.AddCustomrSite.get("radius_alert").setValue(data.alert_radius);
        // this.AddCustomrSite.get("site_level").setValue(data.level);
        // this.AddCustomrSite.get("payrol").setValue(data.payrol);
        this.AddCustomrSite.get("dateSelectionOfPay").setValue(data.dateSelectionOfPay);
        this.AddCustomrSite.get("dateSelectionOfCharge").setValue(data.dateSelectionOfCharge);
        this.AddCustomrSite.get("payrate_affective_from").setValue(data?.payrate_affective_from);
        this.AddCustomrSite.get("chargerate_affective_from").setValue(data?.chargerate_affective_from);
        const sitePayrateLevelControl = this.AddCustomrSite.get("site_payrate_level");
        if (sitePayrateLevelControl) {
          sitePayrateLevelControl.setValue(data.site_payrate_level?.toString());
        }
        const siteChargerateLevelControl = this.AddCustomrSite.get("site_chargerate_level");
        if (siteChargerateLevelControl) {
          siteChargerateLevelControl.setValue(data.site_chargerate_level?.toString());
        }
        // this.AddCustomrSite.get("site_payrate").setValue(data.site_payrate);
        // this.AddCustomrSite.get("site_charge_rate").setValue(data.site_charge_rate);
        // this.AddCustomrSite.get("site_break").setValue(data.site_break);
        // this.AddCustomrSite.get("site_break_payable").setValue(data.break_payable);
        this.AddCustomrSite.get("break_deduction_payable").setValue(data.break_deduction_payable);
        this.AddCustomrSite.get("site_break_chargeable").setValue(data.break_chargeable);
        this.AddCustomrSite.get("break_deduction_chargeable").setValue(data.break_deduction_chargeable);
        this.AddCustomrSite.get("welfare_call").setValue(data.welfare_call);
        this.AddCustomrSite.get("welfare_call_type").setValue(data.welfare_call_type);
        this.AddCustomrSite.get("welfare_timing").setValue(data.welfare_timing);
        this.AddCustomrSite.get("green_call").setValue(data.green_call);
        this.AddCustomrSite.get("first_green_call").setValue(data.first_green_call);
        this.AddCustomrSite.get("second_green_call").setValue(data.second_green_call);
        this.AddCustomrSite.get("job_instruction_file").setValue(data.job_instruction_file);
        this.AddCustomrSite.get("first_green_call_time").setValue(data.first_green_call_time);
        this.AddCustomrSite.get("second_green_call_time").setValue(data.second_green_call_time);
        this.AddCustomrSite.get("unpublished_site").setValue(data.unpublished_site);
        this.AddCustomrSite.get("adhoc_shift").setValue(data.adhoc_shift);
        this.AddCustomrSite.get("alarm_dispatch_instruction").setValue(data.alarm_dispatch_instruction);
        // this.AddCustomrSite.get('site_update_reason').setValue(site_update_reason)
        // this.picker1.writeValue(data.start);
        if (data.job_instruction_file) {
          this.fileuploaded = true;
          this.shift_file_name = "View Uploaded File";
          this.uploadFileUrl = data.job_instruction_file;
        }
        if (data.site_break == "yes") {
          this.break_yes_no = true;
        } else {
          this.break_yes_no = false;
        }
        if (data.break_payable == "no") {
          this.break_ded_pay = true;
        }
        if (data.break_chargeable == "yes") {
          this.break_ded_charg = true;
        }
        if (data.welfare_call == "yes") {
          this.walfare_call_type = true;
        }
        if (data.green_call == "yes") {
          this.green_call_type = true;
        }
        if (data.first_green_call == "yes") {
          this.first_green_call_boolean = true;
        }
        if (data.second_green_call == "yes") {
          this.second_green_call_boolean = true;
        }
        if (data?.address && data.coordinates) {
          const [lat, lng] = data.coordinates.split(",");
          if (lat && lng) {
            this.lat = lat.trim();
            this.lng = lng.trim();
          }
        }
        if (data.alarm_dispatch_instruction) {
          this.alarm_dispatch = true;
          this.alarm_dispatch_file_name = "View Uploaded File";
          this.uploadFileUrl = data.alarm_dispatch_instruction;
        }
        this.AddCustomrSite.get("is_patrolling_site").setValue(data.is_patrolling_site);
        this.showFullContent = data.is_patrolling_site;
        this.AddCustomrSite.get("monitoring_person").setValue(data.monitoring_person);
        this.AddCustomrSite.get("monitoring_contact").setValue(data.monitoring_contact);
        this.AddCustomrSite.get("after_hours").setValue(data.after_hours);
        this.AddCustomrSite.get("is_alarm_patrol_site").setValue(data?.is_alarm_patrol_site);
        this.AddCustomrSite.get("internal_patrolling").setValue(data?.internal_patrolling);
        this.AddCustomrSite.get("external_patrolling").setValue(data?.external_patrolling);
        this.AddCustomrSite.get("intermediate_patrolling").setValue(data?.intermediate_patrolling);
        this.AddCustomrSite.get("intern_no_calls").setValue(data?.intern_no_calls);
        this.AddCustomrSite.get("extern_no_calls").setValue(data?.extern_no_calls);
        this.AddCustomrSite.get("intermed_no_calls").setValue(data?.intermed_no_calls);
        this.AddCustomrSite.get("intern_time_type").setValue(data?.intern_time_type);
        this.AddCustomrSite.get("extern_time_type").setValue(data?.extern_time_type);
        this.AddCustomrSite.get("intermed_time_type").setValue(data?.intermed_time_type);
        this.spinner.hide();
      } 
      else {
        this.toast.toastNotification1("Something went wrong or data not found. Please try again later", "Request Incomplete!");
        this.spinner.hide();
      }
      if (data.site_tasks) {
        data.site_tasks.forEach((task) => {
          this.site_tasks.push(
            this.fb.group({
              task: task.task,
            })
          );
        });
      }
      if (data.scanners) {
        data.scanners.forEach((scan) => {
          this.scanners.push(
            this.fb.group({
              name: scan.name,
              location: scan.location,
              id: scan.id,
              unique_key: scan.unique_key,
              scan_path: scan.scan_path,
              scanner_file: scan.scanner_file,
            })
          );
        });
      }
      if (data.keys) {
        data.keys.forEach((key) => {
          this.keys.push(
            this.fb.group({
              key_name: key.key_name,
              key_number: key.key_number,
              key_file: key.key_file,
              key_path: key.key_path,
            })
          );
        });
      }
      if (data.alarm_panels) {
        data.alarm_panels.forEach((panel) => {
          this.alarm_panels.push(
            this.fb.group({
              alarm_panel_codes: panel.alarm_panel_codes,
            })
          );
        });
      }
      if (data.intern_particular_times) {
        const particularTimesArray = this.AddCustomrSite.get("intern_particular_times") as FormArray;
        particularTimesArray.clear();
        data.intern_particular_times.forEach((part_time) => {
          particularTimesArray.push(
            this.fb.group({
              particular_time: new FormControl(part_time.particular_time),
            })
          );
        });
      }
      if (data.extern_particular_times) {
        const particularTimesArray = this.AddCustomrSite.get("extern_particular_times") as FormArray;
        particularTimesArray.clear();
        data.extern_particular_times.forEach((part_time) => {
          particularTimesArray.push(
            this.fb.group({
              extern_particular_time: new FormControl(
                part_time.extern_particular_time
              ),
            })
          );
        });
      }
      if (data.intermed_particular_times) {
        const particularTimesArray = this.AddCustomrSite.get("intermed_particular_times") as FormArray;
        particularTimesArray.clear();
        data.intermed_particular_times.forEach((part_time) => {
          particularTimesArray.push(
            this.fb.group({
              intermed_particular_time: new FormControl(
                part_time.intermed_particular_time
              ),
            })
          );
        });
      }
      this.scanners_site_id = data.scanners;
      this.sitetrack = site_traker;
    },
    () => {
      this.toast.toastNotification1("Something went wrong. Please contact with support team.", "Request Incomplete!");
      this.spinner.hide();
    });
  }

  close(data?) {
    if (this.patrolsiteId) {
      let sitedata = {
        id: this.patrolsiteId,
        reason: "",
        admin_id: "",
      };
      this.siteService.delSite(sitedata).subscribe(({ success }) => {
        if (success) {
          this.modalService.dismiss(data);
          this.fileuploaded = false;
          this.alarm_dispatch = false;
          // this.toast.toastNotification1(message, 'Site Operation!')
        }
      });
    } else if (this.fromParent) {
      this.modalService.dismiss(data);
      this.fileuploaded = false;
      this.alarm_dispatch = false;
    } else {
      this.modalService.dismiss(data);
      this.fileuploaded = false;
      this.alarm_dispatch = false;
    }
  }

  selectCard(index: number) {
    this.selectedCardIndex = index;
    if (index === 1) {
      this.isShowMap = true;
      const coordinates = this.AddCustomrSite.get('coordinates').value;
      if (coordinates) {
        const [lat, lng] = coordinates.split(',').map(Number);
        console.log(lat, lng);

        if (lat && lng) {
          setTimeout(() => this.initMap(), 0);
        } else {
          setTimeout(() => this.initMap(), 0);
        }
      }
    }
  }

  nextStep() {
    this.selectedCardIndex += 1;
  }

  backStep() {
    this.selectedCardIndex -= 1;
  }

  previousStep() {
    if (this.selectedCardIndex != 0) {
      this.selectedCardIndex = this.selectedCardIndex - 1;
    }
  }

  isFieldError(submitted: boolean, formControl: AbstractControl, sideCardText: string): boolean {
    return (
      submitted &&
      formControl.errors &&
      (sideCardText === "Location Information" ||
        sideCardText === "Location" ||
        sideCardText === "Charge and Payrates" ||
        sideCardText === "Break"
      )
    );
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity("Create Site", "Exit create site model", this.routeId).subscribe(() => { });
    localStorage.removeItem("routerId");
  }

  activity() {
    this.trackAdmin.storeActivity("Create Site", "Open create site model").subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem("routerId", id);
        this.routeId = id;
      }
    });
  }

  toggleFullContent(checked: boolean) {
    this.showFullContent = checked;
  }

  get scanners() {
    return this.AddCustomrSite.get("scanners") as FormArray;
  }

  AddScanner() {
    const randomId = this.generateRandomString(16);
    this.scanners.push(
      this.fb.group({
        name: [""],
        location: [""],
        id: [randomId],
        scanner_file: [""],
        scan_path: [""],
        loading: false,
        unique_key: [randomId],
      })
    );
  }

  get keys() {
    return this.AddCustomrSite.get("keys") as FormArray;
  }

  AddKey() {
    this.keys.push(
      this.fb.group({
        key_name: [""],
        key_number: [""],
        key_file: [""],
        key_path: [""],
        loading: false,
      })
    );
  }

  get alarm_panels() {
    return this.AddCustomrSite.get("alarm_panels") as FormArray;
  }

  AddPanel() {
    this.alarm_panels.push(
      this.fb.group({
        alarm_panel_codes: [""],
      })
    );
  }

  AddTime() {
    this.calltime.push(
      this.fb.group({
        time: [""],
      })
    );
  }

  get calltime() {
    return this.AddCustomrSite.get("calltime") as FormArray;
  }

  Random() {
    return Math.floor(Math.random() * 1000000000);
  }

  randomValue(index) {
    const keyFormGroup = this.keys.at(index) as FormGroup;
    const randomNumber = this.Random();
    keyFormGroup.get("key_number").setValue(randomNumber);
  }

  uploadAlarmFile(event) {
    this.uploadAlarmDispatch = event.target.files[0];
    const myFormData = new FormData();
    const headers = new HttpHeaders({AuthorizationToken: "Bearer " + localStorage.getItem("accessToken")});
    myFormData.append("file", this.uploadAlarmDispatch, this.uploadAlarmDispatch.name);
    myFormData.append("folder", "Alarm_Dispatch_Instruction");
    this.cusService.uploadImgPdf(myFormData, {headers: headers,}).subscribe(
      (response) => {
        if (response.success) {
          this.AddCustomrSite.get("alarm_dispatch_instruction").setValue(response.url);
        } else {
          this.toast.toastNotification1("Something went wrong please check your file size or connection", "File Upload!");
        }
      },
      (error) => {
        console.error(error);
      }
    );
  }

  generateRandomString(length: number): string {
    const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    let result = "";
    for (let i = 0; i < length; i++) {
      const randomIndex = Math.floor(Math.random() * characters.length);
      result += characters.charAt(randomIndex);
    }
    return result;
  }

  isInputEmpty(index: number): boolean {
    return !(this.qrCodeData[index]?.trim().length > 0);
  }

  ngAfterViewInit() {
    this.qrCodeImageAvailable = true;
  }


  generateURL(index: number): string {
    if (index >= 0 && index < this.scanners_site_id.length) {
      // this.siteId = this.scanners_site_id[index].site_id;
      this.uniqueId = this.scanners_site_id[index].unique_key;
    }
    // return `https://apis.thescouts.com.au/api/${this.uniqueId}/${this.siteId}/${this.businessId}`;
    return `${this.uniqueId}`;
  }

  uploadImage(name, index) {
    if (name === "scan") {
      this.AddCustomrSite.value.scanners[index].loading = true;
      let input = document.createElement("input");
      input.type = "file";
      input.accept = "image/*";
      input.onchange = (_) => {
        let files = Array.from(input.files);
        if (!files || files.length === 0) return;
        const myFormData = new FormData();
        const headers = new HttpHeaders({ AuthorizationToken: "Bearer " + localStorage.getItem("accessToken")});
        this.uploadScanFile = files[0];
        myFormData.append("file", this.uploadScanFile, this.uploadScanFile.name);
        myFormData.append("folder", "Scanner Details");
        this.cusService.uploadImgPdf(myFormData, { headers: headers,}).subscribe(
          (response) => {
            this.AddCustomrSite.value.scanners[index].loading = false;
            this.cdr.markForCheck();
            if (response.success) {
              this.AddCustomrSite.value.scanners[index].scanner_file = response.path;
              this.AddCustomrSite.value.scanners[index].scan_path = response.url;
            }
          },
          (error) => {
            this.AddCustomrSite.value.scanners[index].loading = false;
            this.cdr.markForCheck();
            console.error(error);
          }
        );
      };
      input.click();
    } else {
      this.AddCustomrSite.value.keys[index].loading = true;
      let input = document.createElement("input");
      input.type = "file";
      input.accept = "image/*";
      input.onchange = (_) => {
        let files = Array.from(input.files);
        if (!files || files.length === 0) return;
        const myFormData = new FormData();
        const headers = new HttpHeaders({AuthorizationToken: "Bearer " + localStorage.getItem("accessToken")});
        this.uploadKeyFile = files[0];
        myFormData.append("file", this.uploadKeyFile, this.uploadKeyFile.name);
        myFormData.append("folder", "Key Details");
        this.cusService.uploadImgPdf(myFormData, {headers: headers,}).subscribe(
          (response) => {
            this.cdr.markForCheck();
            this.AddCustomrSite.value.keys[index].loading = false;
            if (response.success) {
              this.AddCustomrSite.value.keys[index].key_file = response.path;
              this.AddCustomrSite.value.keys[index].key_path = response.url;
            }
          },
          (error) => {
            this.cdr.markForCheck();
            this.AddCustomrSite.value.keys[index].loading = false;
            console.error(error);
          }
        );
      };
      input.click();
    }
  }

  removeImg(name, i?) {
    if (name === "scan") {
      this.AddCustomrSite.value.scanners[i].scanner_file = "";
      this.AddCustomrSite.value.scanners[i].scan_path = "";
    } else {
      this.AddCustomrSite.value.keys[i].key_file = "";
      this.AddCustomrSite.value.keys[i].key_path = "";
    }
  }

  viewFiles(file, name) {
    if (name === "key") {
      window.open(file, "_blank");
    } else {
      window.open(file, "_blank");
    }
  }

  getOrdinalSuffix(number: number): string {
    const suffixes = ["th", "st", "nd", "rd"];
    const v = number % 100;
    return number + (suffixes[(v - 20) % 10] || suffixes[v] || suffixes[0]);
  }

  removeScanner(i: number) {
    (this.AddCustomrSite.get("scanners") as FormArray).removeAt(i);
  }

  removeKey(i: number) {
    (this.AddCustomrSite.get("keys") as FormArray).removeAt(i);
  }

  removeAlarmPanel(i: number) {
    (this.AddCustomrSite.get("alarm_panels") as FormArray).removeAt(i);
  }

  payRateHistory() {
    const dialogPopup = new DialogInitializer(PayChargeHistoryComponent);
    dialogPopup.setConfig({ width: "800px", layoutType: DialogLayoutDisplay.INFO,});
    dialogPopup.setCustomData({ type: "pay", site_id: this.fromParent });
    dialogPopup.setConfig({width: "800px", loaderComponent: CustomeLoaderComponent, layoutType: DialogLayoutDisplay.NONE,});
    dialogPopup.setButtons([
      new ButtonMaker("Close", "close", ButtonLayoutDisplay.DARK),
    ]);
    dialogPopup.openDialog$().subscribe((resp) => {
      console.log("dialog response: ", resp);
    });
  }

  chargeRateHistory() {
    const dialogPopup = new DialogInitializer(PayChargeHistoryComponent);
    dialogPopup.setConfig({width: "800px", layoutType: DialogLayoutDisplay.INFO,});
    dialogPopup.setCustomData({ type: "chargeRate", site_id: this.fromParent });
    dialogPopup.setConfig({ width: "800px", loaderComponent: CustomeLoaderComponent, layoutType: DialogLayoutDisplay.NONE });
    dialogPopup.setButtons([
      new ButtonMaker("Close", "close", ButtonLayoutDisplay.DARK),
    ]);
    dialogPopup.openDialog$().subscribe((resp) => {
      console.log("dialog response: ", resp);
    });
  }

}


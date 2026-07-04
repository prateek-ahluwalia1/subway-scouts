import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  OnInit,
  ViewChild,
} from "@angular/core";
import { DateAdapter } from "@angular/material/core";
import { ModalDismissReasons, NgbModal } from "@ng-bootstrap/ng-bootstrap";
import { CustomerService } from "app/services/customer.service";
import { FormGroup, FormBuilder, NgForm, Validators } from "@angular/forms";
import { StaffService } from "app/services/staff.service";
import moment from "moment";
import { TrackAdminActivityService } from "app/services/track-admin-activity.service";
import { AdminService } from "app/services/admin.service";
import { AuthService } from "app/core/auth/auth.service";
import { DatePipe } from "@angular/common";
import { ToastServiceService } from "app/services/toast-service.service";
import { FuseAlertType } from "@fuse/components/alert";
import { MatRadioChange } from "@angular/material/radio";
import { ModalInvoiceComponent } from "../../modal-invoice/modal-invoice.component";
import * as customBuild from "../../../../shared/ck-editor-module/ckCustomBuild/build/ckEditor";
import { TDocumentDefinitions } from "pdfmake/interfaces";
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
import { trigger, transition, style,query, animate, stagger } from "@angular/animations";

pdfMake.vfs = pdfFonts.pdfMake.vfs;
export class Customers {
  id: number;
  name: string;
  email: string;
  abn: string;
  phone: string;
}

@Component({
  selector: "app-invoice",
  templateUrl: "./invoice.component.html",
  styleUrls: ["./invoice.component.scss"],
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('500ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('500ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ],  
  changeDetection: ChangeDetectionStrategy.OnPush,
})

export class InvoiceComponent implements OnInit {

  public Editor = customBuild;

  config = {
    fontSize: {
      options: [9, 11, 13, 14, 15, 16, 17, 19, 21],
    },
    toolbar: ["heading", "|", "bold", "italic", "bulletedList", "numberedList"],
    resize_maxHeight: 600,
  };
  selectedCustomer;
  fromMultiCustomer;
  admin_data: any;
  business_id: any;
  customersIds: any;
  customers: Customers[] = [];
  siteData: any = [];
  roasterHoursList: any[] = [];
  detailed_invoice_data: any[] = [];
  ratesObject: any = {};
  range: FormGroup;
  name: string;
  email: string;
  phone: string;
  abn: Number;
  invoiceDescriptionFrom: string;
  invoiceDescriptionTo: string;
  grand_total = 0;
  detail_grand = 0;
  userInfo: any;
  id;
  type;
  data;
  routeId;
  param_id: any;
  showDateRange: boolean = false;
  totalPrice = 0;
  subtotal = 0;
  dynamicRows: any[] = [];
  searchPerformed: boolean = false;
  editingMode: boolean = true;
  admin: any;
  apikeys: any;
  paymentMethods: any[] = [];
  lateFeesToggleOn: boolean = false;
  lateFeesValue: number = 0.0;
  showNotes: boolean = false;
  additionalNotes: string = "";
  selectedCustomerData: any[] = [];
  // currencyOptions = ["AUD", "USD", "GBP", "JPY"];
  currencyOptions = ["AUD"];
  selectedCurrency = "AUD";
  isSearched: boolean = false;
  invoice_no: string = "";
  invoiceType: string = "";
  gstValue: number = 10.0;
  gstToggleChecked: boolean = true;
  gstValueDisplay: string = "%";
  // notesTextarea: HTMLTextAreaElement | undefined;
  @ViewChild("notesTextarea") notesTextarea: any;
  submitted: boolean = false;
  isSend: boolean = false;
  isPreview: boolean = false;
  isDonwload: boolean = false;
  alert: { type: FuseAlertType; message: string } = {
    type: "error",
    message: "Search is Required!",
  };
  searchRequired: boolean = false;
  // isAbnError: boolean = true;
  isAbnValid: boolean = true;
  eidtRowIndex: number;
  showPaymentMethods: boolean = false;
  isPaymentMethodOn: boolean = true;
  selectedPaymentOption: string = "";
  isEmailError = false;
  iscustEmailError = false;
  isAdminName: boolean = true;
  iscustName: boolean = true;
  abn_company: any;
  phone_company: any;
  businessData: any;
  due_date: Date;
  isdueDate = false;
  currentDate: string;
  constructor(
    private adminservice: AdminService,
    private formBuilder: FormBuilder,
    public dateAdapter: DateAdapter<Date>,
    private modalService: NgbModal,
    private cus: CustomerService,
    private invoice: StaffService,
    public userData: AuthService,
    private trackAdmin: TrackAdminActivityService,
    private datePipe: DatePipe,
    private toast: ToastServiceService,
    private cdr: ChangeDetectorRef
  ) {
    localStorage.removeItem("formData");
    this.dateAdapter.setLocale("en-AU");

    this.range = this.formBuilder.group({
      start: ["", Validators.required],
      end: ["", Validators.required],
    });
    this.range.valueChanges.subscribe(() => {
      this.searchPerformed = false;
      this.detailed_invoice_data = [];
      this.roasterHoursList = [];
    });

    let routerId = localStorage.getItem("routerId");
    if (!routerId) {
      this.activity();
    }

    this.generateInvoiceNumber();
    const adminDataFromLocalStorage = localStorage.getItem("admin");

    if (adminDataFromLocalStorage) {
      const admin = JSON.parse(adminDataFromLocalStorage);
    
      if (admin.apiKeys) {
        if (typeof admin.apiKeys === 'object') {
          this.paymentMethods = Object.entries(admin.apiKeys).map(
            ([key, value]) => ({ key, value })
          );
        } else {
          console.warn("apiKeys property is not an object in admin data.");
        }
      } else {
        console.warn("apiKeys property not found in admin data.");
      }
    } else {
      console.warn("Admin data not found in local storage.");
    }
    
    this.abn_company = this.paymentMethods.find(
      (method) => method.key === "abn"
    );
    this.phone_company = this.paymentMethods.find(
      (method) => method.key === "phone"
    );
    console.log("company_phone: ", this.paymentMethods);

    // this.businessData = JSON.parse(localStorage.getItem("business"));
    // console.log("business:", this.businessData);

    this.currentDate = this.getCurrentDate();
  }

  ngOnInit(): void {
    this.userInfo = this.userData._user;

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data;
      }
    });

    if (this.userInfo.id) {
      this.getProfile();
    }
  }

  generateInvoiceNumber() {
    const monthCodes = [
      "JAN",
      "FEB",
      "MAR",
      "APR",
      "MAY",
      "JUN",
      "JUL",
      "AUG",
      "SEP",
      "OCT",
      "NOV",
      "DEC",
    ];
    const currentDate = new Date();
    const currentMonthCode = monthCodes[currentDate.getMonth()];
    const timestampComponent = currentDate.getTime().toString().slice(-8);

    this.invoice_no = `${currentMonthCode}-${timestampComponent}`;
  }

  getCurrentDate(): string {
    return this.datePipe.transform(new Date(), "dd MMM yyyy");
  }

  addRow() {
    const newRow = {
      name: "",
      hours: 0,
      payrate: 0,
      totalpay: 0,
      isEdit: true,
    };
    this.dynamicRows.push(newRow);
  }

  calculateTotals() {
    this.totalPrice = 0;
    if (this.invoiceType === "normal") {
      for (const item of this.roasterHoursList) {
        this.totalPrice += item.totalpay;
      }
    }

    this.calculateGrandTotals();
  }

  calculateGrandTotals() {
    const gstvalue = this.gstValue;
    const totalWithGst = (this.totalPrice * gstvalue) / 100;

    this.grand_total = this.totalPrice + totalWithGst;
  }

  saveRow(index: number) {
    const editedRow = this.dynamicRows[index];
    editedRow.isEdit = false;

    if (!editedRow.name || !editedRow.hours || !editedRow.payrate) {
      return;
    }

    editedRow.totalpay = editedRow.hours * editedRow.payrate;

    if (
      this.eidtRowIndex !== undefined &&
      this.eidtRowIndex >= 0 &&
      this.eidtRowIndex < this.roasterHoursList.length
    ) {
      Object.assign(this.roasterHoursList[this.eidtRowIndex], editedRow);
      this.eidtRowIndex = undefined;
    } else {
      const existingRowIndex = this.roasterHoursList.findIndex(
        (item) =>
          item.name === editedRow.name &&
          item.hours === editedRow.hours &&
          item.payrate === editedRow.payrate
      );

      if (existingRowIndex !== -1) {
        Object.assign(this.roasterHoursList[existingRowIndex], editedRow);
      } else {
        this.roasterHoursList.push({ ...editedRow });
      }
      this.dynamicRows.splice(index, 1);
      this.calculateTotals();
    }
  }

  toggleEdit(index: number) {
    this.eidtRowIndex = index;
    if (index >= 5 && !this.roasterHoursList[index].isEdit) {
      const editableRow = { ...this.roasterHoursList[index], isEdit: true };
      this.dynamicRows.push(editableRow);
    }
  }

  deleteRow(index: number) {
    const addedRowIndex = this.roasterHoursList.findIndex(
      (item) =>
        item.name === this.dynamicRows[index].name &&
        item.hours === this.dynamicRows[index].hours &&
        item.payrate === this.dynamicRows[index].payrate
    );

    if (addedRowIndex !== -1) {
      this.roasterHoursList.splice(addedRowIndex, 1);
    }

    this.dynamicRows.splice(index, 1);

    this.calculateTotals();
  }

  getProfile() {
    this.adminservice.getAdminData(this.userInfo.id).subscribe(
      ({ success, data }) => {
        if (success) {
          this.admin_data = data;
          // this.validateAbn();
        }
      },
      (error) => {
        console.log(error);
      }
    );
  }

  ngOnDestroy() {
    localStorage.removeItem("routerId");

    this.trackAdmin
      .storeActivity("Exit Invoice Page", "Exit Invoice Page", this.routeId)
      .subscribe(() => {});
  }

  activity() {
    this.trackAdmin
      .storeActivity("Invoice Page", "Enter in Invoice Page")
      .subscribe(({ success, id }) => {
        if (success) {
          localStorage.setItem("routerId", id);
          this.routeId = id;
        }
      });
  }

  open() {
    const modelRef = this.modalService.open(ModalInvoiceComponent, {
      size: "xl",
    });
    modelRef.componentInstance.fromParent = this.siteData;
    modelRef.componentInstance.grand_total = this.grand_total;
    modelRef.componentInstance.date =
      moment(this.range.value.start).format("DD-MM-YYYY") +
      " - " +
      moment(this.range.value.end).format("DD-MM-YYYY");
    modelRef.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        console.log(
          "Modal Closed Reason -> ",
          `Dismissed ${this.getDismissReason(reason)}`
        );
      }
    );
  }

  singleSites;

  selectedCus(data: string) {
    this.fromMultiCustomer = data;
    this.selectedCustomer = data;

    this.selectedCustomerData = [];

    if (this.selectedCustomer && this.selectedCustomer.value) {
      this.selectedCustomerData.push(this.selectedCustomer.value);
      this.searchPerformed = false;
      this.roasterHoursList = [];
      this.detailed_invoice_data = [];
      let abn = this.selectedCustomerData[0].abn;
      this.isAbnValid = this.validateCustAbn(abn);
    }

    this.customersIds = this.fromMultiCustomer.value.id;
    console.log("id : ", this.customersIds);

    this.showDateRange = true;
  }
  updateInvoiceType() {
    this.searchPerformed = false;
    this.roasterHoursList = [];
    this.detailed_invoice_data = [];
  }
  changedate() {
    this.searchPerformed = false;
    this.roasterHoursList = [];
    this.detailed_invoice_data = [];
  }

  onSearch() {
    console.log("here in search ");
    console.log("slected  : ", this.fromMultiCustomer);
    console.log("id : ", this.customersIds);

    this.range.markAllAsTouched();
    this.submitted = true;
    this.searchRequired = false;

    if (!this.selectedCustomer || this.selectedCustomer==undefined) {
      this.toast.toastNotification1("Please select a customer.", "Error");
      return;
    }
    if (!this.range.value.start && !this.range.value.end) {
      this.toast.toastNotification1("Please select a date range.", "Error");
      return;
    }

    if (this.range.value.start && !this.range.value.end) {
      this.toast.toastNotification1(
        "End date is missing. Please select both start and end dates.",
        "Error"
      );
      return;
    }

    if (!this.range.value.start && this.range.value.end) {
      this.toast.toastNotification1(
        "Start date is missing. Please select both start and end dates.",
        "Error"
      );
      return;
    }

    if (this.range.value.start > this.range.value.end) {
      this.toast.toastNotification1(
        "Please select a valid date range.",
        "Error"
      );
      return;
    }
    if (!this.invoiceType) {
      this.toast.toastNotification1("Please select an invoice type.", "Error");
      // this.toast.toastNotification("performed search!", '', '')
      return;
    }
    // if (!this.invoice_no) {
    //   this.toast.toastNotification1("Please enter an invoice number.", "Error");
    //   return;
    // }

    this.isSearched = true;
    if (
      this.showDateRange &&
      this.customersIds &&
      this.range.value.start &&
      this.range.value.end &&
      this.invoiceType
    ) {
      let value = {
        type: this.invoiceType,
        customer_id: this.customersIds,
        date:
          moment(this.range.value.start).format("DD/MM/YYYY") +
          " - " +
          moment(this.range.value.end).format("DD/MM/YYYY"),
      };

      if (this.invoiceType == "normal") {
        // this.searchPerformed = true;
        this.invoice.getInvoice(value).subscribe(({ success, data }) => {
          if (success) {
            data?.forEach((element) => {
              element.isEdit = false;
            });
            this.roasterHoursList = data;

            this.calculateTotals();
            this.searchPerformed = true;

            this.toast.toastNotification(
              "All data is listed below in the table.",
              "Data found",
              "Success"
            );
            this.cdr.markForCheck();
          }
        });
      } else if (this.invoiceType === "summariz") {
        this.searchPerformed = true;
        this.invoice.getInvoice(value).subscribe(({ data }) => {
          // data?.forEach(element => {
          //   element.isEdit = false
          // });
          this.detailed_invoice_data = data;

          this.searchPerformed = true;
          if (
            this.detailed_invoice_data &&
            this.detailed_invoice_data.length > 0
          ) {
            this.toast.toastNotification(
              "All data is listed below in the table.",
              "Data found",
              "Success"
            );
          } else {
            this.toast.toastNotification1(
              "No data found for detailed invoice.",
              "Data not found"
            );
          }
          this.cdr.markForCheck();
        });
      }
    }
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return "by pressing ESC";
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return "by clicking on a backdrop";
    } else if (reason === "updated") {
      // this.getAdmin_data();
    }
  }

  onPaymentMethodsToggleChange() {
    this.showPaymentMethods = this.isPaymentMethodOn;
  }

  yourSelectionChangeFunction(event: MatRadioChange) {
    this.selectedPaymentOption = event.value;
  }
  onLateFeesToggleChange() {
    this.lateFeesToggleOn = !this.lateFeesToggleOn;
  }

  onNotesToggleChange() {
    this.showNotes = !this.showNotes;
  }

  onGSTToggleChange(event: any) {
    this.gstToggleChecked = event.checked;

    if (!this.gstToggleChecked) {
      this.gstValue = 0.0;
      this.calculateTotals();
    } else {
      this.gstValue = 10.0;
      this.calculateTotals();
    }
  }
  private preparePaymentTypeObject(): any {
    const abnEntry = this.paymentMethods.find((method) => method.key === "abn");

    const bankNameEntry = this.paymentMethods.find(
      (method) => method.key === "bank_name"
    );
    const accountNumberEntry = this.paymentMethods.find(
      (method) => method.key === "account_number"
    );

    const bsbEntry = this.paymentMethods.find((method) => method.key === "bsb");

    const abnValue = abnEntry ? abnEntry.value : "";
    const bankNameValue = bankNameEntry ? bankNameEntry.value : "";
    const accountNumberValue = accountNumberEntry
      ? accountNumberEntry.value
      : "";

    const bsbValue = bsbEntry ? bsbEntry.value : "";

    return {
      abn: abnValue,
      bank_name: bankNameValue,
      account_number: accountNumberValue,
      bsb: bsbValue,
    };
  }

  private companydetailsObject() {
    const billercode = this.paymentMethods.find(
      (method) => method.key === "biller_code"
    );
    const referenceNo = this.paymentMethods.find(
      (method) => method.key === "reference_no"
    );
    const biller_code = billercode ? billercode.value : "";
    const reference_num = referenceNo ? referenceNo.value : "";
    return {
      biller_code: biller_code,
      reference_no: reference_num,
    };
  }
  private prepareFormData(form: NgForm): any {
    if (this.invoiceType === "normal") {
      return {
        invoice_num: this.invoice_no,
        due_Date: this.datePipe.transform(this.due_date, "dd MMM yyyy"),
        paymentMethod: this.selectedPaymentOption,
        type: this.invoiceType,
        admin_id: this.admin_data.id,
        customer_id: this.customersIds,
        invoice_from: {
          name: this.businessData?.title,
          email: this.businessData?.email,
          phone: this.phone_company.value,

          // phone: this.admin_data?.phone,
          // abn: this.admin_data?.abn,
          abn: this.abn_company.value,
          invoiceDescriptionFrom: this.invoiceDescriptionFrom,
        },
        invoice_to:
          this.selectedCustomerData.length > 0
            ? {
                name: this.selectedCustomerData[0].name,
                email: this.selectedCustomerData[0].email,
                phone: this.selectedCustomerData[0].phone,
                abn: this.selectedCustomerData[0].abn,
                invoiceDescriptionTo: this.invoiceDescriptionTo,
              }
            : null,
        rates: this.roasterHoursList,
        currency: this.selectedCurrency,
        // payment_type: this.isPaymentMethodOn ? (this.selectedPaymentOption === "bantransfer" ? this.preparePaymentTypeObject() : (this.selectedPaymentOption === "bpay" ? this.companydetailsObject() : {})) : {},
        // payment_type: this.isPaymentMethodOn
        // ? this.preparePaymentTypeObject()
        // : null,
        payment_type: (() => {
          if (this.selectedPaymentOption === "bank_transfer") {
            return this.preparePaymentTypeObject();
          } else if (this.selectedPaymentOption === "bpay") {
            return this.companydetailsObject();
          } else {
            return null;
          }
        })(),
        late_fee: this.lateFeesToggleOn ? this.lateFeesValue : 0.0,
        notes: this.showNotes ? this.additionalNotes : "",
        gst: this.gstToggleChecked ? this.gstValue : 0.0,
      };
    } else if (this.invoiceType === "summariz") {
      return {
        detailed_invoice: this.detailed_invoice_data,
        due_Date: this.datePipe.transform(this.due_date, "dd MMM yyyy"),
        type: this.invoiceType,
        invoice_from: {
          name: this.businessData?.title,
          email: this.businessData?.email,
          phone: this.phone_company.value,

          // phone: this.admin_data?.phone,
          // abn: this.admin_data?.abn,
          abn: this.abn_company.value,
          invoiceDescriptionFrom: this.invoiceDescriptionFrom,
        },
        invoice_to:
          this.selectedCustomerData.length > 0
            ? {
                name: this.selectedCustomerData[0].name,
                email: this.selectedCustomerData[0].email,
                phone: this.selectedCustomerData[0].phone,
                abn: this.selectedCustomerData[0].abn,
                invoiceDescriptionTo: this.invoiceDescriptionTo,
              }
            : null,
        date_range:
          moment(this.range.value.start).format("DD/MM/YYYY") +
          " - " +
          moment(this.range.value.end).format("DD/MM/YYYY"), // Assuming you have a date_range property
        customer_id: this.customersIds,

        payment_type: (() => {
          if (this.selectedPaymentOption === "bank_transfer") {
            return this.preparePaymentTypeObject();
          } else if (this.selectedPaymentOption === "bpay") {
            return this.companydetailsObject();
          } else {
            return null;
          }
        })(),
        paymentMethod: this.selectedPaymentOption,

        late_fee: this.lateFeesToggleOn ? this.lateFeesValue : 0.0,
        notes: this.showNotes ? this.additionalNotes : "",
        currency: this.selectedCurrency,
        gst: this.gstToggleChecked ? this.gstValue : 0.0,
      };
    }
  }

  private sendformData(form: NgForm): any {
    if (this.invoiceType === "normal") {
      return {
        due_Date: this.datePipe.transform(this.due_date, "dd MMM yyyy"),
        paymentMethod: this.selectedPaymentOption,
        invoice_num: this.invoice_no,
        type: this.invoiceType,
        admin_id: this.admin_data.id,
        customer_id: this.customersIds,
        invoice_from: {
          name: this.businessData?.title,
          email: this.businessData?.email,
          phone: this.phone_company.value,

          abn: this.abn_company.value,
          invoiceDescriptionFrom: this.invoiceDescriptionFrom,
        },
        invoice_to:
          this.selectedCustomerData.length > 0
            ? {
                name: this.selectedCustomerData[0].name,
                email: this.selectedCustomerData[0].email,
                phone: this.selectedCustomerData[0].phone,
                abn: this.selectedCustomerData[0].abn,
                invoiceDescriptionTo: this.invoiceDescriptionTo,
              }
            : null,
        rates: this.roasterHoursList,
        currency: this.selectedCurrency,
        payment_type: (() => {
          if (this.selectedPaymentOption === "bank_transfer") {
            return this.preparePaymentTypeObject();
          } else if (this.selectedPaymentOption === "bpay") {
            return this.companydetailsObject();
          } else {
            return null;
          }
        })(),
        late_fee: this.lateFeesToggleOn ? this.lateFeesValue : 0.0,
        notes: this.showNotes ? this.additionalNotes : "",
        gst: this.gstToggleChecked ? this.gstValue : 0.0,
      };
    } else if (this.invoiceType === "summariz") {
      return {
        due_Date: this.datePipe.transform(this.due_date, "dd MMM yyyy"),
        admin_id: this.admin_data.id,
        invoice_num: this.invoice_no,
        type: this.invoiceType,
        invoice_from: {
          name: this.businessData?.title,
          email: this.businessData?.email,
          phone: this.phone_company.value,

          // phone: this.admin_data?.phone,
          // abn: this.admin_data?.abn,
          abn: this.abn_company.value,
          invoiceDescriptionFrom: this.invoiceDescriptionFrom,
        },
        invoice_to:
          this.selectedCustomerData.length > 0
            ? {
                name: this.selectedCustomerData[0].name,
                email: this.selectedCustomerData[0].email,
                phone: this.selectedCustomerData[0].phone,
                abn: this.selectedCustomerData[0].abn,
                invoiceDescriptionTo: this.invoiceDescriptionTo,
              }
            : null,
        date_range:
          moment(this.range.value.start).format("DD/MM/YYYY") +
          " - " +
          moment(this.range.value.end).format("DD/MM/YYYY"), // Assuming you have a date_range property
        customer_id: this.customersIds,
        payment_type: (() => {
          if (this.selectedPaymentOption === "bank_transfer") {
            return this.preparePaymentTypeObject();
          } else if (this.selectedPaymentOption === "bpay") {
            return this.companydetailsObject();
          } else {
            return null;
          }
        })(),
        paymentMethod: this.selectedPaymentOption,
        late_fee: this.lateFeesToggleOn ? this.lateFeesValue : 0.0,
        notes: this.showNotes ? this.additionalNotes : "",
        currency: this.selectedCurrency,
        gst: this.gstToggleChecked ? this.gstValue : 0.0,
      };
    }
  }

  sendInvoice(form: NgForm) {
    const formData = this.prepareFormData(form);

    if (!this.searchPerformed) {
      this.searchRequired = true;
      this.toast.toastNotification1(
        "Please perform a search before sending the invoice.",
        "Error"
      );
      return;
    }
    if (!this.selectedCustomerData[0].abn) {
      this.isAbnValid = false;
    }

    if (!this.admin_data.name) {
      this.isAdminName = false;
    }

    if (this.admin_data.name) {
      this.isAdminName = true;
    }
    if (!this.selectedCustomerData[0].name) {
      this.iscustName = false;
    }
    if (this.selectedCustomerData[0].name) {
      this.iscustName = true;
    }

    this.isSend = true;
    if (formData) {
      if (!this.selectedPaymentOption && this.isPaymentMethodOn) {
        this.toast.toastNotification1("Please select payment method.", "Error");
        return;
      }

      if (!this.isAbnValid) {
        this.toast.toastNotification1("Required Valid ABN!", "Error");
        this.isAbnValid = false;
        return;
      }
      if (!this.invoice_no) {
        this.toast.toastNotification1(
          "Please enter an invoice number.",
          "Error"
        );
        return;
      }
      if (
        this.invoiceType == "summariz" &&
        this.detailed_invoice_data.length === 0
      ) {
        this.toast.toastNotification1(
          "No data found for detailed invoice.",
          "Data not found"
        );
        return;
      }
      if (!this.isAdminName) {
        this.toast.toastNotification1("Name is Required!", "Error");
        return;
      }
      if (!this.iscustName) {
        this.toast.toastNotification1("Name is Required", "Error");
        return;
      }
      if (!this.due_date) {
        this.toast.toastNotification1("Due Date is Required", "Error");
        return;
      }

      const formData = this.sendformData(form);

      this.invoice.sendInvoiceToCustoemr(formData).subscribe(
        (response) => {
          console.log("Invoice sent successfully", response);
        },
        (error) => {
          console.error("Error sending invoice", error);
        }
      );
    }
  }

  donwloadPDF(action = "open", form: NgForm) {
    const formData = this.prepareFormData(form);

    if (!this.searchPerformed) {
      this.searchRequired = true;
      this.toast.toastNotification1(
        "Please perform a search before sending the invoice.",
        "Error"
      );
      return;
    }
    if (!this.selectedCustomerData[0].abn) {
      this.isAbnValid = false;
    }

    if (!this.admin_data.name) {
      this.isAdminName = false;
    }

    if (this.admin_data.name) {
      this.isAdminName = true;
    }
    if (!this.selectedCustomerData[0].name) {
      this.iscustName = false;
    }
    if (this.selectedCustomerData[0].name) {
      this.iscustName = true;
    }
    this.isDonwload = true;
    if (formData) {
      if (!this.selectedPaymentOption && this.isPaymentMethodOn) {
        this.toast.toastNotification1("Please select payment method.", "Error");
        return;
      }

      if (!this.isAbnValid) {
        this.toast.toastNotification1("Required Valid ABN!", "Error");
        this.isAbnValid = false;
        return;
      }
      if (!this.invoice_no) {
        this.toast.toastNotification1(
          "Please enter an invoice number.",
          "Error"
        );
        return;
      }
      if (
        this.invoiceType == "summariz" &&
        this.detailed_invoice_data.length === 0
      ) {
        this.toast.toastNotification1(
          "No data found for detailed invoice.",
          "Data not found"
        );
        return;
      }
      if (!this.isAdminName) {
        this.toast.toastNotification1("Name is Required!", "Error");
        return;
      }
      if (!this.iscustName) {
        this.toast.toastNotification1("Name is Required", "Error");
        return;
      }
      if (!this.due_date) {
        this.toast.toastNotification1("Due Date is Required", "Error");
        return;
      }
      const formData = this.prepareFormData(form);
      console.log("formdata: ", formData);

      if (this.invoiceType === "normal") {
        this.normalInvoice(action, formData);
      } else if (this.invoiceType === "summariz") {
        this.summerizedInvoice(action, formData);
      }
    }
  }

  getCurrency(event) {
    this.selectedCurrency = event.value;
  }

  formatDateRange(start: string, end: string, hourType: string): string {
    const formattedStart = this.datePipe.transform(
      new Date(start),
      "dd.MM.yyyy"
    );
    return `${formattedStart} (${hourType})`;
  }

  formatTimeRange(start: string, end: string): string {
    const formattedStartTime = this.datePipe.transform(new Date(start), "HHmm");
    const formattedEndTime = this.datePipe.transform(new Date(end), "HHmm");
    return `${formattedStartTime} - ${formattedEndTime}`;
  }

  calculateTotalAmount(item: any): number {
    const hours =
      item.units ||
      item.morning_hours ||
      item.night_hours ||
      item.sunday_morning_hours ||
      item.sunday_night_hours ||
      item.saturday_morning_hours ||
      item.saturday_night_hours ||
      item.ph_morning_hours ||
      item.ph_night_hours ||
      0;
    const unitPrice =
      item.day_rate ||
      item.night_rate ||
      item.saturday_rate ||
      item.sunday_rate ||
      item.public_holiday_rate ||
      0;
    const subtotal = hours * unitPrice;

    return subtotal;
  }

  get subTotal(): number {
    return this.detailed_invoice_data.reduce((sum, item) => {
      return sum + this.calculateTotalAmount(item);
    }, 0);
  }

  get grand_Total(): number {
    const gstPercentage = this.gstValue;
    const gstAmount = (this.subTotal * gstPercentage) / 100;
    return this.subTotal + gstAmount;
  }

  onInputChange(event: any) {
    const value = event.target.value;

    if (value.trim() === "" || value.trim() === "%") {
      this.gstValueDisplay = "%";
      this.gstValue = 0;
    } else {
      this.gstValueDisplay = value;

      const numericValue = parseFloat(value.replace("%", ""));

      this.gstValue = isNaN(numericValue) ? 0 : numericValue;
    }
    this.calculateTotals();
  }

  formatText(type: string) {
    switch (type) {
      case "bold":
        this.wrapText("**");
        break;
      case "italic":
        this.wrapText("*");
        break;
      case "heading":
        this.insertText("# ");
        break;
      case "unorderedList":
        this.insertText("- ");
        break;
      case "orderedList":
        this.insertText("1. ");
        break;
      default:
        break;
    }
  }

  wrapText(wrapper: string) {
    if (this.notesTextarea) {
      const start = this.notesTextarea.selectionStart;
      const end = this.notesTextarea.selectionEnd;

      this.additionalNotes =
        this.additionalNotes.substring(0, start) +
        wrapper +
        this.additionalNotes.substring(start, end) +
        wrapper +
        this.additionalNotes.substring(end);
    }
  }

  insertText(text: string) {
    if (this.notesTextarea) {
      const start = this.notesTextarea.nativeElement.selectionStart;
      const end = this.notesTextarea.nativeElement.selectionEnd;

      this.additionalNotes =
        this.additionalNotes.substring(0, start) +
        text +
        this.additionalNotes.substring(end);

      this.notesTextarea.nativeElement.value = this.additionalNotes;

      this.notesTextarea.nativeElement.setSelectionRange(
        start + text.length,
        start + text.length
      );
    }
  }

  onAbnInputChange(event: any) {
    const abnValue = event.target.value;

    if (!abnValue) {
      this.isAbnValid = false;
      return;
    }

    if (this.validateCustAbn(abnValue)) {
      this.isAbnValid = true;

      if (this.selectedCustomer && this.selectedCustomer.value) {
        this.selectedCustomer.value.abn = abnValue.trim(); // Remove leading and trailing spaces
        const selectedIndex = this.selectedCustomerData.findIndex(
          (customer) => customer.id === this.selectedCustomer.value.id
        );

        if (selectedIndex !== -1) {
          this.selectedCustomerData[selectedIndex] = {
            ...this.selectedCustomer.value,
          };
        }
      }
    } else {
      this.isAbnValid = false;
    }
  }

  validateCustAbn(abn) {
    const trimmedAbn = abn.trim();

    const abnRegex = /^\d{11}$/;

    return abnRegex.test(trimmedAbn);
  }

  onDiscountKeyDown(event: KeyboardEvent): void {
    if (event.key === "-" || event.key === "+") {
      event.preventDefault();
    }
  }

  emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;

  validateEmail(emailvalue: string) {
    this.isEmailError = !this.emailPattern.test(emailvalue);
  }

  validatecustEmail(emailvalue: string) {
    this.iscustEmailError = !this.emailPattern.test(emailvalue);
  }

  checkValue(event) {
    return event.target.value.length === 11
      ? event.preventDefault()
      : String.fromCharCode(event.charCode).match(/[^0-9]/g) === null;
  }

  checkGST(event: KeyboardEvent): void {
    const target = event.target as HTMLInputElement;

    if (target.value.length === 3) {
      event.preventDefault();
    } else {
      const isDigit =
        String.fromCharCode(event.charCode).match(/[^0-9]/g) === null;
      if (!isDigit) {
        event.preventDefault();
      }
    }
  }

  gstkeydown(event: KeyboardEvent): void {
    const keyPressed = event.key;
    const currentValue = (event.target as HTMLInputElement).value;

    // Allow backspace
    if (keyPressed === "Backspace") {
      return;
    }

    // Check if the input is becoming empty
    if (currentValue.length === 1 && keyPressed === "Backspace") {
      return;
    }

    // Check if the input is a valid number between 1 and 100
    const proposedValue = currentValue + keyPressed;

    // Do not allow leading zero
    if (proposedValue.length === 2 && proposedValue.startsWith("0")) {
      event.preventDefault();
      return;
    }

    const isNumberBetween1And100 = /^([1-9]|[1-9][0-9]|100)$/.test(
      proposedValue
    );

    if (!isNumberBetween1And100) {
      event.preventDefault();
    }
  }

  filterDates = (date: Date | null): boolean => {
    const currentDate = new Date();
    currentDate.setHours(0, 0, 0, 0);
    return date >= currentDate;
  };

  async normalInvoice(action = "open", formdata: any) {
    const customerFirstName = formdata.invoice_to.name.split(" ")[0];

    const pdfFilename = `${customerFirstName}_${this.invoice_no}.pdf`;
    console.log("form:", formdata);
    if (this.invoiceType == "normal") {
      const getBase64Image = (imgPath: string): Promise<string> => {
        return new Promise((resolve, reject) => {
          const img = new Image();
          img.crossOrigin = "Anonymous";

          img.onload = () => {
            const canvas = document.createElement("canvas");
            canvas.width = img.width;
            canvas.height = img.height;

            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0);

            const dataURL = canvas.toDataURL("image/png");
            resolve(dataURL);
          };

          img.onerror = () => {
            reject(new Error("Error loading image"));
          };

          img.src = imgPath;
        });
      };
      const logoDataURL = await getBase64Image(
        "/assets/images/logo/scouts.png"
      );
      const generateTableBody = () => {
        const headerRow = ["Item ID", "Name", "Hrs", "Price", "Total"];
        const currencyOptions = {
          style: "currency",
          currency: formdata.currency,
        };

        if (formdata.currency === "AUD") {
          currencyOptions.currency = "USD";
        }
        const bodyRows = formdata.rates.map((rate, i: number) => [
          i + 1,
          { text: rate.name, alignment: "left" },
          { text: rate.hours },
          {
            text: new Intl.NumberFormat("en-US", { ...currencyOptions }).format(
              rate.payrate
            ),
            alignment: "center",
            style: "currency",
          },
          {
            text: new Intl.NumberFormat("en-US", { ...currencyOptions }).format(
              rate.totalpay
            ),
            alignment: "right",
            style: "currency",
          },
        ]);

        const totalRow = [
          { colSpan: 3, text: "" },
          "",
          "",
          { text: "Subtotal", bold: true, alignment: "left" },

          {
            text: new Intl.NumberFormat("en-US", { ...currencyOptions }).format(
              this.totalPrice
            ),
            alignment: "right",
          },
        ];
        const gstRow = [
          { colSpan: 3, text: "" },
          "",
          "",

          { text: "GST", bold: true, alignment: "left" },

          { text: `${this.gstValue}%`, alignment: "right" },
        ];
        const grandTotalRow = [
          { colSpan: 3, text: "" },
          "",
          "",

          { text: "Grand Total", bold: true, alignment: "left" },

          {
            text: new Intl.NumberFormat("en-US", { ...currencyOptions }).format(
              this.grand_total
            ),
            alignment: "right",
          },
        ];
        const tableBody = [
          headerRow,
          ...bodyRows,
          totalRow,
          gstRow,
          grandTotalRow,
        ];

        return tableBody;
      };

      let rows = [];

      if (formdata.paymentMethod === "bank_transfer") {
        rows = [
          [
            { text: "Bank Name:", style: "bold", alignment: "left" },
            { text: formdata.payment_type.bank_name, alignment: "right" },
          ],
          [
            { text: "BSB:", style: "bold", alignment: "left" },
            { text: formdata.payment_type.bsb, alignment: "right" },
          ],
          [
            { text: "Account No:", style: "bold", alignment: "left" },
            { text: formdata.payment_type.account_number, alignment: "right" },
          ],
        ];
      } else if (formdata.paymentMethod === "bpay") {
        rows = [
          [
            { text: "Biller Code:", style: "bold", alignment: "left" },
            {
              text:
                formdata.payment_type.biller_code !== null
                  ? formdata.payment_type.biller_code
                  : "",
              alignment: "right",
            },
          ],
          [
            { text: "Reference No:", style: "bold", alignment: "left" },
            {
              text:
                formdata.payment_type.reference_no !== null
                  ? formdata.payment_type.reference_no
                  : "",
              alignment: "right",
            },
          ],
        ];
      }
      let docDefinition: TDocumentDefinitions = {
        pageSize: "A4",
        content: [
          {
            absolutePosition: { x: 249, y: -60 },
            canvas: [
              {
                type: "rect",
                x: 0,
                y: 100,
                w: 120,
                h: 106,
                r: 50,
                color: "#64b38a",
              },
            ],
          },
          {
            table: {
              widths: ["50%", "30%", "20%"],
              body: [
                [
                  {
                    image: logoDataURL,
                    width: 100,
                    height: 80,
                  },
                  {
                    stack: [
                      {
                        text: "INVOICE",
                        fontSize: 23,
                        color: "white",
                        bold: true,
                        margin: [0, 0, 0, 10],
                      },
                      { text: "Invoice #", style: "header1" },
                      { text: "Issue Date:", style: "header1" },
                      { text: "Due Date:", style: "header1" },
                    ],
                    fillColor: "#64b38a",
                    margin: [0, 10, 0, 10],
                  },
                  {
                    stack: [
                      { text: "  " },
                      { text: formdata.invoice_num, style: "headerText" },
                      { text: this.currentDate, style: "headerText" },
                      { text: formdata.due_Date, style: "headerText" },
                    ],
                    fillColor: "#64b38a",
                    margin: [0, 34, 0, 10],
                  },
                ],
              ],
            },
            fillColor: "#f3f3f3",
            layout: {
              defaultBorder: false,
            },
          },
          {
            columns: [
              {
                width: "50%",
                stack: [
                  { text: "Invoice To", style: "subheader" },
                  { text: formdata.invoice_to.name, bold: true, fontSize: 12 },
                  {
                    text: "Email: " + formdata.invoice_to.email,
                    style: "centerright",
                  },
                  {
                    text: "Phone: " + formdata.invoice_to.phone,
                    style: "centerright",
                  },
                  {
                    text: "ABN: " + formdata.invoice_to.abn,
                    style: "centerright",
                  },
                ],
                margin: [0, 20, 0, 20],
              },
              {
                width: "50%",
                stack: [
                  {
                    text: "Invoice From",
                    style: "subheader",
                    alignment: "left",
                  },
                  {
                    text: formdata.invoice_from.name,
                    bold: true,
                    alignment: "left",
                    fontSize: 12,
                  },
                  {
                    text: "Email: " + formdata.invoice_from.email,
                    style: "centerleft",
                  },
                  {
                    text: "Phone: " + formdata.invoice_from.phone,
                    style: "centerleft",
                  },
                  {
                    text: "ABN: " + formdata.invoice_from.abn,
                    style: "centerleft",
                  },
                ],
                alignment: "right",
                margin: [25, 20, 0, 20],
              },
            ],
            margin: [0, 5, 0, 5],
          },
          {
            canvas: [
              {
                type: "line",
                x1: 0,
                y1: 10,
                x2: 515,
                y2: 10,
                lineWidth: 5,
                lineColor: "#c6c3c3",
              },
            ],
            margin: [0, 0, 0, 5],
          },

          {
            table: {
              headerRows: 1,
              widths: ["10%", "30%", "15%", "25%", "20%"],

              body: generateTableBody(),
              dontBreakRows: true,
            },

            layout: {
              paddingTop: function () {
                return 8;
              },
              paddingBottom: function () {
                return 8;
              },
              hLineWidth: function (i, node) {
                return i === 0 || i === 1 || i === node.table.body.length
                  ? 2
                  : 1;
              },
              vLineWidth: function () {
                return 1;
              },
              hLineColor: function (i) {
                return i === 0 || i === 1 ? "#000" : "#ccc";
              },
              vLineColor: function () {
                return "#ccc";
              },
            },
            alignment: "center",
            margin: [0, 5, 0, 5],
          },

          {
            columns: [
              {
                width: "60%",
                table: {
                  widths: [300],
                  heights: [10, 60],
                  body: [
                    [
                      {
                        text: "Additional Notes",
                        style: "subheader",
                        alignment: "left",
                      },
                    ],
                    [
                      {
                        text: formdata.notes,
                        alignment: "justify",
                        margin: [2, 2, 4, 2],
                      },
                    ],
                  ],
                },
                layout: {
                  hLineColor: function (i, node) {
                    return i === 0 || i === node.table.body.length
                      ? "#CCC"
                      : "#CCC"; // border color #CCC
                  },
                  vLineColor: function (i, node) {
                    return i === 0 || i === node.table.body.length
                      ? "#CCC"
                      : "#CCC"; // border color #CCC
                  },
                  vLineWidth: function (i, node) {
                    return i === 0 || i === node.table.widths.length ? 1 : 0; // outer vertical borders
                  },
                },
                margin: [0, 10, 0, 10],
              },

              {
                width: "40%",

                table: {
                  widths: [80, 100],
                  body: [
                    [
                      {
                        colSpan: 2,
                        text:
                          formdata.paymentMethod === "bank_transfer"
                            ? "Bank Transfer\nAccount Detials"
                            : formdata.paymentMethod === "bpay"
                            ? "B-Pay\n Account Details"
                            : "",
                        style: "subheader",
                      },
                      "",
                    ],

                    ...rows,
                  ],
                },
                margin: [10, 10, 0, 10],
                layout: {
                  hLineColor: function (i, node) {
                    return i === 0 || i === node.table.body.length
                      ? "#CCC"
                      : "#CCC";
                  },
                  vLineColor: function (i, node) {
                    return i === 0 || i === node.table.body.length
                      ? "#CCC"
                      : "#CCC";
                  },
                  vLineWidth: function (i, node) {
                    return i === 0 || i === node.table.widths.length ? 1 : 0;
                  },
                },
              },
              ,
            ],
            margin: [0, 5, 0, 5],
          },
        ],
        styles: {
          header1: {
            color: "white",
            bold: true,
            margin: [23, 0, 0, 0],
          },
          headerText: {
            color: "white",
            alignment: "right",
            margin: [0, 0, 10, 0],
          },

          subheader: {
            fontSize: 14,
            bold: true,
            margin: [0, 0, 0, 10],
            color: "#535353",
          },
          centerright: {
            fontSize: 10,
            margin: [0, 2, 0, 2],
          },

          centerleft: {
            alignment: "left",
            fontSize: 10,
            margin: [0, 2, 0, 2],
          },
        },
      };

      if (action === "open") {
        pdfMake.createPdf(docDefinition).open();
      } else if (action === "download") {
        pdfMake.createPdf(docDefinition).download(pdfFilename);
      }
    }
  }

  async summerizedInvoice(action = "open", formdata: any) {
    const customerFirstName = formdata.invoice_to.name.split(" ")[0];

    const pdfFilename = `${customerFirstName}_${this.invoice_no}.pdf`;
    const getBase64Image = (imgPath: string): Promise<string> => {
      return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = "Anonymous";

        img.onload = () => {
          const canvas = document.createElement("canvas");
          canvas.width = img.width;
          canvas.height = img.height;

          const ctx = canvas.getContext("2d");
          ctx.drawImage(img, 0, 0);

          const dataURL = canvas.toDataURL("image/png");
          resolve(dataURL);
        };

        img.onerror = () => {
          reject(new Error("Error loading image"));
        };

        img.src = imgPath;
      });
    };

    // Load logo as dataURL
    const logoDataURL = await getBase64Image("/assets/images/logo/scouts.png");

    const generateTableBody = () => {
      const headerRow = [
        "Item ID",
        "Description",
        "State",
        "PO/WO",
        "Hours",
        "Price ($) excluding tax",
        "Disc. (%)",
        "Amount ($) excluding tax",
      ];

      const currencyOptions = {
        style: "currency",
        currency: formdata.currency,
      };

      if (formdata.currency === "AUD") {
        currencyOptions.currency = "USD";
      }

      const bodyRows = formdata.detailed_invoice.map((item: any, i: number) => [
        i + 1,
        `${this.formatDateRange(item.start, item.end, item.hour_type)}\n${
          item.first_name
        } ${item.last_name}\n${this.formatTimeRange(item.start, item.end)}`,
        item.state,
       {
        text: item.po_wo || item.site_po_wo || 'N/A',
        alignment:"center"
       },
        // {
        //   text:
        //     +item.units ||
        //     +item.morning_hours ||
        //     +item.night_hours ||
        //     +item.sunday_morning_hours ||
        //     +item.sunday_night_hours ||
        //     +item.saturday_morning_hours ||
        //     item.saturday_night_hours ||
        //     item.ph_morning_hours ||
        //     item.ph_night_hours ||
        //     0,
        //   alignment: "center",
        // },
        {
          text: `${(
              (item.units || 0) +
              (item.morning_hours || 0) +
              (item.night_hours || 0) +
              (item.sunday_morning_hours || 0) +
              (item.sunday_night_hours || 0) +
              (item.saturday_morning_hours || 0) +
              (item.saturday_night_hours || 0) +
              (item.ph_morning_hours || 0) +
              (item.ph_night_hours || 0)
          ).toFixed(2)}
      
          ${
              (() => {
                  let timeSlots = '';
                  if(item.morning_hours || item.night_hours || item.saturday_morning_hours || item.saturday_night_hours || item.sunday_night_hours || item.sunday_morning_hours){
                    if (item.morning_time && item.morning_time.length > 0) {
                      timeSlots += item.morning_time.map(slot => `${slot[0]} - ${slot[1]}`).join('\n');
                  }
                  if (item.night_time && item.night_time.length > 0) {
                      if (timeSlots !== '') timeSlots += '\n';
                      timeSlots += item.night_time.map(slot => `${slot[0]} - ${slot[1]}`).join('\n');
                  }
                  if (item.saturday_morning && item.saturday_morning.length > 0) {
                    if (timeSlots !== '') timeSlots += '\n';
                    timeSlots += item.saturday_morning.map(slot => `${slot[0]} - ${slot[1]}`).join('\n');
                }
                if (item.saturday_night && item.saturday_night.length > 0) {
                  if (timeSlots !== '') timeSlots += '\n';
                  timeSlots += item.saturday_night.map(slot => `${slot[0]} - ${slot[1]}`).join('\n');
              }
              if (item.sunday_night && item.sunday_night.length > 0) {
                if (timeSlots !== '') timeSlots += '\n';
                timeSlots += item.sunday_night.map(slot => `${slot[0]} - ${slot[1]}`).join('\n');
            }
            if (item.sunday_morning && item.sunday_morning.length > 0) {
              if (timeSlots !== '') timeSlots += '\n';
              timeSlots += item.sunday_morning.map(slot => `${slot[0]} - ${slot[1]}`).join('\n');
          }
                  return timeSlots !== '' ? timeSlots : (
                      (item.start && item.start.split(" ")[1] && item.end && item.end.split(" ")[1]) ?
                      `${item.start.split(" ")[1].slice(0, 5)} - ${item.end.split(" ")[1].slice(0, 5)}` :
                      'N/A'
                  );
                  }else {
                    return ''; 
                }
                 
              })()
          }`,
          alignment: "center"
      },      
      
        {
          text: new Intl.NumberFormat("en-US", { ...currencyOptions }).format(
            +item.day_rate ||
              +item.night_rate ||
              +item.saturday_rate ||
              +item.sunday_rate ||
              +item.public_holiday_rate ||
              0
          ),
          alignment: "center",
        },
        { text: "0.0", alignment: "center" },
        {
          text: new Intl.NumberFormat("en-US", { ...currencyOptions }).format(
            this.calculateTotalAmount(item)
          ),
          alignment: "right",
        },
      ]);
      const totalRow = [
        { colSpan: 5, text: "" },
        "",
        "",
        "",
        "",
        { colSpan: 2, text: "Subtotal", bold: true },
        "",
        {
          text: this.formatCurrency(this.subTotal, formdata.currency),
          alignment: "right",
        },
      ];

      const gstRow = [
        { colSpan: 5, text: "" },
        "",
        "",
        "",
        "",
        { colSpan: 2, text: "GST", bold: true },
        "",
        {
          text: `${formdata.gst}%`,
          alignment: "right",
        },
      ];

      const grandTotalRow = [
        { colSpan: 5, text: "" },
        "",
        "",
        "",
        "",
        { colSpan: 2, text: "Grand total", bold: true },
        "",
        {
          text: this.formatCurrency(this.grand_Total, formdata.currency),
          alignment: "right",
        },
      ];

      const tableBody = [
        headerRow,
        ...bodyRows,
        totalRow,
        gstRow,
        grandTotalRow,
      ];

      tableBody.forEach((row, index) => {
        if (index > 0 && index % 30 === 0) {
          row.pageBreak = "before";
        }
      });

      return tableBody;
    };

    let rows = [];

    if (formdata.paymentMethod === "bank_transfer") {
      rows = [
        [
          { text: "Bank Name:", style: "bold", alignment: "left" },
          { text: formdata.payment_type.bank_name, alignment: "right" },
        ],
        [
          { text: "BSB:", style: "bold", alignment: "left" },
          { text: formdata.payment_type.bsb, alignment: "right" },
        ],
        [
          { text: "Account No:", style: "bold", alignment: "left" },
          { text: formdata.payment_type.account_number, alignment: "right" },
        ],
      ];
    } else if (formdata.paymentMethod === "bpay") {
      rows = [
        [
          { text: "Biller Code:", style: "bold", alignment: "left" },
          {
            text:
              formdata.payment_type.biller_code !== null
                ? formdata.payment_type.biller_code
                : "",
            alignment: "right",
          },
        ],
        [
          { text: "Reference No:", style: "bold", alignment: "left" },
          {
            text:
              formdata.payment_type.reference_no !== null
                ? formdata.payment_type.reference_no
                : "",
            alignment: "right",
          },
        ],
      ];
    }

    // Document definition
    let docDefinition: TDocumentDefinitions = {
      pageSize: "A4",
      // pageOrientation: 'landscape',
      footer: (currentPage: number, pageCount: number) => {
        return {
          columns: [
            {
              text: `Page ${currentPage}/${pageCount}`,
              alignment: "left",
              margin: [30, 0],
              fontSize: 8,
              color: "#555",
            },

            {
              text: `${formdata.due_Date}`,
              alignment: "right",
              margin: [5, 0],
              fontSize: 8,
              color: "#555",
            },
            {
              text: `Invoice No. ${this.invoice_no}`,
              alignment: "right",
              margin: [0, 0],
              fontSize: 8,
              color: "#555",
            },
          ],
          margin: [40, 10],
        };
      },

      content: [
        {
          absolutePosition: { x: 249, y: -60 },
          canvas: [
            {
              type: "rect",
              x: 0,
              y: 100,
              w: 120,
              h: 106,
              r: 50,
              color: "#64b38a",
            },
          ],
        },
        {
          table: {
            widths: ["50%", "30%", "20%"],
            body: [
              [
                {
                  image: logoDataURL,
                  width: 100,
                  height: 80,
                },
                {
                  stack: [
                    {
                      text: "INVOICE",
                      fontSize: 23,
                      color: "white",
                      bold: true,
                      margin: [0, 0, 0, 10],
                    },
                    { text: "Invoice #", style: "header1" },
                    { text: "Issue Date:", style: "header1" },
                    { text: "Due Date:", style: "header1" },
                  ],
                  fillColor: "#64b38a",
                  margin: [0, 10, 0, 10],
                },
                {
                  stack: [
                    { text: "  " },
                    { text: this.invoice_no, style: "headerText" },
                    { text: this.currentDate, style: "headerText" },
                    { text: formdata.due_Date, style: "headerText" },
                  ],
                  fillColor: "#64b38a",
                  margin: [0, 34, 0, 10],
                },
              ],
            ],
          },
          fillColor: "#f3f3f3",
          layout: {
            defaultBorder: false,
          },
        },
        {
          columns: [
            {
              width: "50%",
              stack: [
                { text: "Invoice To", style: "subheader" },
                { text: formdata.invoice_to.name, bold: true, fontSize: 12 },
                {
                  text: "Email: " + formdata.invoice_to.email,
                  style: "centerright",
                },
                {
                  text: "Phone: " + formdata.invoice_to.phone,
                  style: "centerright",
                },
                {
                  text: "ABN: " + formdata.invoice_to.abn,
                  style: "centerright",
                },
              ],
              margin: [0, 20, 0, 20],
            },
            {
              width: "50%",
              stack: [
                { text: "Invoice From", style: "subheader", alignment: "left" },
                {
                  text: formdata.invoice_from.name,
                  bold: true,
                  alignment: "left",
                  fontSize: 12,
                },
                {
                  text: "Email: " + formdata.invoice_from.email,
                  style: "centerleft",
                },
                {
                  text: "Phone: " + formdata.invoice_from.phone,
                  style: "centerleft",
                },
                {
                  text: "ABN: " + formdata.invoice_from.abn,
                  style: "centerleft",
                },
              ],
              alignment: "right",
              margin: [25, 20, 0, 20],
            },
          ],
          margin: [0, 5, 0, 5],
        },
        {
          canvas: [
            {
              type: "line",
              x1: 0,
              y1: 10,
              x2: 515,
              y2: 10,
              lineWidth: 5,
              lineColor: "#c6c3c3",
            },
          ],
          margin: [0, 0, 0, 5],
        },
        // Table
        {
          table: {
            headerRows: 1,
            widths: ["auto", "20%", "auto", "auto", "20%", "auto", "auto", "auto"],
            body: generateTableBody(),
            dontBreakRows: true,
          },

          layout: {
            hLineWidth: function (i, node) {
              return i === 0 || i === 1 || i === node.table.body.length ? 2 : 1;
            },
            vLineWidth: function () {
              return 1;
            },
            hLineColor: function (i) {
              return i === 0 || i === 1 ? "#000" : "#ccc";
            },
            vLineColor: function () {
              return "#ccc";
            },
          },
          margin: [0, 5, 0, 5],
        },

        {
          columns: [
            {
              width: "60%",

              table: {
                widths: [300],
                heights: [10, 60],
                body: [
                  [
                    {
                      text: "Additional Notes",
                      style: "subheader",
                      alignment: "left",
                    },
                  ],
                  [
                    {
                      text: formdata.notes,
                      alignment: "justify",
                      margin: [2, 2, 4, 2],
                    },
                  ],
                ],
              },
              layout: {
                hLineColor: function (i, node) {
                  return i === 0 || i === node.table.body.length
                    ? "#CCC"
                    : "#CCC";
                },
                vLineColor: function (i, node) {
                  return i === 0 || i === node.table.body.length
                    ? "#CCC"
                    : "#CCC";
                },
                vLineWidth: function (i, node) {
                  return i === 0 || i === node.table.widths.length ? 1 : 0;
                },
              },
            },

            {
              width: "40%",

              table: {
                widths: [80, 100],
                body: [
                  [
                    {
                      colSpan: 2,
                      text:
                        formdata.paymentMethod === "bank_transfer"
                          ? "Bank Transfer\nAccount Detials"
                          : formdata.paymentMethod === "bpay"
                          ? "B-Pay\n Account Details"
                          : "",
                      style: "subheader",
                    },
                    "",
                  ],

                  ...rows,
                ],
              },
              margin: [10, 0, 0, 0],
              layout: {
                hLineColor: function (i, node) {
                  return i === 0 || i === node.table.body.length
                    ? "#CCC"
                    : "#CCC";
                },
                vLineColor: function (i, node) {
                  return i === 0 || i === node.table.body.length
                    ? "#CCC"
                    : "#CCC";
                },
                vLineWidth: function (i, node) {
                  return i === 0 || i === node.table.widths.length ? 1 : 0;
                },
              },
            },
            ,
          ],
          margin: [0, 5, 0, 5],
        },
      ],
      styles: {
        header1: {
          color: "white",
          bold: true,
          margin: [23, 0, 0, 0],
        },
        headerText: {
          color: "white",
          alignment: "right",
          margin: [0, 0, 10, 0],
        },
        header: {
          fontSize: 15,
          bold: true,
        },
        subheader: {
          fontSize: 14,
          bold: true,
          margin: [0, 0, 0, 10],
          color: "#535353",
        },
        centerright: {
          fontSize: 10,
          margin: [0, 2, 0, 2],
        },

        centerleft: {
          alignment: "left",
          fontSize: 10,
          margin: [0, 2, 0, 2],
        },
        accountdetials: {
          fontSize: 12,
          bold: true,
          margin: [0, 3, 0, 5],
          color: "#535353",
        },
        tableHeader: {
          bold: true,
          fontSize: 10,
          color: "#ccc",
        },

        tableBody: {
          fontSize: 2,
        },
        anotherStyle: {
          alignment: "justify",
        },
      },
    };

    // PDF actions
    if (action === "download") {
      pdfMake.createPdf(docDefinition).download(pdfFilename);
    } else if (action === "print") {
      pdfMake.createPdf(docDefinition).print();
    } else {
      pdfMake.createPdf(docDefinition).open();
    }
  }

  formatCurrency(value: number, currency: string): string {
    let formattedValue: string;

    if (currency === "AUD") {
      formattedValue = new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
      })
        .format(value)
        .replace("US", "");
    } else {
      formattedValue = new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: currency,
      }).format(value);
    }

    return formattedValue;
  }
}

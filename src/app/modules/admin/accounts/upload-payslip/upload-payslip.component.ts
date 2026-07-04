import { HttpHeaders } from '@angular/common/http';
import { ChangeDetectorRef, Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ChargeRateService } from 'app/services/charge-rate.service';
import { CustomerService } from 'app/services/customer.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import moment from 'moment';

@Component({
  selector: 'app-upload-payslip',
  templateUrl: './upload-payslip.component.html',
  styleUrls: ['./upload-payslip.component.scss']
})

export class UploadPayslipComponent implements OnInit {

  dateRange: any;
  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  selectedFile: File | null = null;
  startDate: Date | null = null;
  endDate: Date | null = null;
  paySlipList: any[];
  getPaySlip: any[];
  file: any;
  guards = [];

  constructor(private modalService: NgbModal, public chargeRateService: ChargeRateService,  public toastService: ToastServiceService,
    private cusService: CustomerService, private userService: StaffService, private cdr: ChangeDetectorRef) { }

  ngOnInit(): void {

    this.chargeRateService.inactiveChargeRates().subscribe(
      (res) => {
        if (res.success) {
          this.getAllStaff();
          this.getPayslipData();
        }
        this.cdr.detectChanges();
      },
      error => {
        console.log(error);
      }
    );
  }

  getAllStaff() {
    let data = {
      guard_status: '',
      pageIndex: 0,
      pageSize: 10000,
      length: 0,
    }
    this.userService.getStaff(data).subscribe(
      (res) => {
        if (res.success) {
          res.data.forEach((element) => {
            element.name = [
              element.first_name,
              element.middle_name,
              element.last_name,
            ]
            .filter(Boolean)
            .join(" ");
          });
        }
        // this.guards = res.data;
        this.guards = res.data.filter((guard: any) => guard.guard_status === 'active');
        this.cdr.detectChanges();
      },
      error => {
        console.log(error);
      }
    );
  }

  guardsIds
  fromMultiGuard
  receiveDataFromChildGuards(data: any) {
    this.fromMultiGuard = data;
    this.guardsIds = this.fromMultiGuard.value.map((item) => item.id);
    this.getPayslipData();
  }

  getStartEnd(event: any) {
    console.log('Date range event:', event);
    this.dateRange = event || { start: null, end: null };
    if (this.dateRange.start && this.dateRange.end) {
      this.getPayslipData();
    } else {
      console.warn('Invalid date range:', this.dateRange);
      this.getPaySlip = [];
      this.cdr.detectChanges();
    }
  }

  openLg(uploadModal) {
    this.modalService.open(uploadModal, { size: 'md', centered: true });
  }

  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.selectedFile = input.files[0];
    }

    const myFormData = new FormData();
    const headers = new HttpHeaders({
      AuthorizationToken: "Bearer " + localStorage.getItem("accessToken")
    });
    myFormData.append("file", this.selectedFile, this.selectedFile.name);
    myFormData.append("folder", "payslip");
    this.cusService.uploadImgPdf(myFormData, { headers: headers}).subscribe((response) => {
      if (response.success) {
        this.file = response.path;
      } else {
        this.toastService.toastNotification1("Something went wrong please check your file size or connection", "File Upload!");
      }
    },
    (error) => {
      console.error(error);
    });
  }

  submitForm(modal: any) {
    if (!this.selectedFile) {
      this.toastService.toastNotification1('Please upload a file.', 'Payslip Operation');
      return;
    }

    let data = {
      start_date: this.startDate ? moment(this.startDate).format('YYYY-MM-DD') : null,
      end_date: this.endDate ? moment(this.endDate).format('YYYY-MM-DD') : null,
      pdf: this.file
    }
    this.chargeRateService.addPayslip(data).subscribe((response) => {
      if (response.success) {
        this.paySlipList = response.data;
        this.toastService.toastNotification('Payslip uploaded successfully!', 'Payslip Operation');
        this.selectedFile = null; 
        this.getPayslipData();
        modal.close('Submit click');
      }
      else {
        this.toastService.toastNotification1(response.message, 'Payslip Operation')
      }
    });
  }

  getPayslipData() {
    const data = {
      start_date: this.dateRange.start
        ? moment(this.dateRange.start).format('YYYY-MM-DD')
        : null,
      end_date: this.dateRange.end
        ? moment(this.dateRange.end).format('YYYY-MM-DD')
        : null,
      guard_id: this.guardsIds,
    };

    console.log('Fetching payslip data with:', data); 

    this.chargeRateService.getAllPayslips(data).subscribe(
      (response) => {
        if (response.status) {
          this.getPaySlip = response.data || [];
          this.cdr.detectChanges(); // Trigger change detection after updating data
        } else {
          this.getPaySlip = [];
          this.toastService.toastNotification1(response.message, 'Payslip Operation');
          this.cdr.detectChanges();
        }
      },
      (error) => {
        console.error('Error fetching payslip data:', error);
        this.getPaySlip = [];
        this.toastService.toastNotification1('Failed to fetch payslip data.', 'Payslip Operation');
        this.cdr.detectChanges();
      }
    );
  }

  downloadFile(fileUrl: string) {
    if (fileUrl) {
      const link = document.createElement('a');
      link.href = fileUrl;
      link.target = '_blank';
      link.download = fileUrl.split('/').pop() || 'payslip.pdf';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    } else {
      this.toastService.toastNotification1('File URL not available.', 'Download Error');
      this.getPayslipData();
    }
  }

}

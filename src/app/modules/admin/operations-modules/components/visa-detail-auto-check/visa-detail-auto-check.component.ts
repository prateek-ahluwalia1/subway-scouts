import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { DateAdapter } from '@angular/material/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { PermissionsService } from 'app/services/permissions.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { NgxSpinnerService } from 'ngx-spinner';

interface ApiResponse {
  message: string;
  results: any[];
  status: string;
}

@Component({
  selector: 'app-visa-detail-auto-check',
  templateUrl: './visa-detail-auto-check.component.html',
  styleUrls: ['./visa-detail-auto-check.component.scss']
})
export class VisaDetailAutoCheckComponent implements OnInit {


  filteredVisaData: any[] = [];
  originalData: any = []
  results: any = []
  adminPermissions: any;
  adminData: any;

  constructor(private modalService: NgbModal, private staffService: StaffService,
    private toast: ToastServiceService, private permissionService: PermissionsService, private global: GlobalVariable,
    public dateAdapter: DateAdapter<Date>, private http: HttpClient, private spinner: NgxSpinnerService,) {
    const per = this.permissionService.getPermissionsByTitle('WFM Tools');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Visa Status');
    console.log(this.adminPermissions);

    // this.dateAdapter.setLocale('en-AU');
  }

  ngOnInit(): void {

    const adminObject = localStorage.getItem("admin")
    this.adminData = JSON.parse(adminObject);

    this.getRecord();

  }

  openLg(content, user) {
    console.log(this.originalData);
    this.results = this.originalData.find(item => item.guard_id == user.id); // Add user variable if needed
    console.log(this.results);
    this.modalService.open(content, { size: 'lg' });
  }


  getRecord() {
    this.staffService.getVisaRecord().subscribe(({ data, success }) => {
      if (success) {
        this.originalData = data;
        this.filteredVisaData = data.map((item) => {
          return {
            name: item?.name || 'N/A',
            id: item?.guard_id,
            passport: item?.action_type?.results?.find((result) => result.label === 'Document number')?.value || item?.passport_no || 'N/A',
            country: item?.country || 'N/A',
            dob: item?.dob || 'N/A',
            visa: item?.action_type?.results?.find((result) => result.label === 'Visa description')?.value || 'N/A',
            visa_exp: item?.action_type?.results?.find((result) => result.label === 'Visa expiry date')?.value || 'N/A',
            status: item?.action_type?.results?.find((result) => result.label === 'Visa stream')?.value || 'N/A',
            is_correct: item?.is_correct,
            visa_class_subclass: item?.action_type?.results?.find((result) => result.label === 'Visa class / subclass')?.value || 'N/A'
          };
        });
      }
    })
  }

  manualVisaVerification(params) {
    const apiKeys = this.global.admin?.apiKeys;
    let data = {
      requestType: params,
      email: apiKeys?.visa_mail,
      password: apiKeys?.visa_password,
    }
    this.staffService.manualVisaVerification(data).subscribe(({ message, success, count }) => {
      if (success && count != 0) {
        this.manualVisaVerification('old')
        this.toast.toastNotification(message, 'Visa/Vivo Operation')
        this.getRecord()
      }
      else if (!success) {
        this.toast.toastNotification1('Something went wrong', 'Visa/Vivo Operation')
      }
    }, (error => {
      this.toast.toastNotification1('Something went wrong', 'Visa/Vivo Operation')
    }))
  }

  formattedDate: any;
  getIncompletevisaData(user){

    this.spinner.show();

    console.log("Submit data", user)

    const dobParts = user.dob.split("-"); 
    const formattedDob = `${dobParts[2]}-${dobParts[1]}-${dobParts[0]}`; 
    const parsedDate = new Date(formattedDob);
    
    if (!isNaN(parsedDate.getTime())) {
      this.formattedDate = parsedDate.toLocaleDateString("en-GB", {
        day: "numeric",
        month: "long",
        year: "numeric"
      });
    }
    let data = {
      date_of_birth:this.formattedDate,
      dob: user.dob,
      email: this.adminData.apiKeys.visa_mail,
      family_name: user.name,
      passport_number: user.passport,
      password: this.adminData.apiKeys.visa_password,
      select_country: user.country,
      guard_id: user.id
    }
    
    console.log("Store data", data)

    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    this.http.post<ApiResponse>('https://apis.thescouts.com.au/api/guard/visa-varification-recheck', data, httpOptions).subscribe((response:any) => {
      console.log('db', response)
      if (response.success) {
        this.getRecord();
        this.spinner.hide();
        this.toast.toastNotification('Visa Rechecking', "Rechecked Succesfully.")
      }
      else {
        this.toast.toastNotification1('Something went wrong', "Request not complete")
      }
    }, 
    (error) => {
      console.error('An error occurred:', error);
      if (error.error && error.error.message) {
        console.error('Server error message:', error.error.message);
        this.spinner.hide();
      }
      this.toast.toastNotification1('Something went wrong', 'Request failed');
      this.spinner.hide();
    }
    );
  }

}


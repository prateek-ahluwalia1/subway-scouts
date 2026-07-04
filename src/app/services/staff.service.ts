import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { catchError, Observable, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class StaffService {

  constructor(private http: HttpClient, private globals: GlobalVariable) { }

  handleError(error: HttpErrorResponse) {
    console.log(error);

    if (error.error instanceof ErrorEvent) {
      console.error('An error occurred:', error.error.message);
    } else {
      console.error(
        `Backend returned code ${error.status}, ` + `body was: ${error.error}`
      );
    }
    return throwError('Something bad happened; please try again later.');
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  //add Quick onboarding new staff///
  public addQickStaff(value): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard/quick-onboarding-staff';
    // return this.http.post(link, this.httpOptions)

    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  /////Create new Staff
  public createNewStaff(value): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard/create-new-staff';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));

  }
  /////update new Staff
  public updateStaff(value): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard/update';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));

  }

  //Get All Staff///////
  public getStaff(data?): Observable<any> {
    var link =
      this.globals.baseUrl + 'get-all-guards';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public changeAdminApprovalStatus(data): Observable<any> {
    var link = this.globals.baseUrl + 'guard/active-deactive';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public changeStaffStatus(data): Observable<any> {
    var link = this.globals.baseUrl + 'guard/active-deactive';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ///get specfic staff data when open user-menu model
  getSpecificStaffData(id) {
    var link =
      this.globals.baseUrl + 'guard/edit';
    let data = {
      id: id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  //////////get Customer Site///////
  getCusSite(id) {
    var link =
      this.globals.baseUrl + 'get-sites-by-customers';
    let data = {
      customers: id,
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  /////get payrate function call in employement details component
  getPayrate(payrate_level, state) {
    var link =
      this.globals.baseUrl + 'get-payrates-with-level-and-state';
    let data = {
      state: state,
      level: payrate_level
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  //////upload Staff Docuemnt 
  uploadStaffDocument(value): Observable<any> {
    value.admin_id = this.globals.admin.admin_id
    var link = this.globals.baseUrl + 'guard-update-employment-details';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getStaffDocument(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'edit-employment-details';
    let data = {
      id: id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ////3rd Tab Add Document 
  public staffAddDoc(value): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard-add-documents';

    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1));
  }
  public editGuardDoc(value): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard-edit-documents';

    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ////3rd Tab Update Document 
  public staffUpdateDoc(value): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard-update-documents';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ///get staff All Doc When Click tabs3
  getStaffAddDocument(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard-all-documents';
    let data = {
      id: id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  guardDocumenType(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard-document-type';
    let data = {
      id: id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  uploadImgPdf(formData, headers): Observable<any> {
    var link =
      this.globals.baseUrl + 'upload-file';
    console.log(link);

    return this.http
      .post<any>(link, formData, headers)
      .pipe(retry(1), catchError(this.handleError));
  }

  ///Delete specific staff doc
  delAddDoc(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard-delete-documents';
    let data = {
      id: id,
      admin_id: this.globals.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError)); {

    }
  }

  //del Staff
  public delStaff(id, reason): Observable<any> {
    let link = this.globals.baseUrl + 'guard/delete';
    let data = {
      id: id,
      reason: reason,
      admin_id: this.globals.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  // administor Feedback about staff
  public adminFeedback(data): Observable<any> {
    let link = this.globals.baseUrl + 'save-feedback';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }
  public getFeedback(id): Observable<any> {
    let data = {
      guard_id: id
    }
    let link = this.globals.baseUrl + 'show-guard-feedback';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }
  public update_Feedback(data): Observable<any> {

    let link = this.globals.baseUrl + 'update-feedback';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }
  public delete(data): Observable<any> {

    let link = this.globals.baseUrl + 'delete-feedback';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  // get Guards By Status

  restoreGuard(id): Observable<any> {
    console.log("restoreGuard ", id);

    var link =
      this.globals.baseUrl + 'guard/restore';
    let data = {
      id: id,
      admin_id: this.globals.admin.admin_id
    }

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  saveIds(value): Observable<any> {
    var link =
      this.globals.baseUrl + 'add-guard-internal-external-ids';

    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getStaffIds(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'edit-guard-internal-external-ids';
    let data = {
      guard_id: id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  delStaffIds(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'delete-external-id';
    let data = {
      id: id,
      admin_id: this.globals.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // this section for guard leave
  addLeave(data): Observable<any> {
    var link =
      this.globals.baseUrl + 'addAdminLeaveRequest';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getstaffLeave(): Observable<any> {
    var link = this.globals.baseUrl + 'getLeaveDetails';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getLeaveRequest(data): Observable<any> {
    var link =
      this.globals.baseUrl + 'getPendingLeaveRequests';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  addAdminLeaveRequest(data): Observable<any> {
    var link =
      this.globals.baseUrl + 'addAdminLeaveRequest';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getGuard(): Observable<any> {
    var link =
      this.globals.baseUrl + 'getLeaveGuards';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  approveLeave(data): Observable<any> {
    var link =
      this.globals.baseUrl + 'approveLeave';
    data.admin_id = this.globals.admin.admin_id
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getInvoice(data): Observable<any> {
    var link =
      this.globals.baseUrl + 'get-roaster-hour-sum';
    // data.admin_id = this.globals.admin.admin_id

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  sendInvoiceToCustoemr(formData): Observable<any> {
    var link = this.globals.baseUrl + 'customer-invoice-store';
    const headersInvoice = new HttpHeaders({
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    });
    return this.http
      .post<any>(link, formData, { headers: headersInvoice })
      .pipe(retry(1), catchError(this.handleError));
  }

  downloadInvoice(formData): Observable<any> {
    var link = this.globals.baseUrl + 'download-invoice-form';
    const headersInvoice = new HttpHeaders({
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    });
    return this.http
      .post<any>(link, formData, { headers: headersInvoice })
      .pipe(retry(1), catchError(this.handleError));
  }

  // adduniform(selectedUniforms, guardId, customerId, adminId): Observable<any> {


  //   var formdata = new FormData();
  //   formdata.append("unifrom_type", selectedUniforms);
  //   formdata.append("guard_id", guardId);

  //   formdata.append("customer_id", customerId);

  //   formdata.append("admin_id", adminId);

  //   var link =
  //     this.globals.baseUrl + 'save-and-update-staff-uniform-detials';
  //   let data = {
  //     uniform_type: selectedUniforms,
  //     admin_id: this.globals.admin.admin_id,
  //     customer_id: customerId,
  //     guard_id: guardId,

  //   }
  //   return this.http
  //     .post<any>(link, data, this.httpOptions)
  //     .pipe(retry(1), catchError(this.handleError));
  // }

  adduniform(data): Observable<any> {
    var link =
      this.globals.baseUrl + 'save-staff-uniform-detials';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  updateuniform(data): Observable<any> {
    var link =
      this.globals.baseUrl + 'update-staff-uniform-detials';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getProfileTracker(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'get-guard-profile-traker';
    let data = {
      login_user_id: localStorage.getItem('admin_id'),
      guard_id: id

    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getUniforms(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'get-staff-uniform-detials';
    let data = {
      id: id,
      //   admin_id:this.globals.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getUniformDetail(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'edit-staff-uniform-detials';
    let data = {
      id: id,
      //   admin_id:this.globals.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getShiftRecords(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'get-guards-before-and-after-week-shift';
    let data = {
      guard_id: id,
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  addStaffAvailbility(data): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard/update_guard_avability';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getStaffAvailbility(data): Observable<any> {
    var link =
      this.globals.baseUrl + 'guard/get_guard_avability';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // get staff dob 
  getDataforVisaCheck(id): Observable<any> {
    var link = this.globals.baseUrl + 'guard/get_guard_avability';

    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  documentsOnlineVerification(id, no): Observable<any> {
    let data = {
      guard_id: id,
      license_number: no
    }
    var link = this.globals.baseUrl + 'documents-online-verification';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  delInduction(id): Observable<any> {
    let data = {
      id: id,
      admin_id: this.globals.admin.admin_id
    }
    var link = this.globals.baseUrl + 'delete-guard-induction';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  saveSites(value): Observable<any> {
    var link =
      this.globals.baseUrl + 'save-update-trained-guard-on-site';

    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getSites(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'edit-trained-guard-on-site';
    let data = {
      guard_id: id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  delSites(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'delete-trained-guard-on-site';
    let data = {
      id: id,
      admin_id: this.globals.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getVisaRecord(): Observable<any> {
    var link = this.globals.baseUrl + 'guard/get-visa-details';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  manualVisaVerification(data): Observable<any> {
    var link = this.globals.baseUrl + 'guard/manual-visa-verification';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getStaffLeaves(guard_id): Observable<any> {
    var link = `${this.globals.baseUrl}get-guard-leaves/${guard_id}`;
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  searchStaff(data): Observable<any> {
    var link = `${this.globals.baseUrl}find-guard`;
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  searchLocation(data): Observable<any> {
    var link = `${this.globals.baseUrl}find-sites`;
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getLocation(): Observable<any> {
    var link = `${this.globals.baseUrl}get-location`;
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getUniformHistory(id): Observable<any> {
    let data = {
      guard_id: id
    }
    let link = this.globals.baseUrl + 'staffUniFormActivity';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  googleAuthenticator() {
    let link = this.globals.baseUrl + 'authenticationQr';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1));
  }

  statusRecord(id) {
    let data = {
      guard_id: id
    }
    let link = this.globals.baseUrl + 'staff-status-acitvity';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  sendEmailAgain(data) {
    let link = this.globals.baseUrl + 'again-guard-email-verification';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  delStaffDoc(data) {
    let link = this.globals.baseUrl + 'guard/delete-document';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  updateLeave(data) {
    var link =
      this.globals.baseUrl + 'guard/update-leaves';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
}

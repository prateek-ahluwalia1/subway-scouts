import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { BehaviorSubject, catchError, Observable, retry, tap, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class JobRoster1Service {

  private _sites: BehaviorSubject<any> = new BehaviorSubject(null);
  private _job: BehaviorSubject<any> = new BehaviorSubject(null);
  private _roster: BehaviorSubject<any> = new BehaviorSubject(null);
  private _allRoster: BehaviorSubject<any> = new BehaviorSubject(null);

  private dataSubject = new BehaviorSubject<any[]>([]);
  data$: Observable<any[]> = this.dataSubject.asObservable();
  constructor(private http: HttpClient, private global: GlobalVariable) { }

  handleError(error: HttpErrorResponse) {
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

  get sites$(): Observable<any> {
    return this._sites.asObservable();
  }
  clearSitesData(): void {
    this._sites.next(null); // Replace with how you clear the data in your service
  }


  get job$(): Observable<any> {
    return this._job.asObservable();
  }

  get roster$(): Observable<any> {
    return this._roster.asObservable();
  }

  get allRoster$(): Observable<any> {
    return this._allRoster.asObservable();
  }

  getNewRoster(status, state): Observable<any> {
    var link =
      this.global.baseUrl + 'job-new-roster/get-all';
    let params = {
      status: status,
      id: this.global.admin.admin_id,
      type: this.global.admin.admin_user_type,
      state: state,
    }
    return this.http
      .post<any>(link, params, this.httpOptions).pipe(
        tap((response) => {
          this._allRoster.next(response);
        })
      );
  }

  accessRoster(id): Observable<any> {
    var link = this.global.baseUrl + 'get-newjobroster-customers-name';
    let params = {
      user_id: this.global.admin.admin_id,
      id: id
    }
    return this.http.post<any>(link, params, this.httpOptions).pipe(
      tap((response) => {
        this._roster.next(response);
      })
    );
    // return this.http
    //   .post<any>(link, params, this.httpOptions)
    //   .pipe(retry(1));
  }

  changeStatus(params): Observable<any> {
    var link =
      this.global.baseUrl + 'update-jobRoster-status';
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1));
  }

  editRoster(id): Observable<any> {
    var link = this.global.baseUrl + 'edit-job-new-roster';
    let params = {
      id: id
    }
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1));
  }
  /////get Custom Template of roster
  getTemplate() {
    var link =
      this.global.baseUrl + 'fetch-template-shifts';
    return this.http
      .post<any>(link, '', this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getFilterData(params) {

    var link = this.global.baseUrl + 'fetch-customer-sites';
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError),
        tap((response) => {
          this._sites.next(response);
        }));
  }

  public addShift(value): Observable<any> {
    value.admin_id = this.global.admin.admin_id
    var link =
      this.global.baseUrl + 'add-new-shift';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
  // public updateShift(value): Observable<any> {
  //   value.admin_id = this.global.admin.admin_id
  //   var link =
  //     this.global.baseUrl + 'update-shift';
  //   return this.http
  //     .post<any>(link, value, this.httpOptions)
  //     .pipe(retry(1), catchError(this.handleError));
  // }

  public updateShift(value): Observable<any> {
    value.admin_id = this.global.admin.admin_id
    var link =
      this.global.baseUrl + 'add-new-shift';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ////get site of guards
  getGuardSite(params) {
    var link =
      this.global.baseUrl + 'get-guards-sites';
    let data = {
      id: params,
      date: moment().format('YYYY-MM-DD')
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
  ////get specific roster data getGuardSite
  getSpecRoster(params) {
    var link =
      this.global.baseUrl + 'edit-job-roster';
    let data = {
      id: params
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
  ////get specific roster data
  delShift(params) {
    var link =
      this.global.baseUrl + 'delete-shift';
    let data = {
      id: params.id,
      reason: params.reason,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  updateTime(prams) {
    var link =
      this.global.baseUrl + 'update-roster-time';
    return this.http
      .post<any>(link, prams, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ////////shift Drag and drop
  public dragDrop(value): Observable<any> {
    var link = this.global.baseUrl + 'shift-drop-and-copy';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public copyShift(value): Observable<any> {
    var link = this.global.baseUrl + 'shift-drop-and-copy';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ////getting the polling data
  public polling(value): Observable<any> {
    var link =
      this.global.baseUrl + 'fetch-customer-updated-sites';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError),
        tap((response) => {
          this._job.next(response)
        }));
  }

  ////getting the polling data
  public delShiftTask(data): Observable<any> {
    var link =
      this.global.baseUrl + 'delete-job-roster-task';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public jobSignInOut(data): Observable<any> {
    var link =
      this.global.baseUrl + 'get-jobSignIn-jobSignOut';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public staffData(data): Observable<any> {
    var link =
      this.global.baseUrl + 'get-staff-data';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public breadkDetails(data): Observable<any> {
    var link =
      this.global.baseUrl + 'guard-break-details';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public welfareCall(data): Observable<any> {
    var link =
      this.global.baseUrl + 'guard-welfarecall';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public trackerCall(data): Observable<any> {
    var link =
      this.global.baseUrl + 'guard-jobTraker';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public incidentReport(data): Observable<any> {
    var link =
      this.global.baseUrl + 'guard-incident-report';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public greenCall(data): Observable<any> {
    var link =
      this.global.baseUrl + 'guard-greencall-details';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public operationNotes(data): Observable<any> {
    var link =
      this.global.baseUrl + 'store-operation-notes';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public operationDelete(data): Observable<any> {
    var link =
      this.global.baseUrl + 'delete-operation-notes';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getNotes(data): Observable<any> {
    var link =
      this.global.baseUrl + 'get-operation-notes';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ///publish all shifts at once
  publishAllShift(params) {
    var link =
      this.global.baseUrl + 'fetch-customer-unpublish-sites';
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ///get site of current where user stand or calender start end
  //this is action for copied bulked shifts
  getSiteOfCurrentWeek() {
    var link =
      this.global.baseUrl + 'copy-shifts-sites';

    let params = {
      start: this.global.start,
      end: this.global.end,
    }
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getCopyShiftsSitesByCustomer(customerId) {
    var link =
      this.global.baseUrl + 'copy-shifts-sites-by-customer';

    let params = {
      start: this.global.start,
      end: this.global.end,
      customer_id: customerId
    }
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  copiedShiftsToSite(params) {
    var link =
      this.global.baseUrl + 'copy-roster-next-dates';

    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  rosterActions(type, ids, userType, roster_id, site_ids) {
    console.log(userType);

    var link =
      this.global.baseUrl + 'roster-actions';
    if (userType == 'guard') {
      let data = {
        start: this.global.start,
        end: this.global.end,
        guardIds: site_ids,
        type: type,
        admin_id: this.global.admin.admin_id,
        customer_ids: ids,
        roster_id: roster_id
      }
      return this.http
        .post<any>(link, data, this.httpOptions)
        .pipe(retry(1), catchError(this.handleError));
    }
    else {
      let data = {
        start: this.global.start,
        end: this.global.end,
        siteIds: site_ids,
        type: type,
        admin_id: this.global.admin.admin_id,
        customer_ids: ids,
        roster_id: roster_id
      }
      return this.http
        .post<any>(link, data, this.httpOptions)
        .pipe(retry(1), catchError(this.handleError));
    }

  }

  getGuadSiteCustomer(site, cus) {
    var link =
      this.global.baseUrl + 'filter-add-guard-on-site';
    let data = {
      customer_id: cus,
      site_id: site,
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  addGuard(id, side_id, action?) {
    var link =
      this.global.baseUrl + 'add-customer-site-guard';
    let data = {
      guard_id: id,
      site_id: side_id,
      action: action,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  submitMultipleShifts(value) {
    var link =
      this.global.baseUrl + 'roster-multiple-shifts';

    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getAdhocGuard(value) {
    var link =
      this.global.baseUrl + 'inradius-guards';

    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  adHocShift(value) {
    var link =
      this.global.baseUrl + 'asap-job';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  addLeave(value) {
    var link =
      this.global.baseUrl + 'guardOnLeave';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getBreakDetail(data) {
    var link =
      this.global.baseUrl + 'guard-break-details';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getShiftActivity(data) {
    var link =
      this.global.baseUrl + 'get-shift-activity';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getshiftTask(data) {
    var link =
      this.global.baseUrl + 'get-job-tasks';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  checkStaffAvailbility(status, data?) {
    if (status) {
      var link = this.global.baseUrl + 'get-available-guards';
      return this.http
        .post<any>(link, data, this.httpOptions)
        .pipe(retry(1), catchError(this.handleError));
    }
    else {
      var link = this.global.baseUrl + 'available-guards';
      return this.http
        .get<any>(link, this.httpOptions)
        .pipe(retry(1), catchError(this.handleError));
    }
  }

  pdfshiftTask(data) {
    var link =
      this.global.baseUrl + 'generateJobTaskReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  adminNote(data) {
    var link = this.global.baseUrl + 'save-admin-shift-activity'
    return this.http.post<any>(link, data, this.httpOptions).pipe(
      retry(1), catchError(this.handleError));
  }

  getadminactivity(data) {
    var link = this.global.baseUrl + 'get-admin-shift-activity';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  deletenote(id): Observable<any> {
    var link = `${this.global.baseUrl}delete-admin-shift-activity/${id}`;
    return this.http.get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  generateRosterReportNormal(data) {
    var link = this.global.baseUrl + 'generateRosterReportNormal';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  generateRosterReport(data) {
    var link = this.global.baseUrl + 'generateRosterReportDivNormal';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public footPatrolReport(data): Observable<any> {
    var link =
      this.global.baseUrl + 'guard-foot-patrol-report';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public saveEditTask(data): Observable<any> {
    var link =
      this.global.baseUrl + 'update-shift-task';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }


  public savePetroleDetail(data): Observable<any> {
    var link =
      this.global.baseUrl + 'update-foot-patrol-report';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public giveRating(data):Observable<any>{
    var link =
      this.global.baseUrl + 'jobroster-give-rating';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public patrolling_report(data):Observable<any>{
    var link =
      this.global.baseUrl + 'guard-patrolling-report';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public generatePatrollingPdf(data: any): Observable<any> {
    const link = `${this.global.baseUrl }generate-patrolling-pdf`;
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public copyDaywiseShift(data):Observable<any>{
    var link = this.global.baseUrl + 'copyShiftNew';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public deleteMultipleShifts(data):Observable<any>{
    var link = this.global.baseUrl + 'roster-bulk-delete';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  pdfshiftActivity(data) {
    var link =
      this.global.baseUrl + 'download-shift-activity';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }


}

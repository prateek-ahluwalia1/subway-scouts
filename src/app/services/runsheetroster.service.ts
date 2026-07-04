import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { BehaviorSubject, Observable, Subject, catchError, retry, tap, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class RunsheetrosterService {

  private _allRoster: BehaviorSubject<any> = new BehaviorSubject(null);
  private _roster: BehaviorSubject<any> = new BehaviorSubject(null);
  private _sites: BehaviorSubject<any> = new BehaviorSubject(null);
  private _job: BehaviorSubject<any> = new BehaviorSubject(null);

  private publishShiftDataSubject = new Subject<any>();
  publishShiftData$ = this.publishShiftDataSubject.asObservable();

  private dataSubject = new BehaviorSubject<any[]>([]);
  data$: Observable<any[]> = this.dataSubject.asObservable();

  constructor(private http: HttpClient, private global: GlobalVariable) { }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  handleError(error: HttpErrorResponse) {
    if (error.error instanceof ErrorEvent) {
      // A client-side or network error occurred. Handle it accordingly.
      console.error('An error occurred:', error.error.message);
    } else {
      // The response body may contain clues as to what went wrong,
      console.error(
        `Backend returned code ${error.status}, ` + `body was: ${error.error}`
      );
    }
    // return an observable with a user-facing error message
    return throwError('Something bad happened; please try again later.');
  }

  get allRoster$(): Observable<any> {
    return this._allRoster.asObservable();
  }

  get roster$(): Observable<any> {
    return this._roster.asObservable();
  }

  get sites$(): Observable<any> {
    return this._sites.asObservable();
  }

  get job$(): Observable<any> {
    return this._job.asObservable();
  }

  publishShiftData(data: any) {
    this.publishShiftDataSubject.next(data);
  }

  clearSitesData(): void {
    this._sites.next(null);
  }

  createRoster(value): Observable<any>{
    var link = this.global.baseUrl + 'runsheet-roster/store';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getRoster(status): Observable<any>{
    var link = this.global.baseUrl + 'runsheet-roster/all';
    let params = {
      status: status,
      id: this.global.admin.admin_id,
      type: this.global.admin.admin_user_type,
    }
    return this.http
    .post<any>(link, params, this.httpOptions).pipe(
      tap((response) => {
        this._allRoster.next(response);
      })
    );
  }

  editRoster(id): Observable<any>{
    var link = this.global.baseUrl + 'runsheet-roster/edit';
    let params = {
      id: id
    }
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1));
  }

  updateRoster(data){
    var link = this.global.baseUrl + 'runsheet-roster/update';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  updateStatus(data){
    var link = this.global.baseUrl + 'runsheet-roster/update-status';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  accessRoster(id): Observable<any> {
    var link = this.global.baseUrl + 'get-runsheet-customers-name';
    let params = {
      user_id: this.global.admin.admin_id,
      id: id
    }
    return this.http.post<any>(link, params, this.httpOptions).pipe(
      tap((response) => {
        this._roster.next(response);
      })
    );
  }

  getFilterData(params) {
    var link = this.global.baseUrl + 'fetch-runsheet-shifts';
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError),
        tap((response) => {
          this._sites.next(response);
        }));
  }

  getCusSite(id) {
    var link =
    this.global.baseUrl + 'get-runsheet-by-customers';
    let data = {
      customers: id,
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getGuadSiteCustomer(site, cus) {
    var link =
    this.global.baseUrl + 'filter-add-guard-on-runsheet';
    let data = {
      customer_id: cus,
      run_sheet_id: site,
    }
    return this.http
    .post<any>(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError));
  }

  addGuard(id, side_id, action?) {
    var link =
      this.global.baseUrl + 'add-customer-runsheet-guard';
    let data = {
      guard_id: id,
      run_sheet_id: side_id,
      action: action,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getGuardBySite(value) {
    var link =
      this.global.baseUrl + 'get-guard-by-runsheet';
    let data = {
      runsheet_id: value
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public addShift(value): Observable<any> {
    value.admin_id = this.global.admin.admin_id
    var link =
      this.global.baseUrl + 'add-new-runsheet-shift';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getGuardSite(params) {
    var link =
      this.global.baseUrl + 'runsheet-roster/get-guards-runsheet';
    let data = {
      id: params
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  publishAllShift(params) {
    var link =
      this.global.baseUrl + 'runsheet-roster/fetch-customer-unpublish-sites';
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  publishedShifts(params) {
    var link =
      this.global.baseUrl + 'runsheet-roster/publish-shifts';
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }


  /// For Specific Shifts
  public delShiftTask(data): Observable<any> {
    var link =
      this.global.baseUrl + 'runsheet-roster/delete-run-sheet-task';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getTemplate() {
    var link =
      this.global.baseUrl + 'runsheet-roster/fetch-template-shifts';
    return this.http
      .post<any>(link, '', this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getSpecShift(params) {
    var link =
      this.global.baseUrl + 'edit-runsheet-shift';
    let data = {
      id: params
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public updateShift(value): Observable<any> {
    value.admin_id = this.global.admin.admin_id
    var link =
      this.global.baseUrl + 'update-runsheet-shift';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  updateTime(prams) {
    var link =
      this.global.baseUrl + 'runsheet-roster/update-runsheet-time';
    return this.http
      .post<any>(link, prams, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  delShift(params) {
    var link =
      this.global.baseUrl + 'delete-runsheet-shift';
    let data = {
      id: params.id,
      reason: params.reason,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public dragDrop(value): Observable<any> {
    var link = this.global.baseUrl + 'shift-drop-copy-runsheet-shift';
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

  public getDeletedShifts(value): Observable<any> {
    var link = this.global.baseUrl + 'runsheet-roster/get-runsheet-deleted-shifts';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  addLeave(value) {
    var link =
      this.global.baseUrl + 'runsheet-roster/guardOnLeavePatrolling';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  rosterActions(type, ids, userType, runsheet, roster_id) {
    var link =
      this.global.baseUrl + 'runsheet-roster/runsheet-actions';
    if (userType == 'guard') {
      let data = {
        start: this.global.start,
        end: this.global.end,
        guardIds: this.global.selectedSitesIds,
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
        type: type,
        admin_id: this.global.admin.admin_id,
        customer_ids: ids,
        run_sheet_roster_id: roster_id,
        runsheets: runsheet
      }
      return this.http
        .post<any>(link, data, this.httpOptions)
        .pipe(retry(1), catchError(this.handleError));
    }
  }

  generateRosterReportNormal(data) {
    var link = this.global.baseUrl + '';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getSiteOfCurrentWeek() {
    var link =
      this.global.baseUrl + 'runsheet-roster/copy-shifts-runsheet';

    let params = {
      start: this.global.start,
      end: this.global.end,
    }
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  copiedShiftsToSite(params) {
    var link =
      this.global.baseUrl + 'runsheet-roster/copy-runsheet-next-dates';

    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
}

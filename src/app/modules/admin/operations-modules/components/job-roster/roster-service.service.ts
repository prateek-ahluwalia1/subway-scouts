import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { catchError, retry, tap } from 'rxjs/operators';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';

@Injectable({
  providedIn: 'root'
})
export class RosterServiceService {
  private _rosterCustomer: BehaviorSubject<any> = new BehaviorSubject<any>(null);
  private _customerSites: BehaviorSubject<any> = new BehaviorSubject<any>(null);
  private _calenderData: BehaviorSubject<any> = new BehaviorSubject<any>(null);

  constructor(private global: GlobalVariable, private http: HttpClient) { }

  private httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  // RosterService
  private calenderTypeSubject = new BehaviorSubject<string>('location');
  calenderType$ = this.calenderTypeSubject.asObservable();

  calenderType(calenderType: string) {
    this.calenderTypeSubject.next(calenderType);
  }

  private viewTypeSubject = new BehaviorSubject<string>('site-week');
  viewType$ = this.viewTypeSubject.asObservable();

  updateViewType(viewType: string) {
    this.viewTypeSubject.next(viewType);
  }

  get rosterCustomer$(): Observable<any> {
    return this._rosterCustomer.asObservable();
  }

  get customerSites$(): Observable<any> {
    return this._customerSites.asObservable();
  }

  get calenderData$(): Observable<any> {
    return this._calenderData.asObservable();
  }

  accessRoster(id: any): Observable<any> {
    const link = `${this.global.baseUrl}get-newjobroster-customers-name`;
    const params = { id: id };

    return this.http.post<any>(link, params, this.httpOptions).pipe(
      retry(1),
      tap((response) => {
        this._rosterCustomer.next(response);
      }),
      catchError(this.handleError) // Handle HTTP errors
    );
  }

  getCusSite(id: any): Observable<any> {
    const link = `${this.global.baseUrl}get-sites-by-customers`;
    const data = { customers: id };
    return this.http.post<any>(link, data, this.httpOptions).pipe(
      retry(1),
      tap((response) => {
        this._customerSites.next(response);
      }),
      catchError(this.handleError) // Handle HTTP errors
    );
  }


  getFilterData(params) {

    var link = this.global.baseUrl + 'fetch-customer-sites';
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1),
        tap((response) => {
          this._calenderData.next(response);
        }),
        catchError(this.handleError));
  }

  public addShift(value): Observable<any> {
    value.admin_id = this.global.admin.admin_id
    var link =
      this.global.baseUrl + 'add-new-shift';
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
    var link = this.global.baseUrl + 'get-roster-deleted-shifts';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  private handleError(error: any): Observable<never> {
    console.error('An error occurred:', error);
    return throwError(error);
  }

  getGuardBySite(id, date?) {
    var link =
      // this.global.baseUrl + 'get-guard-by-site';
      this.global.baseUrl + 'get-active-guards';
    let data = {
      site_id: id,
      date: moment(date?.momentFormat).format('YYYY-MM-DD')
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ////get site of guards
  getGuardSite(id, date?) {
    console.log(this.global.selectedDate);

    var link =
      this.global.baseUrl + 'get-guards-sites';
    let data = {
      id: id,
      date: moment(date?.momentFormat).format('YYYY-MM-DD')
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  publishedShifts(params) {
    var link =
      this.global.baseUrl + 'publish-shifts';

    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  splitShift(params) {
    var link =
      this.global.baseUrl + 'split-shift';
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  checkGuardsGvailibilty(data) {
    var link =
      this.global.baseUrl + 'check-guards-availibilty';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  fullTimerGuard(data) {
    var link =
      this.global.baseUrl + 'get-full-timer-guard-by-site';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getpublishShifts(data){
    var link = this.global.baseUrl + 'getPublishList';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getSelectedPublishShifts(data){
    var link = this.global.baseUrl + 'getSelectedListPublish';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  PublishShifts(data){
    var link = this.global.baseUrl + 'publishRosterNew';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
}

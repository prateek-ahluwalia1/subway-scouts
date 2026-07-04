import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { catchError, Observable, retry, throwError } from 'rxjs';
import { GlobalVariable } from 'app/shared/global';


@Injectable({
  providedIn: 'root'
})
export class TimeSheetService {

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

  //get all cutomer guards

  public getCustGuards(data): Observable<any> {
    var link =
      this.global.baseUrl + 'get-all-customer-guards';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getGuards(): Observable<any> {
    var link =
      this.global.baseUrl + 'get-active-guards';

    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }


  //get timesheet
  public getTimesheet(data): Observable<any> {
    var link =
      this.global.baseUrl + 'getTimesheet';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  //Store Comment
  public storeComment(data): Observable<any> {
    var link =
      this.global.baseUrl + 'store-guard-timesheet-comments';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
  public getTimesheetDetails(data): Observable<any> {
    var link =
      this.global.baseUrl + 'get-timesheet-details';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // job tracker to get the Job tracker Api
  public getJobTracker(data): Observable<any> {
    var link =
      this.global.baseUrl + 'get-jobTraker-details';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public toggleChange(data): Observable<any> {
    var link =
      this.global.baseUrl + 'job-status-manual-approved';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getResport(data): Observable<any> {
    var link =
      this.global.baseUrl + 'generate-timesheet';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getJobTrackerReport(data): Observable<any> {
    var link =
      this.global.baseUrl + 'generateJobTrackerReport';

    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }


}

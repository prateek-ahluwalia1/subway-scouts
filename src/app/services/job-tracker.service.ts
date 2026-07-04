import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { throwError, Observable, retry, catchError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class JobTrackerService {

  constructor(private http: HttpClient, private global: GlobalVariable) { }

  handleError(error: HttpErrorResponse) {
    if (error.error instanceof ErrorEvent) {
      // A client-side or network error occurred. Handle it accordingly.
      console.error('An error occurred:', error.error.message);
    } else {
      // The backend returned an unsuccessful response code.
      // The response body may contain clues as to what went wrong,
      console.error(
        `Backend returned code ${error.status}, ` + `body was: ${error.error}`
      );
    }
    // return an observable with a user-facing error message
    return throwError('Something bad happened; please try again later.');
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  public getJobs(value): Observable<any> {

    var link =
      this.global.baseUrl + 'job-traker-jobs';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
}

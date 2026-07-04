import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { catchError, Observable, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class ContractorService {

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

  public createContractor(value): Observable<any> {
    value.admin_id = this.global.admin.admin_id
    let link = this.global.baseUrl + 'contractor/store';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1));
  }

  public updatesContractor(value): Observable<any> {
    value.admin_id = this.global.admin.admin_id
    let link = this.global.baseUrl + 'contractor/update';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1));
  }

  public getContractor(params?): Observable<any> {
    var link =
      this.global.baseUrl + 'get-contractors';
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public delContractor(id): Observable<any> {
    let link = this.global.baseUrl + 'contractor/delete';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getSpecContractor(id): Observable<any> {
    let link = this.global.baseUrl + 'get-contractor-by-id';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public delContractorContacts(id): Observable<any> {
    let link = this.global.baseUrl + 'delete-contractor-more-contact';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public change_ContractorStatus(data): Observable<any> {
    var link =this.global.baseUrl + 'active-contractors-status';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
}

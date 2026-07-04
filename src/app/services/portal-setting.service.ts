import { HttpHeaders, HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Observable, catchError, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class PortalSettingService {

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
      console.error('An error occurred:', error.error.message);
    } else {
      console.error(
        `Backend returned code ${error.status}, ` + `body was: ${error.error}`
      );
    }
    return throwError(error);
  }

  public portalSetting(): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.global.baseUrl + 'get-colors';
    let data = {
      name: 'colors'
    }
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getAdminsByLogin(): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = this.global.baseUrl + 'get-admins-by-login_users';
    const login_user_id = JSON.parse(localStorage.getItem("admin"));
    let data = {
      login_user_id: login_user_id.admin_id
    }
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getAdminActivity(data): Observable<any> {
    var link = this.global.baseUrl + 'get-admin-activity';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

}

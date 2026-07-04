import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { throwError, Observable, retry, catchError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class BusinessServiceService {
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
    return throwError('Something bad happened; please try again later.');
  }

  public getAllBusinesses(): Observable<any> {
    let link = this.global.baseUrl + 'business-setting/all';
    return this.http.post<any>(link, this.httpOptions)
      .pipe(retry(1));
  }

  public savebusisetting(type, records_business_navbar, business_data_id): Observable<any> {

    let link = this.global.baseUrl + 'add-and-update-business-permission';
    let data = {
      type: type,
      records_business_navbar: records_business_navbar,
      business_data_id: business_data_id,
      admin_id: this.global.admin.admin_id
    }

    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getSettings(type, business_data_id): Observable<any> {
    let link = this.global.baseUrl + 'get-add-and-update-business-permission';
    let data = {
      type: type,
      business_data_id: business_data_id,
      admin_id: this.global.admin.admin_id
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));

  }


  public getBusinessSettings(title): Observable<any> {
    let link = this.global.baseUrl + 'get-business-settings';
    let data = {
      title: title,
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }
  store(data): Observable<any> {
    let link = this.global.baseUrl + 'subscription-plan/store';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  update(data): Observable<any> {
    let link = this.global.baseUrl + 'subscription-plan/update';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  edit(data): Observable<any> {
    let link = this.global.baseUrl + 'subscription-plan/edit';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  getAllPackeges(): Observable<any> {
    let link = this.global.baseUrl + 'get-all-plans';
    return this.http.get<any>(link, this.httpOptions)
      .pipe(retry(1));
  }

  public saveform(data): Observable<any> {
    let link = this.global.baseUrl + 'business-setting/store';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getSinglebusi(id): Observable<any> {
    let link = this.global.baseUrl + 'business-setting/edit';
    let data = {
      id: id
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public updateBusi(data): Observable<any> {
    let link = this.global.baseUrl + 'business-setting/update';

    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public delPackege(id): Observable<any> {
    const data = {
      id: id
    }
    let link = this.global.baseUrl + 'subscription-plan/delete';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public delBusiness(id): Observable<any> {
    let link = this.global.baseUrl + 'business-setting/delete';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }
}

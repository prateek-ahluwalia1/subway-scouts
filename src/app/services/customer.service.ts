import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Customers } from 'app/modules/admin/operations-modules/components/job-roster/job-roster.component';
import { GlobalVariable } from 'app/shared/global';
import { catchError, Observable, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class CustomerService {

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

  public getCustomer(params): Observable<any> {
    var link =
      this.global.baseUrl + 'get-customers';

    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1));

  }

  public createCutomer(value): Observable<any> {
    let link = this.global.baseUrl + 'customer/store';
    value.admin_id = this.global.admin.admin_id
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1));
  }

  public uploadImage(img, type): Observable<any> {
    let link = this.global.baseUrl + 'upload-file';
    let data = {
      upload: img,
      folder: type,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  uploadImgPdf(formData, headers): Observable<any> {
    var link =
      this.global.baseUrl + 'upload-file';
    return this.http
      .post<any>(link, formData, headers)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getSpecCustomer(id): Observable<any> {
    let link = this.global.baseUrl + 'get-customer-by-id';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public updateCustomer(value): Observable<any> {
    let link = this.global.baseUrl + 'customer/update';
    value.admin_id = this.global.admin.admin_id
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1));
  }
  public getCusPersonal(id): Observable<any> {
    let link = this.global.baseUrl + 'get-customer-by-id';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));

  }

  public delCustomer(id): Observable<any> {
    let link = this.global.baseUrl + 'customer/delete';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getCust(): Observable<any> {
    var link =
      this.global.baseUrl + 'get-customers-name';
    // return this.http.post(link, this.httpOptions)

    return this.http
      .post<any>(link, '', this.httpOptions)
      .pipe(retry(1));
  }

  public getCus(): Observable<Customers[]> {
    var link =
      this.global.baseUrl + 'get-customers-name';
    // return this.http.post(link, this.httpOptions)

    return this.http
      .post<any>(link, '', this.httpOptions)
      .pipe(retry(1));
  }

  public change_CustomerStatus(data): Observable<any> {
    var link = this.global.baseUrl + 'active-customer-status';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public delCustomerContacts(id): Observable<any> {
    let link = this.global.baseUrl + 'delete-customer-more-contact';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));

  }

  public profileTracker(data): Observable<any> {
    var link = this.global.baseUrl + 'get-customer-profile-traker';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
}

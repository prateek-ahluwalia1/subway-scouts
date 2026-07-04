import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpHeaders, HttpParams } from '@angular/common/http';
import { BackendService } from 'app/services/backend.service';
import { Observable, retry, throwError } from 'rxjs';
import { GlobalVariable } from 'app/shared/global';
@Injectable()
export class CustomerService {

  private apiUrl = 'https://graph.microsoft.com/v1.0/me/messages';

  constructor(private http: HttpClient, private backend: BackendService, private global: GlobalVariable) { }


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

  saveCustomer(customer): Observable<any> {
    console.log(customer);

    if (customer.id === 0) {
      return this.createCustomer(customer);
    }
    return this.updateCustomer(customer);
  }

  private createCustomer(customer): Observable<any> {
    customer.id = undefined;

    let link = this.global.baseUrl + 'contractor/store';
    return this.http
      .post<any>(link, customer, this.httpOptions)
      .pipe(retry(1));
  }

  private updateCustomer(customer): Observable<any> {
    customer.id = undefined;
    let link = this.global.baseUrl + 'contractor/store';
    return this.http
      .post<any>(link, customer, this.httpOptions)
      .pipe(retry(1));
  }
  getCustomer(id: number) {

  }

  getMessagesForEmail(email: string, accessToken: string): Observable<any> {
    const headers = new HttpHeaders()
      .set('Authorization', `Bearer ${accessToken}`);

    const params = new HttpParams()
      .set('$filter', `toRecipients/any(r: r/emailAddress/address eq '${email}' or from/emailAddress/address eq '${email}')`);

    return this.http.get<any>(this.apiUrl, { headers, params });
  }


}

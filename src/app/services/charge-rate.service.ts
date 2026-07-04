import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { catchError, Observable, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class ChargeRateService {

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  constructor(private http: HttpClient, private globals: GlobalVariable) { }
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

  public sendPayRates(value): Observable<any> {
    value.admin_id = this.globals.admin.admin_id

    var link =
      this.globals.baseUrl + 'payrate/store';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // public sendPayRates(value): Observable<any> {
  //   value.admin_id = this.globals.admin.admin_id

  //   var link =
  //     this.globals.baseUrl + 'create_payrate';
  //   return this.http
  //     .post<any>(link, value, this.httpOptions)
  //     .pipe(retry(1), catchError(this.handleError));
  // }

  public updatePayRates(value): Observable<any> {
    value.admin_id = this.globals.admin.admin_id

    var link =
      this.globals.baseUrl + 'payrate/update';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // public updatePayRates(value): Observable<any> {
  //   value.admin_id = this.globals.admin.admin_id

  //   var link =
  //     this.globals.baseUrl + 'update_payrates';
  //   return this.http
  //     .post<any>(link, value, this.httpOptions)
  //     .pipe(retry(1), catchError(this.handleError));
  // }



  // Get new  getAllPayRates    
  public getAllPayRates(): Observable<any> {

    var link =
      this.globals.baseUrl + 'get-all-payrates';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // public getAllPayRates(): Observable<any> {

  //   var link =
  //     this.globals.baseUrl + 'payrates';
  //   return this.http
  //     .get<any>(link, this.httpOptions)
  //     .pipe(retry(1), catchError(this.handleError));
  // }

  // Get all  archiv epayrates    
  public getallarchivepayrates(): Observable<any> {

    var link =
      this.globals.baseUrl + 'get-all-archive-payrates';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }



  //******************   charge rates section ******************//

  // public sendChargeRates(value): Observable<any> {
  //   value.admin_id = this.globals.admin.admin_id
  //   var link =
  //     this.globals.baseUrl + 'charge_rate/store';
  //   return this.http
  //     .post<any>(link, value, this.httpOptions)
  //     .pipe(retry(1), catchError(this.handleError));
  // }
  public sendChargeRates(value): Observable<any> {
    value.admin_id = this.globals.admin.admin_id
    var link =
      this.globals.baseUrl + 'create_charged_rate';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // public updateChargeRates(value): Observable<any> {
  //   value.admin_id = this.globals.admin.admin_id
  //   var link =
  //     this.globals.baseUrl + 'charge_rate/update';
  //   return this.http
  //     .post<any>(link, value, this.httpOptions)
  //     .pipe(retry(1), catchError(this.handleError));
  // }

  public updateChargeRates(value): Observable<any> {
    value.admin_id = this.globals.admin.admin_id
    var link = `${this.globals.baseUrl}update_charged_rates/${value.id}`
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }



  // Get new  getAll Charge Rates    
  // public getAllchargeRates(): Observable<any> {
  //   var link =
  //     this.globals.baseUrl + 'get-all-chargerates';
  //   return this.http.get<any>(link, this.httpOptions)
  //     .pipe(retry(1), catchError(this.handleError));
  // }
  
  public getAllchargeRates(): Observable<any> {
    var link =
      this.globals.baseUrl + 'get_charged_rates';
    return this.http.get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // Get all  archive Charge rates    
  public getallarchiveChargerates(): Observable<any> {
    var link = this.globals.baseUrl + 'get-all-archive-chargerates';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }




  // put  payrate  rates    
  public makeArchive(data): Observable<any> {
    data.admin_id = this.globals.admin.admin_id

    var link =
      this.globals.baseUrl + 'payrate/remove';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }


  // put  archive Charge rates    
  public makeArchiveCharge(data): Observable<any> {
    data.admin_id = this.globals.admin.admin_id

    var link =
      this.globals.baseUrl + 'charge_rate/remove';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }


  public getSpecificChargeRateWithLevel(data): Observable<any> {
    var link =this.globals.baseUrl + 'getSpecificChargeRateWithLevel';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public sendawardPayRates(value): Observable<any> {
    value.admin_id = this.globals.admin.admin_id

    var link =
      this.globals.baseUrl + 'create_award_payrate';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public updateAwardPayRates(value): Observable<any> {
    value.admin_id = this.globals.admin.admin_id

    var link =
      this.globals.baseUrl + 'update_award_payrates';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getAllAwardPayRates(): Observable<any> {

    var link =
      this.globals.baseUrl + 'award_payrates';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public addPayslip(value): Observable<any> {
    var link =
      this.globals.baseUrl + 'upload-payslips';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getAllPayslips(data): Observable<any> {

    var link =
      this.globals.baseUrl + 'get-guard-payslips';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }


  public inactiveChargeRates(): Observable<any> {

    var link = this.globals.baseUrl + 'auto-update-payslips';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

}

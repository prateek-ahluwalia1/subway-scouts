import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { catchError, Observable, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SiteService {

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

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  ////Get all sites
  public getAllSites(params?): Observable<any> {
    var link =
      this.globals.baseUrl + 'get-all-sites';
    return this.http
      .post<any>(link, params, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getCustomer(): Observable<any> {
    var link =
      this.globals.baseUrl + 'get-customers-name';
    // return this.http.post(link, this.httpOptions)
    return this.http
      .post<any>(link, '', this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  //add site///
  public addSite(value): Observable<any> {
    var link =
      this.globals.baseUrl + 'site/save-and-update';
    // return this.http.post(link, this.httpOptions)
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  //add site///
  public getSpecificSite(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'site/edit';
    // return this.http.post(link, this.httpOptions)
    let data = {
      id: id,
      admin_id: this.globals.admin.admin_id,
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public delSite(data): Observable<any> {
    var link =
      this.globals.baseUrl + 'site/delete';
    // return this.http.post(link, this.httpOptions)
    // let data = {
    //   id: id,
    //   reason: reason,
    //   is_confirm: 'yes',
    //   admin_id: this.globals.admin.admin_id
    // }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getdelSiteReason(): Observable<any> {
    var link = this.globals.baseUrl + 'get-delete-site-reasons';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  //get charge rate and pay rate
  public getChargeRate(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'get-chargerate-and-level';
    // return this.http.post(link, this.httpOptions)
    let data = {
      level: id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getPayRate(id): Observable<any> {
    var link =
      this.globals.baseUrl + 'get-payrate-and-level';
    // return this.http.post(link, this.httpOptions)
    let data = {
      level: id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public saveQR(img, type): Observable<any> {
    let link = this.globals.baseUrl + 'upload-image';
    let data = {
      image: img,
      folder: type
    }
    console.log(data, link);
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public generateSiteLocationReport(data): Observable<any> {
    let link = this.globals.baseUrl + 'generateSiteLocationReport';
    return this.http.post<any>(link, data, this.httpOptions).pipe(retry(1));
  }

  public payRateHistory(site_id): Observable<any> {
    let link = this.globals.baseUrl + 'payrate-history/'+site_id;
    return this.http.get<any>(link, this.httpOptions).pipe(retry(1));
  }

  public chargeRateHistory(site_id): Observable<any> {
    let link = this.globals.baseUrl + 'charge-rate-history/'+site_id;
    return this.http.get<any>(link, this.httpOptions).pipe(retry(1));
  }

}

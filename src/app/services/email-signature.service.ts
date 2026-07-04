import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { GlobalVariable } from 'app/shared/global';
import { catchError, Observable, retry } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class EmailSignatureService {

  constructor(private http: HttpClient, private global: GlobalVariable) { }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  public saveSignature(data): Observable<any> {
    let link = this.global.baseUrl + 'email-signature-store';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getSignature(id) {
    let link = this.global.baseUrl + 'get-all-email-signature';
    let data = {
      admin_id: id,
    }
    return this.http.post<any>(link, data, this.httpOptions).pipe(retry(1));
  }

  deleteSignature(id) {
    let link = this.global.baseUrl + 'delete-email-signature';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id

    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getoneSignature(id): Observable<any> {
    let link = this.global.baseUrl + 'edit-email-signature';
    let data = {
      id: id,
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public updateSignature(data): Observable<any> {
    let link = this.global.baseUrl + 'update-email-signature';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public saveEmail(name, body): Observable<any> {
    let link = this.global.baseUrl + 'store-email-template';
    let data = {
      title: name,
      body: body,
      admin_id: this.global.admin.admin_id
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getAllTemp(): Observable<any> {
    let link = this.global.baseUrl + 'get-email-templates';
    return this.http.get<any>(link, this.httpOptions)
      .pipe(retry(1));
  }

  public updateDesign(name, design, id): Observable<any> {
    let link = this.global.baseUrl + 'update-email-template';
    let data = {
      id: id,
      title: name,
      body: design,
      admin_id: this.global.admin.admin_id
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public delDesign(id): Observable<any> {
    let link = this.global.baseUrl + 'delete-email-template';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getSingle(id): Observable<any> {
    let link = this.global.baseUrl + 'get-single-email-template';
    let data = {
      id: id
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }
}

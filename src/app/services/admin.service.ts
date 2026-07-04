import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { catchError, Observable, retry } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AdminService {

  constructor(private http: HttpClient, private global: GlobalVariable) { }


  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  public uploadImage(img, type): Observable<any> {
    let link = this.global.baseUrl + 'upload-image';
    let data = {
      image: img,
      folder: type
    }
    console.log(data, link);
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public createAdmin(value): Observable<any> {
    value.admin_id = this.global.admin.admin_id
    let link = this.global.baseUrl + 'user/update';
    console.log(value, link);
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1));
  }

  public getAdminData(id): Observable<any> {
    let link = this.global.baseUrl + 'get-admin';
    let data = {
      id: id,
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public delAdmin(id, status): Observable<any> {
    let link = this.global.baseUrl + 'user/delete';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id,
      status: status
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getAdmin(): Observable<any> {
    var link = this.global.baseUrl + 'get-all-admins';
    return this.http.get(link, this.httpOptions)
  }

  public enableOrDisable2FA(data): Observable<any> {
    var link = this.global.baseUrl + 'enable-2fa';
    return this.http.post(link,data, this.httpOptions)

  }

 public updateAdmin(value): Observable<any> {
    value.admin_id = this.global.admin.admin_id;
    let link = this.global.baseUrl + 'user/updateuser'; // Updated API URL
    console.log(value, link);
    return this.http
      .post<any>(link, value, this.httpOptions)
  }

  public changepassword(data): Observable<any> {
    // data.admin_id = this.global.admin.admin_id;
    let link = this.global.baseUrl + 'change-password'; 
    console.log(data, link);
    return this.http
      .post<any>(link, data, this.httpOptions)
  }
  
}

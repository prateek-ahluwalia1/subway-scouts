import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Observable, retry } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EmailService {

  constructor(private http:HttpClient,private global: GlobalVariable) { }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };
  

  public sendEmail(value):Observable<any>{
    let link = this.global.baseUrl+'sendEmail';
    return this.http
    .post<any>(link,value,this.httpOptions)
    .pipe(retry(1));
  }

  public getSignature(id):Observable<any>{
    let link = this.global.baseUrl+'get-all-email-signature-title-id';
    let data ={
      admin_id:id
    }
    return this.http
    .post<any>(link,data,this.httpOptions)
    .pipe(retry(1));
  }

  public getEmail():Observable<any>{
    let link = this.global.baseUrl+'get-email-templates';
    return this.http
    .get<any>(link,this.httpOptions)
    .pipe(retry(1));
  }


  public uploadImage(img): Observable<any> {
    let link = this.global.baseUrl + 'upload-image';
    let data = {
      image: img,
    }
    console.log(data, link);
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }
}

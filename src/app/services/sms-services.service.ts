import {
  HttpClient,
  HttpErrorResponse,
  HttpHeaders,
} from "@angular/common/http";
import { Injectable } from "@angular/core";
import { GlobalVariable } from "app/shared/global";
import { catchError, Observable, retry, throwError } from "rxjs";

@Injectable({
  providedIn: "root",
})
export class SmsServicesService {

  constructor(private http: HttpClient, private globals: GlobalVariable) { }

  handleError(error: HttpErrorResponse) {
    if (error.error instanceof ErrorEvent) {
      console.error("An error occurred:", error.error.message);
    } else {
      console.error(
        `Backend returned code ${error.status}, ` + `body was: ${error.error}`
      );
    }
    return throwError("Something bad happened; please try again later.");
  }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  public getAllGuards(data?): Observable<any> {
    var link = this.globals.baseUrl + "get-all-guards";
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public smsSend(data?): Observable<any> {
    var link = this.globals.baseUrl + "sendSMS";
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // get users numbers and name for chat
  public userChatGet(data?): Observable<any> {
    var link = this.globals.baseUrl + "getChatUser";
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // get single user chat by sending its id

  public getUserChat(data): Observable<any> {
    var link = this.globals.baseUrl + "getChat";
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // to get new message from another end

  public getNewMessage(data): Observable<any> {
    var link = this.globals.baseUrl + "isNewMessage";
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // add new sms templete
  public addTemplete(title, msg): Observable<any> {
    let data = {
      title: title,
      msg_body: msg,
      admin_id: this.globals.admin.admin_id
    };
    var link = this.globals.baseUrl + "addSMSTemplate";
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // getAllTempletes sms

  public getAllTempletes(): Observable<any> {

    var link = this.globals.baseUrl + "getSMSTemplates";
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // deleteTemplete

  public deleteTemplete(id): Observable<any> {
    let data = { id: id, admin_id: this.globals.admin.admin_id };

    var link = this.globals.baseUrl + "deleteSMSTemplate";
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // update templete

  public updateTemplete(title, msg, id): Observable<any> {
    let data = { id: id, msg_body: msg, title: title, admin_id: this.globals.admin.admin_id };

    var link = this.globals.baseUrl + "updateSMSTemplate";
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // Get all sms  history 

  public allSmsHistory(data?): Observable<any> {
    var link = this.globals.baseUrl + "getMessageHistory";
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  // getAllFilteredGuards

  public getAllFilteredGuards(data?): Observable<any> {
    var link = this.globals.baseUrl + "filter-guards";
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }
}

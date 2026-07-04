import { HttpHeaders, HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Observable, catchError, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class AnnouncementInductionService {
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

  public getAllAnnouncement(type): Observable<any> {
    if (type == 'induction') {
      var link = this.globals.baseUrl + 'get-all-induction';
    }
    else {
      var link = this.globals.baseUrl + 'get-all-announcement';
    }
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getHistory(data):Observable<any>{
    var link = this.globals.baseUrl + 'announcement-history';
    return this.http
     .post<any>(link, data, this.httpOptions)

  }

  public addAnnouncement(value): Observable<any> {
    // add-induction
    console.log(value);

    if (value.type == 'induction') {
      var link =
        this.globals.baseUrl + 'add-induction';
    }
    else {
      var link =
        this.globals.baseUrl + 'add-announcement';
    }
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public dellAnnouncement(value, type): Observable<any> {

    if (type == 'induction') {
      var link =
        this.globals.baseUrl + 'delete-induction';
    }
    else {
      var link =
        this.globals.baseUrl + 'delete-announcement';
    }

    let data = {
      id: value,
      admin_id: this.globals.admin.admin_id
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getGuardsFilter(value): Observable<any> {

    var link =
      this.globals.baseUrl + 'getSpecificGuards';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public shareTemplates(value): Observable<any> {

    var link =
      this.globals.baseUrl + 'share';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public addInduction(data): Observable<any> {
      var link = this.globals.baseUrl + 'questionnaire-save';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getAllInduction(): Observable<any> {
    var link = this.globals.baseUrl + 'questionnaire-list';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  delInduction(id):  Observable<any> {
    var link = `${this.globals.baseUrl}questionnaire-delete/${id}`;
    return this.http.get<any>(link, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError));
  }

  public shareQuestionnaire(value): Observable<any> {

    var link =
      this.globals.baseUrl + 'assign-questionnaire';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public downloadInductionExcel(data): Observable<any> {
    var link = this.globals.baseUrl + 'generate-induction-report';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

}

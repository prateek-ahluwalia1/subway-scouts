import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { catchError, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class PatrollingService {

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
      // A client-side or network error occurred. Handle it accordingly.
      console.error('An error occurred:', error.error.message);
    } else {
      // The response body may contain clues as to what went wrong,
      console.error(
        `Backend returned code ${error.status}, ` + `body was: ${error.error}`
      );
    }
    // return an observable with a user-facing error message
    return throwError('Something bad happened; please try again later.');
  }

  getPatrollingLocations(value){
    var link = this.global.baseUrl + 'get-patrolling-locations';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  createRunsheet(value){
    var link = this.global.baseUrl + 'create-update-run-sheet';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getAllRunsheet(){
    var link = this.global.baseUrl + 'get-all-run-sheets';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  deleteRunsheet(data){
    var link = this.global.baseUrl + 'delete-run-sheet';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getSpecific(id) {
    var link = `${this.global.baseUrl}edit-run-sheets/${id}`;
    return this.http.get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getdelRunsheetReason(){
    var link = this.global.baseUrl + 'get-delete-runsheet-reasons';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  deleteSpecific(data) {
    var link = this.global.baseUrl + 'runsheet-roster/delete-run-sheet-detail';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  //Patrol Car Details

  StoreCarDetails(value){
    var link = this.global.baseUrl + 'store-car';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getAllCarDetails(){
    var link = this.global.baseUrl + 'get-all-cars';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  editCar(id){
    var link = this.global.baseUrl + 'edit-car';
    return this.http
      .post<any>(link, id, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  UpdateCarDetails(value){
    var link = this.global.baseUrl + 'update-car';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  delCarDetails(id){
    let data = {
      id: id
    }
    var link = this.global.baseUrl + 'delete-car';
    return this.http
    .post<any>(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError));
  }

  //Alarm Dispatch File
  StoreAlarmDetails(value){
    var link = this.global.baseUrl + 'store-alarm-dispatch';
    return this.http
      .post<any>(link, value, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getAllAlarmDetails(){
    var link = this.global.baseUrl + 'get-all-alarm-dispatch';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  editAlarm(id){
    var link = this.global.baseUrl + 'edit-alarm-dispatch';
    return this.http
      .post<any>(link, id, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  delAlarmDetails(id){
    let data = {
      id: id
    }
    var link = this.global.baseUrl + 'delete-alarm-dispatch';
    return this.http
    .post<any>(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError));
  }
}

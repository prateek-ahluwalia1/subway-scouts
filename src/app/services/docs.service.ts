import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Observable, catchError, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class DocsService {

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

  ////For Company Folders

  createFolder(data): Observable<any> {
    let link = this.global.baseUrl + 'create-business-folder';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  getAllFolders(){
    var link = this.global.baseUrl + 'get-business-folder';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  ////For Company Files

  createFile(data): Observable<any>{
    let link = this.global.baseUrl + 'create-business-file';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  getAllFiles(data){
    var link = this.global.baseUrl + 'get-all-business-files';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  getSpecFile(id){
    var link = this.global.baseUrl + 'get-specific-business-files';
    return this.http
      .post<any>(link, id, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  delFiles(id){
    var link = this.global.baseUrl + 'delete-business-files';
    return this.http
      .post<any>(link, id, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  editFolder(id){
    var link = this.global.baseUrl + 'rename-folder-name';
    return this.http
      .post<any>(link, id, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  deleteFolder(id){
    var link = this.global.baseUrl + 'delete-folder';
    return this.http
      .post<any>(link, id, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

}

import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { catchError, retry } from 'rxjs';
import { Observable } from 'rxjs/internal/Observable';

@Injectable({
  providedIn: 'root'
})

export class RolePermissionService {

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  constructor(private http: HttpClient, private global: GlobalVariable) { }

  public saveRolePermission(data): Observable<any> {
    var link = this.global.baseUrl + 'save-and-update-role-permissions';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getRolesPermissions(data): Observable<any> {
    var link = this.global.baseUrl + 'edit-role-permissions';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getAllRoleAndPermission(): Observable<any> {
    var link = this.global.baseUrl + 'get-all-role-permissions';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1));
  }

  public delRoleAndPermission(id): Observable<any> {
    let data = {
      id: id
    }
    var link = this.global.baseUrl + 'delete-role-permissions';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

}

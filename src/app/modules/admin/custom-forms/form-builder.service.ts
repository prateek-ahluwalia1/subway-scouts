import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { BehaviorSubject, catchError, Observable, retry } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class FormBuildService {

  constructor(private http: HttpClient, private global: GlobalVariable) { }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  public saveForm(data): Observable<any> {
    let link = this.global.baseUrl + 'store-form-template';

    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public updateForm(data): Observable<any> {
    let link = this.global.baseUrl + 'update-form-templates';

    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getAllForms(type): Observable<any> {
    let link = this.global.baseUrl + 'get-all-form-templates';
    return this.http.post<any>(link,type, this.httpOptions)
      .pipe(retry(1));

  }

  public delDesign(id): Observable<any> {
    let link = this.global.baseUrl + 'delete-form-templates';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public getSingletemp(id): Observable<any> {
    let link = this.global.baseUrl + 'edit-form-templates';
    let data = {
      id: id
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  getSites(id, state) {
    var link =
      this.global.baseUrl + 'form-template-guard-filter';
    let data = {
      sites: id,
      state: state
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  getguards(data) {
    if (data.type == 'custom') {
      var link = this.global.baseUrl + 'send-page-link';
    }
    else {
      var link = this.global.baseUrl + 'send-bulitin-form';
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public saveJsonForm(id, form_data): Observable<any> {
    let link = this.global.baseUrl + 'insert-form-data';
    let data = {
      id: id,
      form_data: form_data
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public StoreChecklistform(data): Observable<any> {
    let link = this.global.baseUrl + 'add-employment-pack-checklist';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public GetChecklistform(data): Observable<any> {
    let link = this.global.baseUrl + 'get-employment-pack-checklist';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public StoreReferenceform(data): Observable<any> {
    let link = this.global.baseUrl + 'update-refrence';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public GetReferenceform(data): Observable<any> {
    let link = this.global.baseUrl + 'get-refrence';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public StoreEmergencyContact(data): Observable<any> {
    let link = this.global.baseUrl + 'update-guard-emergency-contact-details';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public GetEmergencyContact(data): Observable<any> {
    let link = this.global.baseUrl + 'get-guard-emergency-contact-details';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public StorePersonalRef(data): Observable<any> {
    let link = this.global.baseUrl + 'update-personal-refrences';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public GetPersonalRef(data): Observable<any> {
    let link = this.global.baseUrl + 'get-personal-refrences';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public StoreUniformDetail(data): Observable<any> {
    let link = this.global.baseUrl + 'update-uniform-details';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public GetUniformDetail(data): Observable<any> {
    let link = this.global.baseUrl + 'get-uniform-details';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public saveSign(img, type): Observable<any> {
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

  public GetEmpForm(data): Observable<any> {
    console.log(data, 'data');
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    console.log(httpOptions);

    let link = this.global.baseUrl + 'get-staff-contractor-details';
    return this.http.post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  public StoreEmpForm(data, businessId): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    let link = this.global.baseUrl + 'save-and-update-staff-contractor-details';
    return this.http.post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  public GetEmpClicks(data, businessId): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };

    let link = this.global.baseUrl + 'bulitin-form-link-clicked';
    return this.http.post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  public GetGuardInfo(data): Observable<any> {
    let link = this.global.baseUrl + 'history-bulitin-form';
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }


  public getFormById(id): Observable<any> {
    let link = this.global.baseUrl + 'edit-form-templates';
    let data = {
      id: id,
      admin_id: this.global.admin.admin_id
    }
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }

  public formSubmissionDetail(id): Observable<any> {
    let link = this.global.baseUrl + 'history-dynamic-form/' + id;
    return this.http.get<any>(link, this.httpOptions)
      .pipe(retry(1));
  }
}

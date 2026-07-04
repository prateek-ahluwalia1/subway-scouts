import { GlobalVariable } from 'app/shared/global';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { HttpClient, HttpHeaders, } from '@angular/common/http';
import { retry, tap } from 'rxjs/operators';
@Injectable({
  providedIn: 'root'
})

export class ServiceService {

  private _securityLicense: BehaviorSubject<any> = new BehaviorSubject(null);
  private _liveOperations: BehaviorSubject<any> = new BehaviorSubject(null);
  private _publishUnPublish: BehaviorSubject<any> = new BehaviorSubject(null);
  private _staffConfirm: BehaviorSubject<any> = new BehaviorSubject(null);
  private _leaves: BehaviorSubject<any> = new BehaviorSubject(null);
  private _graphData: BehaviorSubject<any> = new BehaviorSubject(null);
  private _toDos: BehaviorSubject<any> = new BehaviorSubject(null);
  private _leadsCount: BehaviorSubject<any> = new BehaviorSubject(null);

  weatherBaseUrl = 'http://dataservice.accuweather.com/';
  // weatherBaseUrl='http://dataservice.accuweather.com/locations/v1/cities/geoposition/search';

  apikey: string = 'qCsO1Au6siJIFOdulP9vFYqjmiwtGBU3';

  constructor(private http: HttpClient, private globals: GlobalVariable) { }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  // public getCity(lat, lng) {
  //   var data = JSON.stringify({
  //     latitude: lat,
  //     longtitude: lng,

  //   });
  //   var link = this.weatherBaseUrl + 'locations/v1/cities/geoposition/search?apikey=' + this.apikey;
  //   return this.http.get<any>(link, httpOptions)
  //     .pipe(retry(1), catchError(this.handleError));
  // }

  // public searchCity(city): Observable<any> {
  //   var link =
  //     this.weatherBaseUrl +
  //     'locations/v1/cities/search?apikey=' + this.apikey + '&q=' +
  //     'Lahore&details=true';

  //   console.log('link is ', link);

  //   return this.http
  //     .get<any>(link)
  //     .pipe(retry(1), catchError(this.handleError));
  // }


  get securityLicense$(): Observable<any> {
    return this._securityLicense.asObservable();
  }

  get liveOperations$(): Observable<any> {
    return this._liveOperations.asObservable();
  }

  get publishUnPublish$(): Observable<any> {
    return this._publishUnPublish.asObservable();
  }

  get leaves$(): Observable<any> {
    return this._leaves.asObservable();
  }

  get graphData$(): Observable<any> {
    return this._graphData.asObservable();
  }

  get staffConfirm$(): Observable<any> {
    return this._staffConfirm.asObservable();
  }

  get todos$(): Observable<any> {
    return this._toDos.asObservable();
  }

  get leadsCount$(): Observable<any> {
    return this._leadsCount.asObservable();
  }

  public getAdmin_role(): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    let link = this.globals.baseUrl + 'get-admin-role'
    return this.http
      .get<any>(link, httpOptions)
      .pipe(retry(1));
  }

  // signUp(user: { name: string; email: string; password: string;phone:string;state:string; user_role: string }): Observable<any>

  admin_signUp(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = this.globals.baseUrl + 'user/store';
    return this.http.post(link, data, httpOptions);
  }

  public signOut(): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'logout';
    return this.http.get<any>(link, httpOptions).pipe(retry(1));
  }

  public getAdmin(status, datas?): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = this.globals.baseUrl + 'get-sub-admins';
    let data = {
      status: status,
      type: datas?.type
    }
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  public change_AdminStatus(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };

    var link = this.globals.baseUrl + 'update-subadmin-status';
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  public getMutilCusMutilSite(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };

    var link = this.globals.baseUrl + 'job-new-roster/get-sites-by-customes';

    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  public getAllAdmins(): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link =
      this.globals.baseUrl + 'get-all-admins';
    return this.http
      .get<any>(link, httpOptions)
      .pipe(retry(1));
  }

  saveNewRoster(value): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link =
      this.globals.baseUrl + 'job-new-roster/store';
    return this.http
      .post<any>(link, value, httpOptions)
      .pipe(retry(1));
  }

  updateNewRoster(value): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link =
      this.globals.baseUrl + 'update-job-new-roster';
    return this.http
      .post<any>(link, value, httpOptions)
      .pipe(retry(1));
  }

  getAppUsage(): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'app-usages';

    return this.http
      .get<any>(link, httpOptions)
      .pipe(retry(1));
  }

  leadsCount(data?): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrlCrm + 'lead-status-count';

    return this.http.post<any>(link, data, httpOptions)
      .pipe(retry(1),
        tap((response) => {
          this._leadsCount.next(response)
        }));
  }

  addNotes(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'store-dashboard-notes';
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  public getAllNotes(): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'get-dashboard-notes';
    let data = {
      admin_id: this.globals.admin.admin_id
    }
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  public delNotes(id): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'delete-dashboard-notes';
    let data = {
      id: id,
    }
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  public getOnenote(id): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'edit-dashboard-note';
    let data = {
      id: id,
      admin_id: this.globals.admin.admin_id
    }
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  liveDashboardData(data?): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'liveDashabordData';
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1),
        tap((response) => {
          this._liveOperations.next(response)
        }));
  }

  getNearToExpireLicenseGuard(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'getNearToExpireLicenseGuard';
    return this.http
      .post<any>(link, data, httpOptions).pipe(retry(1),
        tap((response) => {
          this._securityLicense.next(response);
        })
      );
  }

  getNearExpireVisaGuard(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'getNearExpireVisaGuard';

    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  liveWelfareCallData(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'liveWelfareCallData';

    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  getCrmDashboardData(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrlCrm + 'getGraphData';

    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  getMonthlyDataStages(): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrlCrm + 'getMonthlyData';

    return this.http
      .get<any>(link, httpOptions)
      .pipe(retry(1));
  }

  dealsThisMonth(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrlCrm + 'dashboard-customers-count';
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  getAllLeaves(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'guard-leave-count';
    return this.http.post<any>(link, data, httpOptions)
      .pipe(retry(1),
        tap((response) => {
          this._leaves.next(response)
        }));
  }

  addToDo(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'todos';
    return this.http.post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  delToDo(id): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = `${this.globals.baseUrl}todos/${id}`
    return this.http.delete<any>(link, httpOptions)
      .pipe(retry(1));
  }

  updateToDo(id: number, data: any): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    const url = `${this.globals.baseUrl}todos/update/${id}`;
    return this.http.post(url, data, httpOptions)
      .pipe(retry(1));
  }

  getAllToDos(id): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = `${this.globals.baseUrl}todos/${id}`;
    return this.http.get<any>(link, httpOptions)
      .pipe(retry(1),
        tap((response) => {
          this._toDos.next(response)
      }));;
  }

  changeTodoStatus(id): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = `${this.globals.baseUrl}todos/change-status/${id}`;
    return this.http.get<any>(link, httpOptions)
      .pipe(retry(1));
  }

  getBigLoss(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrlCrm + 'dashboard-customers-loss-revenue';
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  getPercent(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrlCrm + 'dashboard-lead-percentage';
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  markAsRead(item): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'operation-notes-mark-as-read';
    return this.http
      .post<any>(link, item, httpOptions)
      .pipe(retry(1));
  }

  getNearExpirePassportGuard(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'getNearExpirePassportGuard';

    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  publishUnpublish(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'publish-and-unpublish-shift-count-one-week';

    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1),
        tap((response) => {
          this._publishUnPublish.next(response)
        }));
  }

  graphData(): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        'Content-Type': 'application/json'
      })
    };
    var link = this.globals.baseUrl + 'dashboard-graph-data';

    return this.http
      .get<any>(link, httpOptions)
      .pipe(retry(1),
        tap((response) => {
          this._graphData.next(response)
        }));
  }

  ///Dashboard Staff Confirmation

  getConfirm(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = this.globals.baseUrl + 'get-staff-confirmation';
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1),
        tap((response) => {
          this._staffConfirm.next(response)
        }));
  }

  greenCall(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = this.globals.baseUrl + 'green-call-toggle';
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  saveRemarks(data) {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = this.globals.baseUrl + 'call-notes-update';
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  getRemarks(data) {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = this.globals.baseUrl + 'get-call-notes';
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

  crmNotification(id): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = this.globals.baseUrlCrm + `unseen-notification/${id}`;
    return this.http
      .get<any>(link, httpOptions)
      .pipe(retry(1));
  }

  getLeadsComparision(data): Observable<any> {
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      }),
    };
    var link = this.globals.baseUrlCrm + `getPieGraphData`;
    return this.http
      .post<any>(link, data, httpOptions)
      .pipe(retry(1));
  }

}

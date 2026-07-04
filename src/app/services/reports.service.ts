import { HttpHeaders, HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Observable, catchError, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class ReportsService {

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

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  public getReports(data, sheet): Observable<any> {
    console.log(sheet);

    if (sheet == 'invoice') {
      var link = this.globals.baseUrl + 'generateInvoiceReport';
    } else {
      var link = this.globals.baseUrl + 'generatePaysheetReport';
    }
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getoldReports(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateOldPaysheetReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getTaskReports(data): Observable<any> {
    var link = this.globals.baseUrl + 'taskReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public generateGreenCallReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateGreenCallReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getGuardReports(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateGuardReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getGuardReportsData(data): Observable<any> {
    var link = this.globals.baseUrl + 'getGuardReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public dowloadTaskReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateTaskReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public generateQuickPaysheetReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateQuickPaysheetReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getSignInOutReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateSigninoutReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getIncidentReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'getIncidentReportData';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public dowloadIncidentReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateIncidentReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public dowloadFootPatrolReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateFootPatrolReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public editInjurydetail(data): Observable<any> {
    var link = this.globals.baseUrl + 'update-incident-report';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getProfitLossReports(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateProfitLossInvoice';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getAuditReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'search-audits';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public downloadAudit(id): Observable<any> {
    var link = this.globals.baseUrl + 'generate-audit-report/' + id;
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public delAudit(id): Observable<any> {
    var link = this.globals.baseUrl + 'delete-audit/' + id;
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }


  public generateMultiCallReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateMultiCallReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getCrmReport(): Observable<any> {
    var link = this.globals.baseUrlCrm + 'generateCrmLeadData';
    return this.http.get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getCrmCustomerReport(): Observable<any> {
    var link = this.globals.baseUrl + 'generateCustomersExcel';
    return this.http.get<any>(link, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getCrmTotalLeads(data):Observable<any>{
    var link = this.globals.baseUrlCrm +'total-lead';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  public downloadMidnightHours(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateGuardMidnightReport';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  public downloadfourtyHoursShift(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateFourtyHoursShiftReport';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  public downloadtenHoursShift(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateTenHoursShiftReport';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  public downloadthirtysixHoursShift(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateThirtySixHoursShiftReport';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  public downloadtwelveHoursShift(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateTwelveHoursShiftReport';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  public downloadAdhocHoursShift(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateAdhocHoursShiftReport';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  public downloadMonthlyReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'generate-pdf-monthly-report';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  addStaffInjuryDetails(data): Observable<any> {
    var link = this.globals.baseUrl + 'staff-injury';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  addStaffLeaveDetails(data): Observable<any> {
    var link = this.globals.baseUrl + 'guard-leave';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  addStaffContactDetails(data): Observable<any> {
    var link = this.globals.baseUrl + 'point-of-contact';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  addHazardsDetails(data): Observable<any> {
    var link = this.globals.baseUrl + 'near-misses';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }

  public PayrollPaysheet(data): Observable<any> {
    var link = this.globals.baseUrl + 'generatePayrollPaysheetReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public trainingMatrix(data): Observable<any> {
    var link = this.globals.baseUrl + 'report/get_guard_document_report';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public getAuthReports(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateGuestAuthReport';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError));
  }

  public downloadComparisonReport(data): Observable<any> {
    var link = this.globals.baseUrl + 'generateCompleteComparisonReport';
    return this.http.post(link, data, this.httpOptions)
    .pipe(retry(1), catchError(this.handleError))
  }


}

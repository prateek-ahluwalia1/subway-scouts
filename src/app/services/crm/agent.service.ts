import { HttpClient, HttpErrorResponse, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Observable, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AgentService {

  private apiUrl = 'https://graph.microsoft.com/v1.0/me/messages';

  constructor(private http: HttpClient, private global: GlobalVariable) { }

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

  getList(dataType: 'customer' | 'sub-admins', month?,): Observable<any> {
    let data = {
      userType: this.global.admin.admin_user_type,
      id: this.global.admin.admin_id,
      month: month
    }
    const link = dataType === 'customer' ? this.global.baseUrlCrm + 'getCustomersList' : this.global.baseUrl + 'get-sub-admins';
    const requestData = dataType === 'customer' ? null : { status: 'active' };

    return this.http.post<any>(link, data, {
      ...this.httpOptions,
      params: requestData
    });
  }

  getDashboardList(dataType: 'customer' | 'sub-admins', month?,): Observable<any> {
    let data = {
      userType: this.global.admin.admin_user_type,
      id: this.global.admin.admin_id,
      month: month
    }
    const link = dataType === 'customer' ? this.global.baseUrlCrm + 'dashboardCustomersList' : this.global.baseUrl + 'get-sub-admins';
    const requestData = dataType === 'customer' ? null : { status: 'active' };

    return this.http.post<any>(link, data, {
      ...this.httpOptions,
      params: requestData
    });
  }

  saveCustomer(customer, type): Observable<any> {
    if (customer.id) {
      return this.updateUser(customer, type);
    }
    return this.createUser(customer, type);
  }

  private createUser(customer: any, userType: string): Observable<any> {
    const link = userType === 'create/sale-person' ? this.global.baseUrlCrm + 'addSalesPerson' : this.global.baseUrlCrm + 'addCustomer';
    customer.id = undefined;
    return this.http.post<any>(link, customer, this.httpOptions).pipe(
      retry(1)
    );
  }

  private updateUser(customer: any, userType: string): Observable<any> {
    const link = userType === 'edit/sale-person' ? this.global.baseUrlCrm + 'updateSalesPerson' : this.global.baseUrlCrm + 'updateCustomer';

    return this.http.post<any>(link, customer, this.httpOptions).pipe(
      retry(1)
    );
  }

  public deleteUser(id: number, type: string): Observable<any> {
    let url: string;
    let data: any;

    data = {
      id: id,
      admin_id: this.global.admin.admin_id,
      type: type  // Pass the 'type' parameter in the request body
    };

    if (type == 'customer') {
      url = `${this.global.baseUrlCrm}deleteCustomer`;
      return this.http.post<any>(url, data, this.httpOptions).pipe(
        retry(1)
      );
    } else {
      url = this.global.baseUrl + 'user/delete';

      return this.http.post<any>(url, data, this.httpOptions).pipe(
        retry(1)
      );
    }
  }

  public getUser(id: number, type?): Observable<any> {
    let url
    url = `${this.global.baseUrlCrm}getCustomersDetails/${id}`;
    return this.http.get<any>(url, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  public getTimeLine(data): Observable<any> {

    let url = `${this.global.baseUrlCrm}get-crm-customer-timeline`;
    return this.http.post<any>(url, data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  public saveNotes(data): Observable<any> {
    data.admin_id = this.global.admin.admin_id
    data.user_id = this.global.admin.admin_id
    let url = `${this.global.baseUrlCrm}add-customer-comment`;
    return this.http.post<any>(url, data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  public getNotes(id: number): Observable<any> {
    let url = `${this.global.baseUrlCrm}get-customers-comments/${id}`;
    return this.http.get<any>(url, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  uploadImgPdf(formData, headers): Observable<any> {
    var link = this.global.baseUrl + 'upload-file-with-size';
    return this.http.post<any>(link, formData, headers)
      .pipe(
        retry(1)
      );
  }

  getAttachments(id): Observable<any> {
    let url = `${this.global.baseUrl}get-customers-files/${id}`;
    return this.http.get<any>(url, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  getQuote(id): Observable<any> {
    let url = `${this.global.baseUrlCrm}get-quotations/${id}`;
    return this.http.get<any>(url, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  getContactList(type, company_name?, agent_id?, start?, end?): Observable<any> {
    let data = {
      userType: this.global.admin.admin_user_type,
      id: this.global.admin.admin_id,
      company_name: company_name,
      agent_id: agent_id,
      start: start,
      end: end
    };

    let link: string;
    switch (type) {
      case 'lead':
        link = this.global.baseUrlCrm + 'get-specific-leads';
        break;
      case 'contacted':
        link = this.global.baseUrlCrm + 'get-contact-leads';
        break;
      case 'lost':
        link = this.global.baseUrlCrm + 'get-lost-leads';
        break;
      case 'won':
        link = this.global.baseUrlCrm + 'get-won-leads';
        break;
      default:
        throw new Error('Invalid contact type.');
    }

    return this.http.post<any>(link, data, this.httpOptions).pipe(retry(1));
  }

  getQuotes(data): Observable<any> {
    var link = this.global.baseUrlCrm + 'get-all-quotations';
    // return this.http
    //   .get<any>(link, this.httpOptions)
    //   .pipe(retry(1));
    return this.http.post<any>(link, data, this.httpOptions).pipe(
      retry(1)
    );
  }

  public saveQuotation(customer: any): Observable<any> {
    const link = this.global.baseUrlCrm + 'add-update-quotation';
    return this.http.post<any>(link, customer, this.httpOptions).pipe(
      retry(1)
    );
  }

  public getQuotation(): Observable<any> {
    const link = this.global.baseUrlCrm + 'get-all-quotations';
    return this.http.get<any>(link, this.httpOptions).pipe(
      retry(1)
    );
  }

  public editQuotation(id): Observable<any> {
    let data = {
      id: id
    }
    const link = this.global.baseUrlCrm + 'edit-quotation';
    return this.http.post<any>(link, data, this.httpOptions).pipe(
      retry(1)
    );
  }

  public delQuotation(id): Observable<any> {
    let data = {
      id: id
    }
    const link = this.global.baseUrlCrm + 'delete-quotation';
    return this.http.post<any>(link, data, this.httpOptions).pipe(
      retry(1)
    );
  }


  public sendEmail(data): Observable<any> {
    const link = this.global.baseUrlCrm + 'send-email';
    return this.http.post<any>(link, data, this.httpOptions).pipe(
      retry(1)
    );
  }

  emailHistory(id): Observable<any> {
    let url = `${this.global.baseUrlCrm}send-email-history/${id}`;
    return this.http.get<any>(url, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  addUpdateCrmTask(data): Observable<any> {
    let url = `${this.global.baseUrlCrm}add-update-crm-task`;
    return this.http.post<any>(url, data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  getAllCrmTask(data): Observable<any> {
    let url = `${this.global.baseUrlCrm}get-all-crm-task`;
    return this.http.post<any>(url,data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  deleteCrmTask(id): Observable<any> {
    let data = {
      id: id
    }
    let url = `${this.global.baseUrlCrm}delete-crm-task`;
    return this.http.post<any>(url, data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  editCrmTask(id): Observable<any> {
    let data = {
      id: id
    }
    let url = `${this.global.baseUrlCrm}edit-crm-task`;
    return this.http.post<any>(url, data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  quoteToInvoice(data): Observable<any> {
    let url = `${this.global.baseUrlCrm}convert-quote-into-invoice`;
    return this.http.post<any>(url, data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  archiveCustomer(data): Observable<any> {
    let url = `${this.global.baseUrlCrm}archiveCustomer`;
    return this.http.post<any>(url, data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  delFile(data): Observable<any> {
    let url = `${this.global.baseUrlCrm}delete-crm-customer-file`;
    return this.http.post<any>(url, data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  getLeadsReport(data): Observable<any> {
    let url = `${this.global.baseUrl}generateGlobalSalesReport`;
    return this.http.post<any>(url, data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  addReminder(data): Observable<any> {
    let url = `${this.global.baseUrlCrm}add-reminder`;
    return this.http.post<any>(url, data, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  getReminders(id): Observable<any> {
    let url = `${this.global.baseUrlCrm}get-reminder/${id}`;
    return this.http.get<any>(url, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  delReminder(id): Observable<any> {
    let url = `${this.global.baseUrlCrm}reminder-delete/${id}`;
    return this.http.get<any>(url, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  markAsComplete(id): Observable<any> {
    let url = `${this.global.baseUrlCrm}changes-reminder-status/${id}`;
    return this.http.get<any>(url, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  getCompanyList(): Observable<any> {
    let url = `${this.global.baseUrlCrm}get-company-list`;
    return this.http.get<any>(url, this.httpOptions)
      .pipe(
        retry(1)
      );
  }

  getRecentMessages(): Observable<any> {
    const token = localStorage.getItem('outlookToken');
  
    if (!token) {
      console.error('Access token not found.');
      return;
    }
  
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
  
    // Only fetch a set number of recent emails
    const params = new HttpParams()
      .set('$select', 'subject,from,toRecipients,body')
      .set('$top', '500'); // You might want to adjust this number based on expected email volume
  
    return this.http.get<any>(`${this.apiUrl}`, { headers, params });
  }
  
  

}

import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, catchError, of, tap, throwError } from 'rxjs';
import { formatDate } from '@angular/common';
import { AircallClient } from 'aircall-everywhere';
import { GlobalVariable } from 'app/shared/global';

@Injectable({
  providedIn: 'root'
})

export class AircalService {
  private baseUrl = 'https://api.aircall.io/v1';
  private appId = '';
  private apiToken = '';

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  constructor(private http: HttpClient, private global: GlobalVariable) {
    this.appId = this.global.admin.apiKeys.air_call_app_id
    this.apiToken = this.global.admin.apiKeys.air_call_token
  }

  getHeaders() {
    const headers = new HttpHeaders({
      'Authorization': 'Bearer ' + this.apiToken
    });
    return headers;
  }

  // get all calls  list

  getCalls() {
    const headers = this.getHeaders();
    return this.http.get(this.baseUrl + 'calls?app_id=' + this.appId, { headers });
  }

  // how to make a call to phone number 

  makeCall(phoneNumber: string) {
    const headers = this.getHeaders();
    const body = {
      action: 'call',
      user_id: '',
      direction: 'outbound',
      phone_number: phoneNumber,
      source: 'web'
    };
    return this.http.post(this.baseUrl + 'activities?app_id=' + this.appId, body, { headers });
  }

  async createContactAndOpenDialerPad() {
    try {
      const phoneNumber = prompt('Enter phone number:');
      if (phoneNumber) {
        // Create new contact with phone number
        const response = await fetch('https://api.aircall.io/v1/contacts', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + this.apiToken
          },
          body: JSON.stringify({
            phone: phoneNumber
          })
        });
        const data = await response.json();

        // Open dialer pad for new contact
        window.location.href = `tel:${data.phone_number}@aircall.talkdesk.com`;
      }
    } catch (error) {
      console.error(error);
    }
  }


  savePhoneNumber(number: string) {
    const url = 'https://api.aircall.io/v1/phone_numbers';
    const body = {
      number: number,
      name: 'Naveed'
      // Add any other parameters required by the AirCall API
    };
    this.http.post(url, body).subscribe(
      response => {
        console.log('Phone number saved to AirCall API:', response);
        // Handle success
      },
      error => {
        console.error('Error saving phone number to AirCall API:', error);
        // Handle error
      }
    );
  }

  getNumbers(): Observable<any> {
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Basic ${btoa(`${this.appId}:${this.apiToken}`)}`
    });
    return this.http.get<any>(`${this.baseUrl}/numbers`, { headers });
  }

  login(): Observable<any> {
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Basic ${btoa(`${this.appId}:${this.apiToken}`)}`
    });
    let data = {
      email: "usmanrajput9990@gmail.com"
      , password: "Sacredsoul!@12"
    }
    return this.http.post<any>(`https://id.aircall.io/auth/v1/users/session`, { data, headers });
  }

  getAllRecordings(): Observable<any> {
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Basic ${btoa(`${this.appId}:${this.apiToken}`)}`
    });

    const url = `${this.baseUrl}/recordings`;

    return this.http.get<any>(url, { headers });
  }


  getCallHistory(startDate?, endDate?) {
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Basic ${btoa(`${this.appId}:${this.apiToken}`)}`
    });
    const params = {
      from: String(startDate),
      to: String(endDate) // Maximum number of records to retrieve per page
    };
    return this.http.get<any>(`${this.baseUrl}/calls`, { headers, params });
  }

  get() {

    console.log("string is ", `Basic ${btoa(`${this.appId}:${this.apiToken}`)}`);

    // const headers = new HttpHeaders()
    //   .set('Content-Type', 'application/json')
    //   .set('Accept', 'application/json')
    //   .set('Authorization', 'Bearer ' + this.apiToken);
    // const headers = new HttpHeaders({
    //   'Content-Type': 'application/json',
    //   'Authorization': 'Bearer ' + this.apiToken
    // });
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Basic ${btoa(`${this.appId}:${this.apiToken}`)}`
    });



    return this.http.get<any>(`${this.baseUrl}/ping`, { headers });
  }


  //get the statics of aircall statics
  statics(from?, to?, page?, perPage?, order?) {
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Basic ${btoa(`${this.appId}:${this.apiToken}`)}`
    });

    const data = {
      from: from,
      to: to,
      page: page,
      per_page: perPage,
      order: order
    };
    const options = { params: data, headers };
    return this.http.get<any>(`${this.baseUrl}/calls`, options);
  }


  enableInteg() {
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Basic ${btoa(`${this.appId}:${this.apiToken}`)}`
    });

    return this.http.get<any>(`${this.baseUrl}/integrations/enable`, { headers });
  }

  getUsers() {
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Basic ${btoa(`${this.appId}:${this.apiToken}`)}`
    });

    return this.http.get<any>(`${this.baseUrl}/users`, { headers });
  }

}

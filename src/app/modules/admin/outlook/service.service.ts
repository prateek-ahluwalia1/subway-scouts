import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Observable, catchError, map, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ServiceService {

  private folders = 'https://outlook.office.com/api/v1.0/me/folders'
  private baseUrl = 'https://graph.microsoft.com/v1.0/me/'
  private profileUrl = 'https://graph.microsoft.com/v1.0/me';
  private profilePicUrl = 'https://graph.microsoft.com/v1.0/me/photo/$value';
  token
  constructor(private http: HttpClient, public global: GlobalVariable) {
  }
  httpOptions = {
    headers: new HttpHeaders({
      'Authorization': 'Bearer ' + localStorage.getItem('outlookToken'),
    }),
  };
  fetchFolders(): Observable<any> {
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      console.error('Access token not found.');
      return throwError('Access token not found.'); // or handle as appropriate
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
    });

    const requestOptions = { headers: headers };
    const endpoint = `https://graph.microsoft.com/v1.0/me/mailFolders`;

    return this.http.get(endpoint, requestOptions);
  }
  fetchFolderDetails(folder): Observable<any> {
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      console.error('Access token not found.');
      return throwError('Access token not found.'); // or handle as appropriate
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
    });

    const requestOptions = { headers: headers };
    const endpoint = `https://graph.microsoft.com/v1.0/me/mailFolders/${folder}`;

    return this.http.get(endpoint, requestOptions);
  }


  getUserProfile(): Observable<any> {

    const token = localStorage.getItem('outlookToken');

    if (!token) {
      // Handle the case where the access token is not available
      console.error('Access token not found.');
      return;
    }
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
    });

    const requestOptions = {
      headers: headers,
    };

    return this.http.get(this.profileUrl, requestOptions);

  }

  getUserProfilePic(): Observable<any> {
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      // Handle the case where the access token is not available
      console.error('Access token not found.');
      return;
    }
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
    });

    const requestOptions = {
      headers: headers,
    };
    return this.http.get(this.profilePicUrl, requestOptions);
  }

  // get folder email
  fetchEmails(folder, currentPage, itemsPerPage): Observable<any> { 
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      // Handle the case where the access token is not available
      console.error('Access token not found.');
      return;
    }
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
    });

    const requestOptions = {
      headers: headers,
    };
    const skip = (currentPage - 1) * itemsPerPage;
    const endpoint = `https://graph.microsoft.com/v1.0/me/mailFolders/${folder}/messages?$top=${itemsPerPage}&$skip=${skip}`;
    return this.http.get(endpoint, requestOptions);
  }

  // get specific email
  fetchEmailById(id: string): Observable<any> {
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      console.error('Access token not found.');
      return;
    }
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
    });

    const requestOptions = {
      headers: headers,
    };
    return this.http.get(`${this.baseUrl}messages/${id}`, requestOptions);
  }

  // get attachemtns of email
  fetchEmailAttachments(emailId: string): Observable<any[]> {
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      console.error('Access token not found.');
      return;
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
    });

    const requestOptions = {
      headers: headers,
    };

    return this.http.get(`${this.baseUrl}messages/${emailId}/attachments`, requestOptions)
      .pipe(
        map((response: any) => {
          return response.value; // Assuming the attachments are stored in the 'value' property of the response
        }),
        catchError((error: any) => {
          console.error('Error fetching email attachments:', error);
          return [];
        })
      );
  }


  // change status to read of when get above specific email
  updateEmailProperties(emailId: string, properties: any): Observable<any> {
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      // Handle the case where the access token is not available
      console.error('Access token not found.');
      return;
    }
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
    });

    const requestOptions = {
      headers: headers,
    };
    const endpoint = `https://graph.microsoft.com/v1.0/me/messages/${emailId}`;
    return this.http.patch(endpoint, properties, requestOptions);
  }

  sendEmail(email: any, attachments: any[]): Observable<any> {
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      // Handle the case where the access token is not available
      console.error('Access token not found.');
      return;
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    });

    const requestOptions = {
      headers: headers,
    };

    const graphApiUrl = 'https://graph.microsoft.com/v1.0/me/microsoft.graph.sendMail';

    const message = {
      message: {
        subject: email.subject,
        body: {
          contentType: 'HTML',
          content: email.body,
        },
        toRecipients: email.to.map(item => ({ emailAddress: { address: item } })),
        ccRecipients: email.cc.map(item => ({ emailAddress: { address: item } })),
        bccRecipients: email.bcc.map(item => ({ emailAddress: { address: item } })),
        attachments: attachments.map(attachment => {
          return {
            '@odata.type': '#microsoft.graph.fileAttachment',
            Name: attachment.name,
            ContentBytes: attachment.content,
          };
        }),
      },
      SaveToSentItems: 'true',
    };


    return this.http.post(graphApiUrl, message, requestOptions);
  }


  // Get all deleted emails
  getAllDeletedEmails(): Observable<any> {
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      // Handle the case where the access token is not available
      console.error('Access token not found.');
      return;
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
    });

    const requestOptions = {
      headers: headers,
    };

    // Construct the endpoint URL for the Deleted Items folder
    const endpoint = `https://graph.microsoft.com/v1.0/me/mailFolders/deleteditems/messages`;

    // Send a GET request to the endpoint
    return this.http.get(endpoint, requestOptions).pipe(
      catchError((error) => {
        console.error('Error getting deleted emails:', error);
        return throwError(error);
      })
    );
  }


  replyForwardEmail(selectEmail, email: any, attachments: any[], type): Observable<any> {
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      console.error('Access token not found.');
      return;
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
    });

    const requestOptions = {
      headers: headers,
    };
    const forwardUrl = `https://graph.microsoft.com/v1.0/me/messages/${selectEmail.id}/forward`;
    const replyEndpoint = `https://graph.microsoft.com/v1.0/me/messages/${selectEmail.id}/reply`;
    const draftEndpoint = `https://graph.microsoft.com/v1.0/me/messages/${selectEmail.id}/send`;

    const message = {
      message: {
        subject: email.subject,
        body: {
          contentType: 'HTML',
          content: email.body,
        },
        toRecipients: email.to.map(item => ({ emailAddress: { address: item } })),
        ccRecipients: email.cc.map(item => ({ emailAddress: { address: item } })),
        bccRecipients: email.bcc.map(item => ({ emailAddress: { address: item } })),
        attachments: attachments.map(attachment => {
          return {
            '@odata.type': '#microsoft.graph.fileAttachment',
            Name: attachment.name,
            ContentBytes: attachment.content,
          };
        }),
      },
      SaveToSentItems: 'true',
    };

    const formData = new FormData();
    formData.append('Message', JSON.stringify(message));
    attachments.forEach((attachment: File, index: number) => {
      formData.append(`Attachments[${index}]`, JSON.stringify({
        '@odata.type': '#Microsoft.OutlookServices.FileAttachment',
        Name: attachment.name,
        ContentBytes: '', // You should populate this with the base64-encoded content of the attachment
      }));
    });

    if (type == 'reply') {
      return this.http.post(replyEndpoint, message, requestOptions);
    }
    else if (type == 'draft') {
      return this.http.post(draftEndpoint, message, requestOptions);
    }
    else {
      return this.http.post(forwardUrl, message, requestOptions);
    }
  }


  deleteEmail(emailId: string): Observable<any> {
    const token = localStorage.getItem('outlookToken');

    if (!token) {
      // Handle the case where the access token is not available
      console.error('Access token not found.');
      return;
    }

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    });

    const requestOptions = {
      headers: headers,
    };
    const deleteUrl = `${this.baseUrl}/messages/${emailId}`;
    return this.http.delete(deleteUrl, requestOptions);
  }


  async downloadAttachment(att): Promise<Blob> {
    const token = localStorage.getItem('outlookToken');
    console.log(token);

    console.log(att.id);

    if (!token) {
      // Handle the case where the access token is not available
      console.error('Access token not found.');
      return;
    }
    const url = `${this.baseUrl}messages/${att.id}/$value`;

    const response = await fetch(url, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
    if (response.ok) {
      return response.blob();
    } else {
      throw new Error(`Failed to download attachment: ${response.status} - ${response.statusText}`);
    }
  }


  getSignature(): string {
    // Assume global and local storage are correctly set up
    const userName = localStorage.getItem('userName') || 'Your Name';
    const name = this.global.admin.admin_name || userName;
    const email = this.global.admin.admin_email;
    const company = this.global.admin?.business?.title;
    const address = this.global.admin?.business?.address;
    const mobile = this.global.admin.mobile || '';
    const phone = this.global.admin.phone || '';
    const website = this.global.admin.website || '';
    const logo = 'assets/images/logo/scouts.png';

    // Construct the signature string dynamically
    let signature = `<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <HTML><HEAD><TITLE>Email Signature</TITLE>
    <META content="text/html; charset=utf-8" http-equiv="Content-Type">
    </HEAD>
    <BODY style="font-size: 10pt; font-family: Arial, sans-serif;">
    
    <table style="width: 420px; font-size: 10pt; font-family: Arial, sans-serif; background: transparent !important;" cellpadding="0" cellspacing="0" border="0">
    <tbody>
        <tr>
            <td style="font-size: 10pt; font-family: Arial, sans-serif; border-right: 1px solid #00a78d; width:200px; padding-right: 10px; vertical-align: top;  padding-bottom: 20px;" valign="top">
                <p style="margin-bottom:25px; padding-bottom: 0px; line-height:1.0">
                    <strong><span style="font-size: 12pt; font-family: Arial, sans-serif; color:#00a78d; line-height: 18pt;">${name ?? ''}</span></strong>
                    <span style="font-family: Arial, sans-serif; font-size:9pt; color:#717171;  line-height: 14pt;"><br>${this.global.admin.title ?? ''}</span>
                </p>  
                <span>
                          <a href="https://www.codetwo.com/email-signatures/" target="_blank"><img border="0" alt="Logo" width="159" style="width:159px; height:auto; border:0;" src="../../../../assets/images/logo/scouts.png"></a>
                      </span>
            </td>
      
            <td valign="top" style="padding-left: 30px; padding-bottom: 20px;"> 
                
                <span><span style="color: #262626;"><strong>E:</strong></span> <a href="mailto:${email}" style="text-decoration: none; font-size: 9pt; font-family: Arial, sans-serif; color:#262626;"><span style="text-decoration: none; font-size: 9pt; font-family: Arial, sans-serif; color:#262626;">${email ?? ''}</span></a><br></span>
                <span><span style="color: #262626;"><strong>P:</strong></span><span style="font-size: 9pt; font-family: Arial, sans-serif; color:#262626;"> ${phone ?? ''}<br></span></span>
    
                <span style="color: #262626;"><strong>A:</strong></span>
                <span>
                    <span style="font-size: 9pt; font-family: Arial, sans-serif; color: #262626;">${address ?? ''}<span>, </span></span>
                </span>		
                        
            </td>
        </tr>
        <tr>
        <td style="border-right: 1px solid #00a78d;vertical-align: top;" valign="top">
            <a href="{website}" target="_blank" rel="noopener" style="font-size: 9pt; font-family: Arial, sans-serif; text-decoration:none; color: #00a78d; font-weight: bold;"><span style="font-size: 9pt; font-family: Arial, sans-serif; text-decoration:none; color: #00a78d; font-weight: bold;">${company ?? ''}</span></a>
        </td>    
        <td valign="top" style="padding-left: 30px;"> 
            <span><a href="https://www.facebook.com/MyCompanyFacebook" target="_blank" rel="noopener"><img border="0" width="26" src="fb.png" alt="facebook icon" style="border:0; height:26px; width:26px"></a>&nbsp;</span><span><a href="https://twitter.com/MyCompanyTwitter" target="_blank" rel="noopener"><img border="0" width="26" src="tt.png" alt="twitter icon" style="border:0; height:26px; width:26px"></a>&nbsp;</span><span><a href="https://www.youtube.com/user/MyCompanyChannel" target="_blank" rel="noopener"><img border="0" width="26" src="yt.png" alt="youtube icon" style="border:0; height:26px; width:26px"></a>&nbsp;</span><span><a href="https://www.linkedin.com/company/mycompanylinkedin" target="_blank" rel="noopener"><img border="0" width="26" src="ln.png" alt="linkedin icon" style="border:0; height:26px; width:26px"></a>&nbsp;</span><span><a href="https://www.instagram.com/mycompanyinstagram/" target="_blank" rel="noopener"><img border="0" width="26" src="it.png" alt="instagram icon" style="border:0; height:26px; width:26px"></a>&nbsp;</span><span><a href="https://pinterest.com/mycompanypinterest/" target="_blank" rel="noopener"><img border="0" width="26" src="pt.png" alt="pinterest icon" style="border:0; height:26px; width:26px"></a></span>
        </td>
    </tr>
        <!-- Social icons and other details -->
    </tbody>
    </table>
    
    </BODY>
    </HTML>
    `;

    return signature;
  }

  searchEmails(query: string): Observable<any> {
    const token = localStorage.getItem('outlookToken');
    if (!token) {
      console.error('Access token not found.');
      return throwError('Access token not found.');
    }
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    });
    const requestOptions = {
      headers: headers,
    };
    const url = `https://graph.microsoft.com/v1.0/me/messages?$search="${encodeURIComponent(query)}"`;
    return this.http.get(url, requestOptions);
  }

}

import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { BehaviorSubject, Observable, of, throwError } from 'rxjs';
import { catchError, map, switchMap, take, tap } from 'rxjs/operators';
import { Mail, MailCategory, MailFilter, MailFolder, MailLabel } from 'app/modules/admin/mailbox/mailbox.types';

@Injectable({
    providedIn: 'root'
})
export class MailboxService {
    private baseUrl = 'https://graph.microsoft.com/v1.0/me/';
    selectedMailChanged: BehaviorSubject<any> = new BehaviorSubject(null);
    private _folders: BehaviorSubject<MailFolder[]> = new BehaviorSubject(null);
    private _mails: BehaviorSubject<Mail[]> = new BehaviorSubject(null);
    private _mailsLoading: BehaviorSubject<boolean> = new BehaviorSubject(false);
    private _mail: BehaviorSubject<Mail> = new BehaviorSubject(null);
    private _pagination: BehaviorSubject<any> = new BehaviorSubject(null);

    constructor(private _httpClient: HttpClient) { }

    get folders$(): Observable<MailFolder[]> {
        return this._folders.asObservable();
    }

    get mails$(): Observable<Mail[]> {
        return this._mails.asObservable();
    }

    get mailsLoading$(): Observable<boolean> {
        return this._mailsLoading.asObservable();
    }

    get mail$(): Observable<Mail> {
        return this._mail.asObservable();
    }

    get pagination$(): Observable<any> {
        return this._pagination.asObservable();
    }

    private folderSubject = new BehaviorSubject<string>(null);
    folder$ = this.folderSubject.asObservable();

    setFolder(folder: string) {
        this.folderSubject.next(folder);
    }

    getCurrentFolder(): string {
        return this.folderSubject.getValue();
    }

    getFolders(): Observable<any> {
        return this._httpClient.get<MailFolder[]>(`${this.baseUrl}mailFolders`).pipe(
            tap((response: any) => {
                this._folders.next(response.value);
            })
        );
    }

    getMailsByFolder(folderId: string, page: string = '1'): Observable<any> {
        const pageNumber = isNaN(parseInt(page, 10)) ? 1 : parseInt(page, 10);
        const skip = (pageNumber - 1) * 20;
        const params = new HttpParams()
            .set('$top', '20')
            .set('$skip', skip.toString());
        this._mailsLoading.next(true);

        return this._httpClient.get<Mail[]>(`${this.baseUrl}mailFolders/${folderId}/messages`, { params }).pipe(
            tap((response: any) => {
                this._mails.next(response.value);
                this._pagination.next({
                    currentPage: pageNumber,
                    totalResults: response.value.length, // Number of items on the current page
                    startIndex: skip,
                    endIndex: skip + response.value.length - 1,
                    nextPage: response['@odata.nextLink'] ? pageNumber + 1 : null,
                    prevPage: pageNumber > 1 ? pageNumber - 1 : null,
                    hasNext: !!response['@odata.nextLink'], // Check if there is a next page
                });

                this._mailsLoading.next(false);
                this.setFolder(folderId);
            }),
            catchError((error: any) => {
                this._mailsLoading.next(false);
                console.error('Error fetching mails:', error);
                return throwError(error);
            })
        );
    }


    getMailById(id: string): Observable<any> {
        return this._mails.pipe(
            take(1),
            switchMap((mails) => {
                const mail = mails ? mails.find(item => item.id === id) : null;
                if (mail) {
                    this._mail.next(mail);
                    return of(mail);
                } else {
                    return this._httpClient.get(`${this.baseUrl}messages/${id}`).pipe(
                        tap((fetchedMail: any) => {
                            this._mail.next(fetchedMail);
                        }),
                        catchError((error: any) => {
                            console.error('Error fetching mail:', error);
                            return throwError(error);
                        })
                    );
                }
            })
        );
    }


    updateMail(id: string, properties): Observable<any> {
        return this._httpClient.patch(`https://graph.microsoft.com/v1.0/me/messages/${id}`, properties, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 200) {
                    this.getFolders().subscribe();
                    return { success: true, message: `Email ${properties.isRead ? 'read' : 'unread'} successfully.` };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while changing the status of the email.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

    resetMail(): Observable<boolean> {
        return of(true).pipe(
            take(1),
            tap(() => {
                this._mail.next(null);
            })
        );
    }

    sendMail(email: any): Observable<any> {
        const emailData = {
            message: {
                subject: email.subject,
                body: {
                    contentType: 'HTML',
                    content: email.body
                },
                toRecipients: email.to.split(',').map((address: string) => ({
                    emailAddress: { address: address.trim() }
                })),
                ccRecipients: email.cc ? email.cc.split(',').map((address: string) => ({
                    emailAddress: { address: address.trim() }
                })) : [],
                bccRecipients: email.bcc ? email.bcc.split(',').map((address: string) => ({
                    emailAddress: { address: address.trim() }
                })) : [],
                attachments: email.attachments ? email.attachments.map(file => ({
                    '@odata.type': '#microsoft.graph.fileAttachment',
                    name: file.name,
                    contentBytes: file.content
                })) : []
            },
            saveToSentItems: true
        };

        return this._httpClient.post(`${this.baseUrl}sendMail`, emailData, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 202) {
                    return { success: true, message: 'Email sent successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while sending the email.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

    saveDraft(email: any): Observable<any> {
        const draftData = {
            subject: email.subject,
            body: {
                contentType: 'HTML',
                content: email.body
            },
            toRecipients: email.to ? email.to.split(',').map((address: string) => ({
                emailAddress: { address: address.trim() }
            })) : [],
            ccRecipients: email.cc ? email.cc.split(',').map((address: string) => ({
                emailAddress: { address: address.trim() }
            })) : [],
            bccRecipients: email.bcc ? email.bcc.split(',').map((address: string) => ({
                emailAddress: { address: address.trim() }
            })) : [],
            attachments: email.attachments ? email.attachments.map(file => ({
                '@odata.type': '#microsoft.graph.fileAttachment',
                name: file.name,
                contentBytes: file.content
            })) : []
        };

        return this._httpClient.post(`${this.baseUrl}messages`, draftData, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 201) {
                    return { success: true, message: 'Draft saved successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while saving the draft.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }


    // get attachemtns of email
    fetchEmailAttachments(emailId: string): Observable<any[]> {
        this._mailsLoading.next(true);

        return this._httpClient.get<any>(`${this.baseUrl}messages/${emailId}/attachments`).pipe(
            map((response: any) => {
                this._mailsLoading.next(false);
                return response.value;
            }),
            catchError((error: any) => {
                this._mailsLoading.next(false);
                console.error('Error fetching email attachments:', error);
                return [];
            })
        );
    }

    // forward email
    forwardToMail(mailId: string, mail: any): Observable<any> {
        let emailData;
        emailData = {
            comment: mail.body,
            toRecipients: mail.to,
        };
        return this._httpClient.post(`${this.baseUrl}messages/${mailId}/forward`, emailData, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 202) {
                    return { success: true, message: 'Email sent successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while sending the email.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }


    replyToMail(mailId: string, mail: any, api: string): Observable<any> {
        let emailData;
        emailData = {
            message: {
                body: {
                    contentType: 'HTML',
                    content: mail.body
                },
                attachments: mail.attachments ? mail.attachments.map(file => ({
                    '@odata.type': '#microsoft.graph.fileAttachment',
                    name: file.name,
                    contentBytes: file.content
                })) : []
            },
            saveToSentItems: true
        };
        return this._httpClient.post(`${this.baseUrl}messages/${mailId}/${api}`, emailData, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 202) {
                    return { success: true, message: 'Email sent successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while sending the email.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

    moveMailToTrash(mailId: string): Observable<any> {
        const body = { destinationId: 'deleteditems' };
        return this._httpClient.post(`${this.baseUrl}messages/${mailId}/move`, body, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 201) {
                    return { success: true, message: 'Email move to trash successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while sending the email.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

    getConversationEmails(conversationId: string): Observable<any> {
        return this._httpClient.get(`${this.baseUrl}messages?$filter=conversationId eq '${conversationId}'`).pipe(
            map((response: any) => response.value),
            catchError((error: any) => {
                console.error('Error fetching conversation emails:', error);
                return throwError(error);
            })
        );
    }

    getMailsByRecipient(): Observable<any[]> {
        const params = new HttpParams()
            .set('$top', '500');
        // .set('$select', 'subject,from,toRecipients,body')

        return this._httpClient.get<{ value: any[] }>(`${this.baseUrl}messages`, { params }).pipe(
            map((response: any) => response.value),
            catchError((error: any) => {
                console.error('Error fetching mails by recipient:', error);
                return throwError(error);
            })
        );
    }

    moveMailToInbox(mailId: string): Observable<any> {
        const body = { destinationId: 'inbox' };
        return this._httpClient.post(`${this.baseUrl}messages/${mailId}/move`, body, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 201) {
                    return { success: true, message: 'Email moved to Inbox successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while moving the email to Inbox.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

    sendDraftMail(draftId: string): Observable<any> {
        return this._httpClient.post(`${this.baseUrl}messages/${draftId}/send`, null, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 202) {
                    return { success: true, message: 'Draft sent successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while sending the draft.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }


    updateDraftMail(draftId: string, email: any): Observable<any> {
        const updateData = {
            subject: email.subject,
            body: {
                contentType: 'HTML',
                content: email.body
            },
            toRecipients: email.toRecipients,
            ccRecipients: email.ccRecipients,
            bccRecipients: email.bccRecipients,
            attachments: email.attachments ? email.attachments.map(file => ({
                '@odata.type': '#microsoft.graph.fileAttachment',
                name: file.name,
                contentBytes: file.contentBytes
            })) : []
        };

        return this._httpClient.patch(`${this.baseUrl}messages/${draftId}`, updateData, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 200) {
                    return { success: true, message: 'Draft updated successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while updating the draft.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }


    flagMail(mailId: string, flagStatus: 'notFlagged' | 'complete' | 'flagged'): Observable<any> {
        const body = {
            flag: {
                flagStatus: flagStatus
            }
        };
        return this._httpClient.patch(`${this.baseUrl}messages/${mailId}`, body, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 200) {
                    return { success: true, message: `Email flagged as ${flagStatus} successfully.` };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while flagging the email.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }


    updateImportance(mailId: string, importance: string): Observable<any> {
        const body = { importance: importance };
        return this._httpClient.patch(`${this.baseUrl}messages/${mailId}`, body, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 200) {
                    return { success: true, message: 'Email importance updated successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while updating the importance of the email.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

    updateFlag(mailId: string, flagStatus: string): Observable<any> {
        const body = { flag: { flagStatus: flagStatus } };
        return this._httpClient.patch(`${this.baseUrl}messages/${mailId}`, body, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 200) {
                    return { success: true, message: 'Email flag status updated successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while updating the flag status of the email.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

    toggleStar(mailId: string, starred: boolean): Observable<any> {
        const body = { categories: starred ? ['Starred'] : [] };
        return this._httpClient.patch(`${this.baseUrl}messages/${mailId}`, body, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 200) {
                    return { success: true, message: 'Email star status updated successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while updating the star status of the email.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }


    getFolderIdByName(folderName: string): Observable<string> {
        return this.getFolders().pipe(
            map((folders) => {
                const folder = folders.find(f => f.displayName.toLowerCase() === folderName.toLowerCase());
                return folder ? folder.id : null;
            })
        );
    }

    moveMailToArchive(mailId: string): Observable<any> {
        const body = { destinationId: 'archive' };
        return this._httpClient.post(`${this.baseUrl}messages/${mailId}/move`, body, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 201) {
                    return { success: true, message: 'Email moved to archive successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while moving the email to archive.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

    unarchiveMail(mailId: string, destinationFolderId: string = 'inbox'): Observable<any> {
        const body = { destinationId: destinationFolderId };
        return this._httpClient.post(`${this.baseUrl}messages/${mailId}/move`, body, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 201) {
                    return { success: true, message: 'Email moved to inbox successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while moving the email to inbox.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

    restoreDeletedMail(mailId: string): Observable<any> {
        const body = { destinationId: 'inbox' };
        return this._httpClient.post(`${this.baseUrl}messages/${mailId}/move`, body, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 201) {
                    this.getFolders().subscribe();
                    return { success: true, message: 'Email restored to Inbox successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while restoring the email to Inbox.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

    permanentlyDeleteMail(mailId: string): Observable<any> {
        return this._httpClient.delete(`${this.baseUrl}messages/${mailId}`, { observe: 'response' }).pipe(
            map(response => {
                if (response.status === 204) {
                    this.getFolders().subscribe();
                    return { success: true, message: 'Email permanently deleted successfully.' };
                }
                return { success: false, message: 'Unexpected response from server.' };
            }),
            catchError(error => {
                let errorMessage = 'An error occurred while permanently deleting the email.';
                if (error.error && error.error.error) {
                    errorMessage = error.error.error.message;
                }
                return throwError({ success: false, message: errorMessage, code: error.error.error.code });
            })
        );
    }

}
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { BehaviorSubject, map, Observable, of, switchMap, take, tap, throwError } from 'rxjs';
import { Item, Items } from 'app/modules/admin/file-manager/file-manager.types';
import { GlobalVariable } from 'app/shared/global';

@Injectable({
    providedIn: 'root'
})
export class FileManagerService {
    // Private
    private _item: BehaviorSubject<Item | null> = new BehaviorSubject(null);
    private _items: BehaviorSubject<Items | null> = new BehaviorSubject(null);
    httpOptions = {
        headers: new HttpHeaders({
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
        }),
    };
    constructor(private _httpClient: HttpClient, private global: GlobalVariable) {
    }

    getAllStaff(): Observable<any> {
        let data = {
            status:'active',
            pageIndex: 0,
            pageSize: 5000,
        }
        return this._httpClient.post<any>(`${this.global.baseUrl}get-all-guards`, data, this.httpOptions);
    }
    getCustomer(): Observable<any> {
        let data = {
            status: 'active'
        }
        return this._httpClient.post<any>(`${this.global.baseUrl}get-customers`,data, this.httpOptions);
    }
    getAdmins(): Observable<any> {
        return this._httpClient.get<any>(`${this.global.baseUrl}get-sub-admins`, this.httpOptions);
    }
    getContractor(): Observable<any> {
        return this._httpClient.get<any>(`${this.global.baseUrl}get-contractors`, this.httpOptions);
    }
    getSpecificStaffDocs(id,type): Observable<any> {
        let data = {
            id: id,
            type:type
        }
        return this._httpClient.post<any>(`${this.global.baseUrl}get-guard-docs-name`, data, this.httpOptions);
    }
    getSpecificStaffFiles(id,type): Observable<any> {
        let data = {
            id: id,
            type:type
        }
        return this._httpClient.post<any>(`${this.global.baseUrl}get-guard-document-file`, data, this.httpOptions);
    }
    // -----------------------------------------------------------------------------------------------------
    // @ Accessors
    // -----------------------------------------------------------------------------------------------------

    /**
     * Getter for items
     */
    get items$(): Observable<Items> {
        console.log('items');

        return this._items.asObservable();
    }

    /**
     * Getter for item
     */
    get item$(): Observable<Item> {
        console.log('item');

        return this._item.asObservable();
    }

    getItemById(id: string): Observable<Item> {
        console.log(id, 'here is id');

        return this._items.pipe(
            take(1),
            map((items) => {

                // Find within the folders and files
                const item = [...items.folders, ...items.files].find(value => value.id === id) || null;

                // Update the item
                this._item.next(item);

                // Return the item
                return item;
            }),
            switchMap((item) => {

                if (!item) {
                    return throwError('Could not found the item with id of ' + id + '!');
                }

                return of(item);
            })
        );
    }
}

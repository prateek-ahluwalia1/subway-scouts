import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';
import Pusher from 'pusher-js';

@Injectable({
    providedIn: 'root'
})
export class PusherService {
    private pusher: Pusher;
    private _notification = new Subject<any>();

    notification$ = this._notification.asObservable();

    constructor() {
        this.connect();
    }

    private connect(): void {
        this.pusher = new Pusher('a1a91e8e3217abe50dee', {
            cluster: 'ap2',
        });

        const channel = this.pusher.subscribe('new-mail-notification');
        channel.bind('new-mail-notification', (data: any) => {
            this._notification.next(data.message);
        });
    }
}

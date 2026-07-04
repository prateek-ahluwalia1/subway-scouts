import { Component, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { Router } from '@angular/router';
import { finalize, Subject, takeUntil, takeWhile, tap, timer } from 'rxjs';
import { AuthService } from 'app/core/auth/auth.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { GlobalVariable } from 'app/shared/global';

@Component({
    selector: 'auth-sign-out',
    templateUrl: './sign-out.component.html',
    encapsulation: ViewEncapsulation.None
})

export class AuthSignOutComponent implements OnInit, OnDestroy {
    httpOptions = {
        headers: new HttpHeaders({
            'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
            'Content-Type': 'application/json'
        })
    };
    countdown: number = 5;
    countdownMapping: any = {
        '=1': '# second',
        'other': '# seconds'
    };
    private _unsubscribeAll: Subject<any> = new Subject<any>();

    constructor(
        private _authService: AuthService,
        private _router: Router,
        private global: GlobalVariable,
        private _httpClient: HttpClient
    ) {}

    ngOnInit(): void {
        // Sign out
        var link = this.global.baseUrl + 'logout';
        this._httpClient.get<any>(link, this.httpOptions);
        this._authService.signOut();
        timer(1000, 1000)
        .pipe(
            finalize(() => {
                this._router.navigate(['sign-in']);
                window.location.reload();
            }),
            takeWhile(() => this.countdown > 0),
            takeUntil(this._unsubscribeAll),
            tap(() => this.countdown--)
        )   
        .subscribe();
    }

    ngOnDestroy(): void {
        // Unsubscribe from all subscriptions
        this._unsubscribeAll.next(null);
        this._unsubscribeAll.complete();
    }
}

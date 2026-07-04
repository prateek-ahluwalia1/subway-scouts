import { animate, query, stagger, style, transition, trigger } from '@angular/animations';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit, OnDestroy, ViewChild, ViewEncapsulation } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ServiceService } from 'app/services/service.service';
import moment from 'moment';
import { Subject } from 'rxjs';
import { filter, takeUntil } from 'rxjs/operators';
import { SigninoutComponent } from './signinout/signinout.component';
import AircallPhone from 'aircall-everywhere';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
  selector: 'app-live-operation',
  templateUrl: './live-operation.component.html',
  styleUrls: ['./live-operation.component.scss'],
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('700ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('700ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ],
  encapsulation: ViewEncapsulation.None,
  changeDetection: ChangeDetectionStrategy.OnPush
})

export class LiveOperationComponent implements OnInit, OnDestroy {

  polling_data = [];
  filteredData: any[] = [];
  @ViewChild('fullScreen') divRef;
  isFullscreen: boolean = false;
  searchText: string = '';
  private ngUnsubscribe = new Subject();
  showMobile: boolean = true;
  private aircallPhone: AircallPhone;

  constructor(private service: ServiceService, private _changeDetect: ChangeDetectorRef,
    private modelService: NgbModal, private toast: ToastServiceService) { }

  ngOnInit() {
    this.service.liveOperations$.pipe(
      filter(res => res && res.data),
      takeUntil(this.ngUnsubscribe)
    )
    .subscribe(res => {
      res?.data.forEach(element => {
        element.name = element.first_name + ' ' + element.last_name;
      });
      this.polling_data = res.data;
      this.filteredData = res.data;
      this.calculateTimeDifferences();
      this._changeDetect.markForCheck();
    });
  }

  ngOnDestroy() {
    this.ngUnsubscribe.next(this.ngUnsubscribe);
    this.ngUnsubscribe.complete();
  }

  openFullscreen() {
    this.isFullscreen = !this.isFullscreen
  }

  trackByFn(index: number, item: any): number {
    return index;
  }

  getBadgeClass(job: any): string {
    const formattedName = this.getFormattedName(job);
    switch (formattedName) {
      case 'CLOCKED OUT':
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
      case 'CLOCKED OUT OFF LOCATION':
        return 'badge badge-pill badge-dark';
      case 'Not Confirmed Yet':
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
      case 'CLOCKED IN - On Location':
        return 'badge badge-pill badge-success';
      case 'Confirmed Job':
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
      case 'Missed Job':
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
      case 'Mock Job':
        return 'badge rounded p-2 badge-secondary';
      case 'Unassigned Shift':
        return 'badge rounded p-2 badge-dark';
      default:
        return ''; // Default class
    }
  }

  getFormattedName(job: any): string {
    if (job.status === 'completed' && job.auto_signout === '0') {
      return 'CLOCKED OUT';
    } else if (job.status === 'missed') {
      return 'Missed Job';
    } else if (job.status === 'completed' && job.auto_signout === '1') {
      return 'CLOCKED OUT OFF LOCATION';
    }
    else if (job.status === 'pending' && job.first_name) {
      return 'Not Confirmed Yet';
    } else if (job.status === 'confirmed' && job.signin_status == 1) {
      return 'CLOCKED IN - On Location';
    } else if (job.status === 'confirmed' && job.signin_status == 0) {
      return 'Confirmed Job';
    } else if (job.unprofile_name && !job.guard_id) {
      return 'Mock Job';
    } else {
      return 'Unassigned Shift';
    }
  }

  calculateTimeDifferences(): void {
    this.filteredData.forEach(data => {
      if (data.signin_time && data.start_time) {
        const startTime = moment(data.start, 'DD-MM-YYYY HH:mm');
        const signinTime = moment(data.signin_time, 'HH:mm');

        const timeDiff = signinTime.diff(startTime);
        const duration = moment.duration(timeDiff);

        if (duration.asMinutes() === 0) {
          data.signinDifference = "On time";
        } else if (duration.asMinutes() < 0) {
          data.signinDifference = `${Math.abs(duration.asMinutes())} min early`;
          data.signinStatus = `early`;
        } else {
          data.signinDifference = `${duration.asMinutes()} min late`;
          data.signinStatus = `late`;

        }
      }

      if (data.signout_time && data.end_time) {
        const endTime = moment(data.end, 'DD-MM-YYYY HH:mm');
        const signoutTime = moment(data.signout_time, 'HH:mm');
        const timeDiff = signoutTime.diff(endTime);
        const duration = moment.duration(timeDiff);

        if (duration.asMinutes() === 0) {
          data.signoutDifference = "On time";
        } else if (duration.asMinutes() < 0) {
          data.signoutDifference = `${Math.abs(duration.asMinutes())} min early`;
          data.signoutStatus = `early`;

        } else {
          data.signoutDifference = `${duration.asMinutes()} min late`;
          data.signoutStatus = `late`;

        }
      }
    });
  }

  signInOutDetail(data) {
    const modelRef = this.modelService.open(SigninoutComponent, {
      centered: true,
      size: 'lg',
      windowClass: 'signin-out-dashboard'
    })
    modelRef.componentInstance.data = data
  }

  loadPhone(phone) {
    this.showMobile = false;
    if (!this.aircallPhone) {
      this.aircallPhone = new AircallPhone({
        domToLoadPhone: '#phone',
        onLogin: (settings) => {
          this.aircallPhone.isLoggedIn(response => {
            if (response) {
              console.log('User is logged in');
              this.dialPhoneNumber(phone);
            } else {
              this.toast.toastNotification1('User is not logged in', 'Call Operation!')
            }
          });
        },
        onLogout: () => {
          this.toast.toastNotification('User is logged out', 'Call Operation!')
        },
      });
    } else {
      this.dialPhoneNumber(phone);
    }
  }

  dialPhoneNumber(phoneNumber: string) {
    if (this.aircallPhone) {
      this.aircallPhone.send('dial_number', { phone_number: phoneNumber }, (success, data) => {
        console.log(success, data);
      });
    } else {
      console.error('AircallPhone instance is not available.');
    }
  }

  shideMobile() {
    this.showMobile = !this.showMobile;
  }

  trackById(index: number, job: any) {
    return job.roster_id
  }
}

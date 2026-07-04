import { animate, query, stagger, style, transition, trigger } from '@angular/animations';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { ServiceService } from 'app/services/service.service';
import { Subject, Subscription, filter, takeUntil } from 'rxjs';
import { FullScreenService } from '../full-screen.service';

@Component({
  selector: 'app-security-license',
  templateUrl: './security-license.component.html',
  styles: [`
    .table-container-security {
      position: relative;
      max-height: 400px;
      overflow-y: scroll;
      overflow-x: hidden;
      table {
        position: sticky;
        top: 0;
        thead {
          position: sticky;
          top: -25px;
          background-color: #f0f0f0;
          z-index: 1;
          tr {
            th {
              background-color: black !important;
              color: white;
            }
          }
        }
      }
    }

    .full-height-license {
      position: relative;
      max-height: 100%;
      overflow-y: scroll;
      table {
        position: sticky;
        top: 0;
        thead {
          position: sticky;
          top: -25px;
          background-color: #f0f0f0;
          z-index: 1;
          tr {
            th {
              background-color: black !important;
              color: white;
            }
          }
        }
      }
    }`
  ],
  
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

export class SecurityLicenseComponent implements OnInit, OnDestroy {

  securityLicenseData: any[] = []; // Define a variable to store the data
  @ViewChild('fullScreen') divRef;
  isFullscreen: boolean = false
  private _unsubscribeAll: Subject<any> = new Subject<any>();

  constructor(private service: ServiceService, private _changeDetect: ChangeDetectorRef,
  private fullScreen: FullScreenService) {}

  ngOnInit() {
    this.service.graphData$.pipe(filter(res => res && res.getNearExpireLicenseGuard),
    takeUntil(this._unsubscribeAll))
    .subscribe((res: any) => {
      this.securityLicenseData = res?.getNearExpireLicenseGuard.data
      this._changeDetect.markForCheck();
    });
  }

  ngOnDestroy() {
    this._unsubscribeAll.next(null);
    this._unsubscribeAll.complete();
  }

  openFullscreen() {
    const elem = this.divRef.nativeElement;
    this.fullScreen.toggleFullscreen(elem)
    this.isFullscreen = !this.isFullscreen
  }

  trackByFn(index: number, item: any): number {
    return index;
  }

  statusCheck(expireDate: string): string {
    const expirationDate = new Date(expireDate);
    const currentDate = new Date();
    const timeDifference = expirationDate.getTime() - currentDate.getTime();
    const daysUntilExpiration = Math.ceil(timeDifference / (1000 * 3600 * 24));
    if (daysUntilExpiration < 0) {
      return 'Expired';
    } else if (daysUntilExpiration === 0) {
      return 'Expires today';
    } else {
      return `Expires in ${daysUntilExpiration} day(s)`;
    }
  }
}

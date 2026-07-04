import { animate, query, stagger, style, transition, trigger } from '@angular/animations';
import { DatePipe } from '@angular/common';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit, OnDestroy, ViewChild, ViewEncapsulation } from '@angular/core';
import { ServiceService } from 'app/services/service.service';
import { filter, takeUntil } from 'rxjs';
import { Subject } from 'rxjs';
import { FullScreenService } from '../full-screen.service';

@Component({
  selector: 'app-reminder-compliance',
  templateUrl: './reminder-compliance.component.html',
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

export class ReminderComplianceComponent implements OnInit, OnDestroy {

  remindersComplianceExpDocs:any[] = [];
  securityLicenseData: any; // Define a variable to store the data
  @ViewChild('fullScreen') divRef;
  isFullscreen: boolean = false
  private _unsubscribeAll: Subject<any> = new Subject<any>();

  constructor(private service: ServiceService, private _changeDetect: ChangeDetectorRef,
    private datePipe: DatePipe, private fullScreen: FullScreenService) {
  }

  ngOnInit() {
    this.service.graphData$.pipe(filter(res => res && res.remindersComplianceExpDocs),
    takeUntil(this._unsubscribeAll))
    .subscribe((res: any) => {
      this.remindersComplianceExpDocs = res?.remindersComplianceExpDocs.data
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

  expiryDate(date) {
    const parsedDate = new Date(date);
    return this.datePipe.transform(parsedDate, 'MMM dd yyyy');
  }

}

import { animate, query, stagger, style, transition, trigger } from '@angular/animations';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { ServiceService } from 'app/services/service.service';
import { Subject, filter, takeUntil } from 'rxjs';
import { FullScreenService } from '../full-screen.service';
import { GlobalVariable } from 'app/shared/global';
import { ToastServiceService } from 'app/services/toast-service.service';
import { AdminsRemarkComponent } from './admins-remark/admins-remark.component';
import { ButtonLayoutDisplay, ButtonMaker, DialogInitializer, DialogLayoutDisplay } from '@costlydeveloper/ngx-awesome-popup';

@Component({
  selector: 'app-welfare-check',
  templateUrl: './welfare-check.component.html',
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

  styles: [`
  .table-container-welfare {
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
        }
    }
}`],
  encapsulation: ViewEncapsulation.None,
  changeDetection: ChangeDetectionStrategy.OnPush
})

export class WelfareCheckComponent implements OnInit, OnDestroy {

  liveWelfareCallData: any[] = [];
  @ViewChild('fullScreen') divRef;
  isFullscreen: boolean = false
  private _unsubscribeAll: Subject<any> = new Subject<any>();

  constructor(private service: ServiceService, private _changeDetect: ChangeDetectorRef,
  private fullScreen: FullScreenService, private global: GlobalVariable, private toast: ToastServiceService) {}

  ngOnInit() {
    this.service.graphData$
      .pipe(filter(res => res && res.callData),
        takeUntil(this._unsubscribeAll))
      .subscribe((res: any) => {
        this.liveWelfareCallData = res?.callData
        this._changeDetect.markForCheck();
      });
  }

  ngOnDestroy(): void {
    // Unsubscribe from all subscriptions
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

  onSlideToggleChange(data) {
    data.admin_id = this.global.admin.admin_id
    let status = 'Green Call Operation!'
    this.service.greenCall(data).subscribe(({ msg, success }) => {
      if (success) {
        this.toast.toastNotification(msg, status)
      }
      else {
        this.toast.toastNotification1(msg, status)
      }
    }, (error => {
      this.toast.toastNotification1(this.global.apiError, status)
    }))
  }

  detail(data) {
    const dialogPopup = new DialogInitializer(AdminsRemarkComponent);
    dialogPopup.setConfig({
      width: '450px',
      layoutType: DialogLayoutDisplay.INFO // SUCCESS | INFO | NONE | DANGER | WARNING
    });
    dialogPopup.setCustomData({ data: data });
    dialogPopup.setButtons([
      new ButtonMaker('Close', 'close', ButtonLayoutDisplay.DARK),
      new ButtonMaker('Save', 'save', ButtonLayoutDisplay.SUCCESS),
    ]);
    dialogPopup.openDialog$().subscribe(resp => {
      console.log('dialog response: ', resp);
    });
  }
}

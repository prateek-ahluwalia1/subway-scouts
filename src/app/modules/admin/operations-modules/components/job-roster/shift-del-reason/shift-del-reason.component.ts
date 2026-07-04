import { Component, OnInit, OnDestroy, Inject } from '@angular/core';
import { Subscription } from 'rxjs';
import { DialogBelonging } from '@costlydeveloper/ngx-awesome-popup';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { RunsheetrosterService } from 'app/services/runsheetroster.service';

@Component({
  selector: 'app-shift-del-reason',
  templateUrl: './shift-del-reason.component.html'
})
export class ShiftDelReasonComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription = new Subscription();

  constructor(@Inject('dialogBelonging') public dialogBelonging: DialogBelonging,
    private rosterService: JobRoster1Service, private toast: ToastServiceService,
    private global: GlobalVariable, private roster: RunsheetrosterService,) { }

  ngOnInit(): void {
    console.log(this.dialogBelonging);
    this.subscriptions.add(
      this.dialogBelonging.eventsController.onButtonClick$.subscribe((_Button) => {
        if (_Button.ID === 'submit') {
          let status = 'Shift Operation!'
          const reason = this.dialogBelonging.customData.reason;
          const type = this.dialogBelonging.customData.type;
          if (type === 'run_sheet') {
            if (reason) {
              this.roster.delShift(this.dialogBelonging.customData).subscribe(res => {
                if (res.success) {
                  this.roster.publishShiftData(this.dialogBelonging.customData);
                  this.toast.toastNotification(res.message, status)
                  this.dialogBelonging.eventsController.close();
                }
              }, (error) => {
                this.toast.toastNotification1(this.global.apiError, 'Error')
              });
            }
            else {
              this.toast.toastNotification1('Please tell use reason to delete this shift', status)
              return
            }
          }
          else {
            if (reason) {
              this.rosterService.delShift(this.dialogBelonging.customData).subscribe(res => {
                if (res.success) {
                  this.toast.toastNotification(res.message, status)
                  this.dialogBelonging.eventsController.close();
                }
              }, (error) => {
                this.toast.toastNotification1(this.global.apiError, 'Error')
              });
            }
            else {
              this.toast.toastNotification1('Please tell us reason to delete this shift', status)
              return
            }
          }
        }
        else if (_Button.ID === 'cancel') {
          this.dialogBelonging.eventsController.close();
        }
      })
    );
    setTimeout(() => {
      this.dialogBelonging.eventsController.closeLoader();
    }, 1000);
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

}

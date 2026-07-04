import { Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { DialogBelonging } from '@costlydeveloper/ngx-awesome-popup';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-staff-response',
  templateUrl: './staff-response.component.html',
})

export class StaffResponseComponent implements OnInit, OnDestroy {

  type
  confirmData
  unconfirmData
  private subscriptions: Subscription = new Subscription();

  constructor(@Inject('dialogBelonging') public dialogBelonging: DialogBelonging,) { }

  ngOnInit(): void {
    console.log(this.dialogBelonging);
    this.type = this.dialogBelonging.customData.type
    this.confirmData = this.dialogBelonging.customData.ConfirmstaffData
    this.unconfirmData = this.dialogBelonging.customData.UnConfirmStaffdata
    setTimeout(() => {
      // Close the loader after some data is ready.
      // IDialogEventsController
      this.dialogBelonging.eventsController.closeLoader();
    }, 1500);

    this.subscriptions.add(

      this.dialogBelonging.eventsController.onButtonClick$.subscribe((_Button) => {
        if (_Button.ID === 'close') {
          this.dialogBelonging.eventsController.close();
        }
      })
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  handleBrokenImage(event: Event) {
    const imgElement = event.target as HTMLImageElement;
    imgElement.src = '../../../../assets/images/avatars/user-128.png';
  }
}

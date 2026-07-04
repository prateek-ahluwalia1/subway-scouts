import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { DialogBelonging } from '@costlydeveloper/ngx-awesome-popup';
import { SiteService } from 'app/services/site.service';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-pay-charge-history',
  templateUrl: './pay-charge-history.component.html',
  styleUrls: ['./pay-charge-history.component.scss'],
})
export class PayChargeHistoryComponent implements OnInit, OnDestroy {
  siteid: any
  type: any
  history: any[] = [];
  private subscriptions: Subscription = new Subscription();

  constructor(@Inject('dialogBelonging') public dialogBelonging: DialogBelonging, private siteService: SiteService) { }

  ngOnInit(): void {
    this.siteid = this.dialogBelonging.customData.site_id
    this.type = this.dialogBelonging.customData.type
    if (this.type == "pay") {
      this.siteService.payRateHistory(this.siteid).subscribe(({ success, history }) => {
        if (success) {
          this.history = history
          this.dialogBelonging.eventsController.closeLoader();
        }
        else {
          this.dialogBelonging.eventsController.closeLoader();
        }
      })
    }

    if (this.type == "chargeRate") {
      this.siteService.chargeRateHistory(this.siteid).subscribe(({ success, history }) => {
        if (success) {
          this.history = history
          this.dialogBelonging.eventsController.closeLoader();
        }
        else {
          this.dialogBelonging.eventsController.closeLoader();
        }

      })
    }


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

}

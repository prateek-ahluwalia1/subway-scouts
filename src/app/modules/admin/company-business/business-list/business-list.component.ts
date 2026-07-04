import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { ToastServiceService } from 'app/services/toast-service.service';
import { fuseAnimations } from '@fuse/animations';
import { BusinessServiceService } from '../business-service.service';
@Component({
  selector: 'app-business-list',
  templateUrl: './business-list.component.html',
  styleUrls: ['./business-list.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: fuseAnimations
})
export class BusinessListComponent implements OnInit {

  businesses: any[] = []
  packeges: any[] = []
  constructor(private toast: ToastServiceService, private changeDetect: ChangeDetectorRef,
     private businessService: BusinessServiceService) {
  }

  business_id: number
  ngOnInit(): void {

    this.getAll();
    this.getAllPackege()
    this.changeDetect.markForCheck()
  }

  getAll() {
    this.businessService.getAllBusinesses().subscribe(({ success, data }) => {
      if (success) {
        this.businesses = data;
      }
      this.changeDetect.markForCheck()
    })
  }


  deleteBusiness(type, id) {
    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure to perform this action?');
    // Choose layout color type
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.DANGER, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'center', // optional 
    });
    newConfirmBox.setButtons([
      new ButtonMaker('Confirm', 'Confirm', ButtonLayoutDisplay.SUCCESS),
      new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER),
    ]);
    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.clickedButtonID == 'Confirm') {
        if (type == 'packege') {
          this.businessService.delPackege(id).subscribe(res => {
            if (res.success) {
              this.getAllPackege()
              this.toast.toastNotification(res.message, 'Packege Plans!')
            }
            else {
              this.toast.toastNotification1(res.message, 'Packege Plans!')

            }
          })
        }
        else {
          this.businessService.delBusiness(id).subscribe(res => {
            if (res.success) {
              this.getAll();
              let status = 'Delete Business setting Operation!'
              this.toast.toastNotification1(res.message, status)
            }
          })
        }
      }
    });
  }

  getAllPackege() {
    this.businessService.getAllPackeges().subscribe(({ data, success }) => {
      if (success) {
        this.packeges = data
      }
      this.changeDetect.markForCheck()
    })
  }

}

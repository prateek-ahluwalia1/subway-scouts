import { Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { DialogBelonging } from '@costlydeveloper/ngx-awesome-popup';
import { Subscription } from 'rxjs';
import { ToastServiceService } from 'app/services/toast-service.service';
import { PortalSettingsService } from '../../portal-settings.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

@Component({
  selector: 'app-demo',
  templateUrl: './demo.component.html',
  styleUrls: ['./demo.component.scss']
})
export class DemoComponent implements OnInit, OnDestroy {

  data: any = {
    holiday_name: '',
    date: '',
    holiday_information: '',
    state: null
  };

  states = [
    { name: 'Victoria', value: 'vic' },
    { name: 'New South Wales', value: 'nsw' },
    { name: 'Tasmania', value: 'tas' },
    { name: 'Queensland', value: 'qld' },
    { name: 'Western Australia', value: 'wa' },
    { name: 'South Australia', value: 'sa' },
    { name: 'ACT', value: 'act' }];

  private subscriptions: Subscription = new Subscription();
  saveDisabled: boolean = true;

  constructor(@Inject('dialogBelonging') public dialogBelonging: DialogBelonging, private service: PortalSettingsService,
    private toast: ToastServiceService, private trackAdmin: TrackAdminActivityService,) {
  }

  ngOnInit(): void {
    if (this.dialogBelonging.customData.type && this.dialogBelonging.customData.type == "update") {
      let data = this.dialogBelonging.customData.data._def
      this.data.holiday_name = data.title,
        this.data.holiday_information = data.extendedProps.info,
        this.data.state = data.extendedProps.state,
        this.data.id = data.publicId,
        this.data.date = this.dialogBelonging.customData.data.start.toISOString()
    }
    else {
      this.data.date = this.dialogBelonging.customData.date
    }
    this.subscriptions.add(
      this.dialogBelonging.eventsController.onButtonClick$.subscribe((_Button) => {
        if (_Button.ID === 'close') {
          this.dialogBelonging.eventsController.close();
        } else if (_Button.ID === 'save') {
          if (this.isValidForm()) {
            this.service.addPh(this.data).subscribe(({ success, message }) => {
              if (success) {
                this.toast.toastNotification(message, 'Public Holiday!');
                this.dialogBelonging.eventsController.close();
                this.trackAdmin.storeActivity('Company Profile', `Add a holiday with Name: ${this.data.name} and Data: ${this.data.date} information`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
                })
              }
              else {
                this.toast.toastNotification1(message, 'Public Holiday!');
              }
            });
          } else {
            this.toast.toastNotification1('Please fill in all required fields.', 'Invalid Form!');
          }
        }
        else if (_Button.ID === 'update') {
          if (this.isValidForm()) {
            this.service.updatePH(this.data).subscribe(({ success, message }) => {
              if (success) {
                this.toast.toastNotification(message, 'Public Holiday!');
                this.dialogBelonging.eventsController.close();
                this.trackAdmin.storeActivity('Company Profile', `Update a holiday Name: ${this.data.name} and Data: ${this.data.date} with Name: ${this.dialogBelonging.customData.data._def.title} and Data: ${this.dialogBelonging.customData.data._def.start}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
                })
              }
              else {
                this.toast.toastNotification1(message, 'Public Holiday!');
              }
            });
          } else {
            this.toast.toastNotification1('Please fill in all required fields.', 'Invalid Form!');
          }
        }
        else if (_Button.ID === 'delete') {
          if (this.dialogBelonging.customData.data._def.publicId) {
            this.service.deletePH(this.data).subscribe(({ success, message }) => {
              if (success) {
                this.toast.toastNotification1(message, 'Public Holiday!');
                this.dialogBelonging.eventsController.close();
              }
              else {
                this.toast.toastNotification1(message, 'Public Holiday!');

              }
            });
          } else {
            this.toast.toastNotification1('Please fill in all required fields.', 'Invalid Form!');
          }
        }

      })
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  isValidForm(): boolean {
    return !!this.data.holiday_name && !!this.data.date && !!this.data.state && !!this.data.holiday_information;
  }

  onFormChange(): void {
    this.saveDisabled = !this.isValidForm();
  }
}

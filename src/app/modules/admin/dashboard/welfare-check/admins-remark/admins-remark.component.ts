import { ChangeDetectorRef, Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { DialogBelonging } from '@costlydeveloper/ngx-awesome-popup';
import { ServiceService } from 'app/services/service.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { Subject, Subscription } from 'rxjs';

@Component({
  selector: 'app-admins-remark',
  templateUrl: './admins-remark.component.html',
})

export class AdminsRemarkComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription = new Subscription();
  AddAdminRemark: FormGroup;
  liveWelfareCallData: any[] = [];
  modalData
  adminNotes: any = [];

  constructor(@Inject('dialogBelonging') public dialogBelonging: DialogBelonging, private fb: FormBuilder,
  private global: GlobalVariable, private service: ServiceService, private toast: ToastServiceService,) { }

  ngOnInit(): void {

    this.modalData = this.dialogBelonging.customData.data;
    this.AddAdminRemark = this.fb.group({
      note: new FormControl('', [Validators.required]),
      id: this.modalData.id,
      type: this.modalData.type,
      admin_id: this.global.admin.admin_id,
      admin_name: this.global.admin.admin_name
    })

    this.subscriptions.add(
      this.dialogBelonging.eventsController.onButtonClick$.subscribe((_Button) => {
        if (_Button.ID === 'close') {
          this.dialogBelonging.eventsController.close();
        }
        if (_Button.ID === 'save' && this.AddAdminRemark.valid) {
          let status = 'Admin Remark Operation!'
          this.service.saveRemarks(this.AddAdminRemark.value).subscribe(({ msg, success }) => {
            if (success) {
              this.toast.toastNotification(msg, status)
              this.getGraphData();
              // this.dialogBelonging.eventsController.close();
            }
            else {
              this.toast.toastNotification1(msg, status)
            }
          }, (() => {
            this.toast.toastNotification1(this.global.apiError, status)
          }))
        }
        else {
          this.AddAdminRemark.markAllAsTouched()
        }
      })
    );
    this.getGraphData();
  }

  getGraphData() {
    let submitdata = {
      type: this.modalData.type,
      id: this.modalData.id,
    }
    let status = 'Something went wrong!'
    this.service.getRemarks(submitdata).subscribe(({ msg, success, data }) => {
      if (success) {
        this.adminNotes = data;
      }
      else {
        this.toast.toastNotification1(msg, status)
      }
    }, (() => {
      this.toast.toastNotification1(this.global.apiError, status)
    }))
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

}

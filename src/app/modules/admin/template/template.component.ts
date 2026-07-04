import { Component, OnInit, OnDestroy } from '@angular/core';
import { NgbModalConfig, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation, AppearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';

import { ToastServiceService } from 'app/services/toast-service.service';
import { SmsServicesService } from 'app/services/sms-services.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';

@Component({
  selector: 'app-template',
  templateUrl: './template.component.html',
  styleUrls: ['./template.component.scss'],
  providers: [NgbModalConfig, NgbModal],
})
export class TemplateComponent implements OnInit, OnDestroy {
  myForm: FormGroup;
  tatle = "Add New Template";
  isSubmitted = false;
  modal: any;
  filteredItems: any[] = [];
  data: any;
  searchTerm: string = '';
  templetes: any[] = [];
  updateEnable = false;
  templeteid: any;
  routeId: any;
  smstempPermissions: any;
  index: number;

  constructor(
    config: NgbModalConfig,
    private modalService: NgbModal,
    private formBuilder: FormBuilder,
    private smsService: SmsServicesService,
    private toastService: ToastServiceService,
    private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService
  ) {
    const per = this.permissionService.getPermissionsByTitle('Communications');
    this.smstempPermissions = per?.childPage?.find((item) => item.title === 'Template');
    config.backdrop = 'static';
    config.keyboard = false;

    let routerId = localStorage.getItem('routerId');
    if (!routerId) {
      this.activity();
    }
  }

  ngOnInit(): void {
    this.myForm = this.formBuilder.group({
      title: ["", Validators.required],
      msg: ["", Validators.required],
    });
    this.getTempletes();
  }

  ngOnDestroy() {
    // localStorage.removeItem('routerId');
    // this.trackAdmin.storeActivity('Exit SMS Template Page', 'Exit SMS Template Page', this.routeId).subscribe(res => {
    // })
  }

  activity() {
    this.trackAdmin.storeActivity('SMS Template Page', 'Enter in SMS Template Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id);
        this.routeId = id;
      }
    });
  }

  getTempletes() {
    this.smsService.getAllTempletes().subscribe((res) => {
      this.templetes = res.templates;
      this.filteredItems = this.templetes; // Initialize filteredItems with all templates
    });
  }

  search() {
    this.filteredItems = this.templetes.filter(item => 
      item.title.toLowerCase().includes(this.searchTerm.toLowerCase())
    );
  }

  openVerticallyCentered(type: string, content: any, data: any = null, i: number = 0) {
    this.templeteid = data?.id || '';
    this.updateEnable = type === 'update';
    if (data) {
      this.myForm.setValue({ title: data.title, msg: data.msg_body });
      this.tatle = "Edit this Template";
      this.data = data;
      this.index = i;
    }
    this.modal = this.modalService.open(content, { centered: true });
  }

  onSubmit(type: string) {
    if (this.myForm.valid) {
      const { title, msg } = this.myForm.value;
      if (!this.updateEnable) {
        this.smsService.addTemplete(title, msg).pipe(
          catchError((error) => {
            this.toastService.toastNotification1('Something went wrong. Please contact the support team', 'Request Incomplete!');
            return throwError(error);
          })
        ).subscribe((res) => this.handleTemplateResponse(res, 'SMS Operation'));

      } else {
        this.smsService.updateTemplete(title, msg, this.templeteid).pipe(
          catchError((error) => {
            this.toastService.toastNotification1('Something went wrong. Please contact the support team', 'Request Incomplete!');
            return throwError(error);
          })
        ).subscribe((res) => this.handleTemplateResponse(res, 'SMS Operation'));
      }

    } else {
      this.isSubmitted = true;
    }
  }

  handleTemplateResponse(res: any, status: string) {
    if (res.success) {
      this.getTempletes();
      this.modal.close();
      this.toastService.toastNotification(res.message, status);
      this.myForm.reset();
    } else {
      this.toastService.toastNotification1(res.message, status);
    }
  }

  get formControl() {
    return this.myForm.controls;
  }

  deleted(data: any) {
    this.openConfirmBox(data.id);
  }

  openConfirmBox(id: any) {
    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure you want to confirm this action?');
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.SUCCESS,
      animationIn: AppearanceAnimation.SWING,
      animationOut: DisappearanceAnimation.BOUNCE_OUT,
      allowHtmlMessage: true,
      buttonPosition: 'right',
    });
    newConfirmBox.setButtonLabels('Confirm', 'Decline');
    newConfirmBox.openConfirmBox$().subscribe((resp) => {
      if (resp.success) {
        this.smsService.deleteTemplete(id).pipe(
          catchError((error) => {
            console.error('Error while deleting template:', error);
            return throwError(error);
          })
        ).subscribe((res) => this.handleDeleteResponse(res, 'Sms Operation'));
      }
    });
  }

  handleDeleteResponse(res: any, status: string) {
    if (res.success) {
      this.getTempletes();
      this.isSubmitted = false;
      this.toastService.toastNotification(res.message, status);
    } else {
      this.toastService.toastNotification1(res.message, 'Request Incomplete');
    }
  }
}

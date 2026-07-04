import { HttpClient, HttpHeaders } from '@angular/common/http';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { PermissionsService } from 'app/services/permissions.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-api-setting',
  templateUrl: './api-setting.component.html',
  styleUrls: ['../company-profile/company-profile.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})

export class ApiSettingComponent implements OnInit, OnDestroy {
  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  adminPermissions: any
  formData: any = {};

  constructor(private toast: ToastServiceService, private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService, private changeDetect: ChangeDetectorRef,
    private global: GlobalVariable, private http: HttpClient) { }

  ngOnInit(): void {
    const per = this.permissionService.getPermissionsByTitle('Settings');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Api Settings');
    this.getApiKeys()
    if (!localStorage.getItem('routerId')) {
      this.activity()
    }
  }

  submitForm() {
    this.http.post(this.global.baseUrl + 'save-third-party-apis', this.formData, this.httpOptions).subscribe((res: any) => {
      if (res.success) {
        this.toast.toastNotification(res.message, 'API Settings!')
        this.getApiKeys()
        this.trackAdmin.storeActivity('Company Profile', `${this.formData.id ? 'Update' : 'Added'} information`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
        })
        this.changeDetect.markForCheck()
      }

    });

  }

  getApiKeys() {
    this.http.get(this.global.baseUrl + 'get-third-party-apis', this.httpOptions).subscribe((res: any) => {
      this.formData = res.data
      this.changeDetect.markForCheck()

    });
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity('Api Settings', 'Api Settings Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Api Settings', `Enter in Api Settings`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }

}

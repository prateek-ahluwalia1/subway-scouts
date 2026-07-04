import { HttpClient, HttpHeaders } from '@angular/common/http';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { PermissionsService } from 'app/services/permissions.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-company-profile',
  templateUrl: './company-profile.component.html',
  styleUrls: ['./company-profile.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush

})

export class CompanyProfileComponent implements OnInit, OnDestroy {

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  adminPermissions: any
  formData: any = {};

  constructor(private toast: ToastServiceService,
    private permissionService: PermissionsService, private changeDetect: ChangeDetectorRef,
    private http: HttpClient, private global: GlobalVariable, private trackAdmin: TrackAdminActivityService,) {
    this.getApiKeys()
  }

  
  ngOnInit(): void {
    const per = this.permissionService.getPermissionsByTitle('Settings');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Company Profile');
    if (!localStorage.getItem('routerId')) {
      this.activity()
    }
    this.changeDetect.markForCheck()
  }


  submitForm() {
    const regex = /^[0-9]{6}$/;
    if (!regex.test(this.formData.bsb)) {
      this.toast.toastNotification1('Please enter a valid 6-digit BSB number.', 'Company Profile!');
      return;
    }
    this.http.post(this.global.baseUrl + 'save-third-party-apis', this.formData, this.httpOptions).subscribe((res: any) => {
      if (res.success) {
        this.toast.toastNotification(res.message, 'Company Profile!')
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
    this.trackAdmin.storeActivity('Company Profile', 'Exite Company Profile Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Company Profile', `Enter in Company Profile`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }
}

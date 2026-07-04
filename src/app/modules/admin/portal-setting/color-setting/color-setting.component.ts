import { HttpHeaders } from '@angular/common/http';
import { ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Router } from '@angular/router';
import { ColorSettingsService } from 'app/services/color-settings.service';
import { PermissionsService } from 'app/services/permissions.service';
import { PortalSettingService } from 'app/services/portal-setting.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

@Component({
  selector: 'app-color-setting',
  templateUrl: './color-setting.component.html',
  styleUrls: ['./color-setting.component.scss'],
})

export class ColorSettingComponent implements OnInit, OnDestroy {

  // primaryColor: string = '#000000';
  // secondaryColor: string = '#7B68EE';
  // lightColor: string = '#D8BFD8';
  pendingshiftsColor: string = '#E5E0EB';
  unpublishshiftsColor: string = '#33CCCC';
  rejectedshiftsColor: string = '#FF99FF';
  publishshiftsColor: string = '#DDD9C3';
  mockshiftsColor: string = '#66FFFF';
  missedshiftsColor: string = '#FF7377';
  uncoveredshiftsColor: string = '#FFFF00';
  operationalshiftColor: string = '#D2B48C';
  unpublishedsiteColor: string = '#191970';
  completedShifts: string = '#191970';
  confirmedShifts: string = '#191970';
  id;
  type;
  data;
  adminPermissions: any;
  public file;
  fileUrl

  constructor(private _router: Router, private colorService: ColorSettingsService, private toast: ToastServiceService,
    private setting: PortalSettingService, private trackAdmin: TrackAdminActivityService, private cdr: ChangeDetectorRef,
    private permissionService: PermissionsService, private _snackBar: MatSnackBar, private staffDoc: StaffService,) 
    {
    this.setting.portalSetting().subscribe(({ data, success }) => {
      if (success) {
        if (data) {
          const color = JSON.parse(data.settings)
          // this.primaryColor = color.primary_color ? color.primary_color : '#01ACED'
          // this.secondaryColor = color.secondary_color ? color.secondary_color : '#1e293c'
          // this.lightColor = color.background_color ? color.background_color : '#01a37e2e'
          this.pendingshiftsColor = color.pending_shifts ? color.pending_shifts : '#E5E0EB'
          this.unpublishshiftsColor = color.unpublish_shifts ? color.unpublish_shifts : '#33CCCC'
          this.rejectedshiftsColor = color.rejected_shifts ? color.rejected_shifts : '#FF99FF'
          this.publishshiftsColor = color.publish_shifts ? color.publish_shifts : '#DDD9C3'
          this.mockshiftsColor = color.mock_shifts ? color.mock_shifts : '#66FFFF'
          this.missedshiftsColor = color.missed_shifts ? color.missed_shifts : '#FF7377'
          this.uncoveredshiftsColor = color.uncoverd_shifts ? color.uncoverd_shifts : '#FFFF00'
          this.operationalshiftColor = color.operational_notes_shifts ? color.operational_notes_shifts : '#D2B48C'
          this.unpublishedsiteColor = color.unpublish_site_shifts ? color.unpublish_site_shifts : '#191970'
          this.unpublishedsiteColor = color.unpublish_site_shifts ? color.unpublish_site_shifts : '#191970'
          this.confirmedShifts = color.confirmed_shift ? color.confirmed_shift : '#191970'
          this.completedShifts = color.completed_shift ? color.completed_shift : '#66FFFF'
          this.cdr.detectChanges();
        }
      }
    })

    if (!localStorage.getItem('routerId')) {
      this.activity()
    }
  }

  ngOnInit(): void {
    const per = this.permissionService.getPermissionsByTitle('Settings');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Color Settings');
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity('Exit Color Settings Page', 'Exit Color Settings Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Color Settings Page', 'Enter in Color Settings Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }
  
  save() {
    let params = {
      // background_color: this.lightColor,
      pending_shifts: this.pendingshiftsColor,
      unpublish_shifts: this.unpublishshiftsColor,
      // primary_color: this.primaryColor,
      rejected_shifts: this.rejectedshiftsColor,
      // secondary_color: this.secondaryColor,
      publish_shifts: this.publishshiftsColor,
      mock_shifts: this.mockshiftsColor,
      missed_shifts: this.missedshiftsColor,
      uncoverd_shifts: this.uncoveredshiftsColor,
      operational_notes_shifts: this.operationalshiftColor,
      unpublish_site_shifts: this.unpublishedsiteColor,
      completed_shift: this.completedShifts,
      confirmed_shift: this.confirmedShifts,
      logo: this.fileUrl

    } as {
      // background_color: string, 
      pending_shifts: string, unpublish_shifts: string, 
      // primary_color: string,
      completed_shift: string, confirmed_shift: string, rejected_shifts: string, 
      // secondary_color: string, 
      publish_shifts: string, mock_shifts: string,
      missed_shifts: string, uncoverd_shifts: string, operational_notes_shifts: string, unpublish_site_shifts: string,
      id?: string, logo: string
    };

    console.log("Submit colors", params)

    this.colorService.saveColor(params).subscribe(({ success, message }) => {
      if (success) {
        this._router.navigate(['/sign-out']);
        const status = 'Save Color Operation!';
        this.trackAdmin.storeActivity('Company Profile', `Update information`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
        })
        this.toast.toastNotification('You are going to logout for Applying Settings on Portal', status);
      }
    });
  }


  onFileChange(fileList: FileList) {
    const file = fileList[0];
    if (!file.type.startsWith('image/')) {
      this._snackBar.open("Please upload only image files!", 'Close', {
        duration: 2000,
      });
      return;
    }

    this.file = file;
    this.previewFile(file);
    this.uploadImageFile(file);
  }

  previewFile(file: File) {
    const reader = new FileReader();
    reader.onload = () => {
      file['preview'] = reader.result as string;
    };
    reader.readAsDataURL(file);
  }

  uploadImageFile(file: File) {
    const myFormData = new FormData();
    const headers = new HttpHeaders({
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    })
    myFormData.append('file', file, file.name);
    myFormData.append('folder', 'company_documents')
    this.staffDoc.uploadImgPdf(myFormData, {
      headers: headers
    }).subscribe(
      response => {
        if (response.success) {
          this.fileUrl = response.url
        }
        else {
          this.toast.toastNotification1(response.message, 'Request Incomplete!')
        }
      },
      (error) => {
        this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Request Incomplete!')
      }
    );

    this._snackBar.open("Successfully uploaded image!", 'Close', {
      duration: 2000,
    });
  }

}

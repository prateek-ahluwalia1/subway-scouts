import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { compactNavigation } from 'app/mock-api/common/navigation/data';
import { PermissionsService } from 'app/services/permissions.service';
import { RolePermissionService } from 'app/services/role-permission.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-role-and-permission',
  templateUrl: './role-and-permission.component.html',
  styleUrls: ['./role-and-permission.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush

})
export class RoleAndPermissionComponent implements OnInit, OnDestroy {

  panelOpenState = false;
  AllUsers: any = []
  users: any[] = []
  role
  id
  navigationItems = compactNavigation.map((item) => ({
    title: item.title,
    url: item.link,
    actions: item.action?.map((actionTitle) => ({
      title: actionTitle,
      enabled: false,
    })) || [],
    parentEnabled: false,
    childPage: item.childPage?.map((childPageTitle) => ({
      title: childPageTitle,
      enabled: false,
      create: false,
      read: false,
      update: false,
      delete: false,
      // Customize properties for "Roster/Scheduling" child pages
      ...(childPageTitle === 'Staff Roster/Scheduling' && {
        viewByGuards: false,
        viewBySite: false,
        twoWeeks: false,
        rosterOptions: false,
        addStaffToLocations: false,
        createNewLocation: false,
        bulkActions: false,
        asapJob: false,
        availableStaff: false,
        createShift: false,
        readShift: false,
        updateShift: false,
        deleteShift: false,
        deleteLocation: false,
        updateLocation: false,
        max14Hours: false,
        onGoingCompletedUpdate: false,
        eightHourBreak: false,
        lockPreweek: false,
        reportEditDelete: false,
        mockUnprofiledStaff: false,
      }),
      ...(childPageTitle === 'Leads' && {
        viewOnlyHisLeave: false,
      }),
    })) || [],
  }));
  adminPermissions: any;


  constructor(
    private modalService: NgbModal,
    private toast: ToastServiceService, private cdr: ChangeDetectorRef,
    private service: RolePermissionService, private permissionService: PermissionsService,
    private spinner: NgxSpinnerService, private trackAdmin: TrackAdminActivityService,
  ) { }

  ngOnInit(): void {
    const per = this.permissionService.getPermissionsByTitle('Settings');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Roles & Permissions');

    this.getAllPermissions()
    if (!localStorage.getItem('routerId')) {
      this.activity()
    }
    this.cdr.detectChanges();

  }

  openLg(longContent) {
    const modalRef = this.modalService.open(longContent, { size: 'xl', backdrop: 'static' });
    modalRef.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);
        this.reset()
      },
      (reason) => {
        this.reset()
      }
    );
  }

  reset() {
    this.navigationItems = compactNavigation.map((item) => ({
      title: item.title,
      url: item.link,
      actions: item.action?.map((actionTitle) => ({
        title: actionTitle,
        enabled: false,
      })) || [],
      parentEnabled: false,
      childPage: item.childPage?.map((childPageTitle) => ({
        title: childPageTitle,
        enabled: false,
        create: false,
        read: false,
        update: false,
        delete: false,
        // Customize properties for "Roster/Scheduling" child pages
        ...(childPageTitle === 'Staff Roster/Scheduling' && {
          viewByGuards: false,
          viewBySite: false,
          twoWeeks: false,
          rosterOptions: false,
          addStaffToLocations: false,
          createNewLocation: false,
          bulkActions: false,
          asapJob: false,
          availableStaff: false,
          createShift: false,
          readShift: false,
          updateShift: false,
          deleteShift: false,
          deleteLocation: false,
          updateLocation: false,
          max14Hours: false,
          onGoingCompletedUpdate: false,
          eightHourBreak: false,
          lockPreweek: false,
          reportEditDelete: false,
          mockUnprofiledStaff: false,
        }),
        ...(childPageTitle === 'Leads' && {
          viewOnlyHisLeave: false,
        }),
      })) || [],
    }));
    this.role = ''
    this.cdr.markForCheck()

  }

  // ... (previous code) ...

  toggleChildCheckboxes(page) {
    if (page.childPage && page.childPage.length > 0) {
      page.childPage.forEach((subpage) => {
        subpage.enabled = page.parentEnabled;
        subpage.create = page.parentEnabled;
        subpage.read = page.parentEnabled;
        subpage.update = page.parentEnabled;
        subpage.delete = page.parentEnabled;
      });
    }
  }

  toggleAllChildCheckboxes(subpage, title) {
    if (title == 'Staff Roster/Scheduling') {
      if (!subpage.enabled) {
        subpage.create = true;
        subpage.read = true;
        subpage.update = true;
        subpage.delete = true;
        subpage.createShift = true;
        subpage.readShift = true;
        subpage.updateShift = true;
        subpage.deleteShift = true;
        subpage.viewByGuards = true;
        subpage.viewBySite = true;
        subpage.twoWeeks = true;
        subpage.rosterOptions = true;
        subpage.addStaffToLocations = true;
        subpage.createNewLocation = true;
        subpage.bulkActions = true;
        subpage.asapJob = true;
        subpage.availableStaff = true;
        subpage.deleteLocation = true;
        subpage.updateLocation = true;
        subpage.max14Hours = true;
        subpage.onGoingCompletedUpdate = true;
        subpage.eightHourBreak = true;
        subpage.lockPreweek = true;
        subpage.reportEditDelete = true;
        subpage.mockUnprofiledStaff = true;
      } else {
        subpage.createShift = false;
        subpage.readShift = false;
        subpage.updateShift = false;
        subpage.deleteShift = false;
        subpage.viewByGuards = false;
        subpage.viewBySite = false;
        subpage.twoWeeks = false;
        subpage.rosterOptions = false;
        subpage.addStaffToLocations = false;
        subpage.createNewLocation = false;
        subpage.bulkActions = false;
        subpage.asapJob = false;
        subpage.availableStaff = false;
        subpage.create = false;
        subpage.read = false;
        subpage.update = false;
        subpage.delete = false;
        subpage.deleteLocation = false;
        subpage.updateLocation = false;
        subpage.max14Hours = false;
        subpage.onGoingCompletedUpdate = false;
        subpage.eightHourBreak = false;
        subpage.lockPreweek = false;
        subpage.reportEditDelete = false;
        subpage.mockUnprofiledStaff = false;
      }
    }
    else {
      if (!subpage.enabled) {
        subpage.create = true;
        subpage.read = true;
        subpage.update = true;
        subpage.delete = true;
      } else {
        subpage.create = false;
        subpage.read = false;
        subpage.update = false;
        subpage.delete = false;
      }
    }
  }

  checkUncheck(subpage) {
    // console.log(subpage);
    // if (!subpage.create || !subpage.delete || !subpage.update || !subpage.write) {
    //   subpage.enabled = true
    // }
    // else {
    //   subpage.enabled = false
    // }
  }

  onSubmit() {
    if (!this.role) {
      this.toast.toastNotification1('Please Enter Role', 'Invaid Form!')
      return
    }
    this.spinner.show()
    let data = {
      id: this.id || '',
      role: this.role,
      permissions: this.navigationItems
    }

    this.service.saveRolePermission(data).subscribe(({ message, success }) => {
      if (success) {
        this.modalService.dismissAll()
        this.toast.toastNotification(message, 'Role and Permission!')
        this.getAllPermissions()
        this.trackAdmin.storeActivity('Role & Permissions', `${this.id ? 'Update' : 'Added'} role with Name: ${data.role}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
        })
      }
      else {
        this.toast.toastNotification(message, 'Role and Permission!')
      }
      this.spinner.hide()
    }, (error => {
      this.toast.toastNotification('Something went wrong. Please contact with support team.', 'Role and Permission!')
      this.spinner.hide()

    }))
  }

  editRole(content, id?) {
    let data = {
      id: id
    }
    this.service.getRolesPermissions(data).subscribe(({ data, success }) => {
      if (success) {
        console.log(data.permissions);
        console.log(this.navigationItems);
        this.navigationItems.forEach(parentKey => {
          const matchingPermission = data.permissions.find(resKey => resKey.title === parentKey.title);

          if (matchingPermission) {
            parentKey.parentEnabled = matchingPermission.parentEnabled;

            if (matchingPermission.childPage) {
              parentKey.childPage?.forEach(childKey => {
                const matchingChildPermission = matchingPermission.childPage.find(resChildKey => resChildKey.title === childKey.title);
                if (matchingChildPermission) {
                  for (const prop in childKey) {
                    if (prop in matchingChildPermission) {
                      childKey[prop] = matchingChildPermission[prop];
                    }
                  }
                }
              });
            }
          }
        });

        this.id = data.id
        this.role = data.role
        // this.navigationItems = data.permissions
        const modalRef = this.modalService.open(content, { size: 'xl', backdrop: 'static' });
        modalRef.result.then((result) => {
          this.reset()
        }).catch((reason) => {
          this.reset()
        });
      }
      else {
        this.toast.toastNotification1('Something went wrong', 'Request Incomplete!')
      }
    }, (error => {
      this.toast.toastNotification1('Something went wrong', 'Request Incomplete!')
    }))

  }

  getAllPermissions() {
    this.service.getAllRoleAndPermission().subscribe(({ data, success }) => {
      if (success) {
        this.users = data
      }
      this.cdr.markForCheck()
    })
  }

  delRole(obj) {
    if (obj?.id) {
      const newConfirmBox = new ConfirmBoxInitializer();
      newConfirmBox.setTitle('Confirm Action');
      newConfirmBox.setMessage('Are you sure you want to confirm this action?');
      // Choose layout color type
      newConfirmBox.setConfig({
        layoutType: DialogLayoutDisplay.DANGER, // SUCCESS | INFO | NONE | DANGER | WARNING
        animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
        animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
        allowHtmlMessage: true,
        buttonPosition: 'right', // optional 
      });

      newConfirmBox.setButtonLabels('Confirm', 'Decline');

      // Simply open the popup and observe button click
      newConfirmBox.openConfirmBox$().subscribe(resp => {
        if (resp.success) {
          this.service.delRoleAndPermission(obj.id).subscribe(({ success, msg }) => {
            if (success) {
              let status = 'Role and Permission!'
              this.toast.toastNotification1(msg, status)
              this.getAllPermissions()
              this.trackAdmin.storeActivity('Role & Permissions', `Delete a role of name ${obj.role}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
              })
            }
            else {
              this.toast.toastNotification1(msg, 'Delete Failed!')
            }
          }, (error => {
            this.toast.toastNotification1('Something went wrong', 'Delete Failed!')
          }));
        }
      });
    }
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity('Role & Permissions', 'Exite Role & Permissions Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Role & Permissions', `Enter in Role & Permissions`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }
}

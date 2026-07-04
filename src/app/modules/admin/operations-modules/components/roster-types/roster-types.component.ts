import { Component, OnInit } from '@angular/core';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastServiceService } from 'app/services/toast-service.service';
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { GlobalVariable } from 'app/shared/global';
import { trigger, transition, style, animate, query, stagger } from '@angular/animations';
import { Router } from '@angular/router';
import { Title } from '@angular/platform-browser';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { CreateRosterComponent } from 'app/modules/admin/models/create-roster/create-roster.component';

@Component({
  selector: 'app-roster-types',
  templateUrl: './roster-types.component.html',
  styleUrls: ['./roster-types.component.scss'],
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('200ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('200ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ]
})
export class RosterTypesComponent implements OnInit {
  activeTab = 'Active'
  rosterList: any = []
  tabs = [
    { id: 1, icon: 'check_circle_outline', name: 'Active' },
    { id: 2, icon: 'visibility_off', name: 'Inactive' },
    { id: 3, icon: 'archive', name: 'Archive' },

  ]
  rosterCreateModal: any;
  id;
  // type;
  data;
  routeId
  states: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia'];
  selectedState = '';
  rosterPermissions: any;
  constructor(private modalService: NgbModal, public router: Router, private titleService: Title,
    private service: JobRoster1Service, private toast: ToastServiceService, private global: GlobalVariable,
    private trackAdmin: TrackAdminActivityService, private permissionService: PermissionsService) {
    const activatedUrl = this.router.url;
    this.global.ActivateUrl = activatedUrl
    this.titleService.setTitle('Staff Roster/Scheduling | The scouts');

    this.activity()
    this.id = this.global.admin.admin_id
    // this.type = this.global.admin.admin_user_type

  }


  ngOnInit(): void {
    this.getRoster()
    const per = this.permissionService.getPermissionsByTitle('WFM Tools');
    this.rosterPermissions = per?.childPage?.find(item => item.title === 'Staff Roster/Scheduling');
    console.log(this.rosterPermissions);
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');
    this.trackAdmin.storeActivity('Exit Staff Roster/Scheduling', 'Exit Staff Roster/Scheduling Page', this.routeId).subscribe(res => {
    })
  }

  getTab(tab) {
    this.rosterList = []
    this.activeTab = tab.name;
    this.getRoster()
  }

  // open Roster Creation Model

  openRosterCreationModel() {
    this.rosterCreateModal = this.modalService.open(CreateRosterComponent, {
      windowClass:
        "createRosterModalClass",
      size: 'lg'
    });
    // this.rosterCreateModal.componentInstance.action = JSON.stringify(action);
    this.rosterCreateModal.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        if (reason) {
          if (reason == true) {
            this.trackAdmin.storeActivity('Staff Roster/Scheduling', `Created a new roster`, this.routeId).subscribe(res => {
            })
          }
          this.getRoster()
          this.global.selectedCustomers = []

        }
      }
    );

  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return "by pressing ESC";
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return "by clicking on a backdrop";
    } else {
      return `with: ${reason}`;
    }
  }


  getRoster() {
    this.service.getNewRoster(this.activeTab.toLowerCase(), this.selectedState).subscribe(({ success, data }) => {
      if (success) {
        this.rosterList = data;
      }
    })
  }


  rosterData
  changeStatus(type, report) {
    if (type == 'edit') {
      this.service.editRoster(report.id).subscribe(({ success, data }) => {
        if (success) {
          this.rosterData = data;
          this.rosterCreateModal = this.modalService.open(CreateRosterComponent, {
            windowClass:
              "createRosterModalClass",
            size: 'lg'
          });
          this.rosterCreateModal.componentInstance.rosterId = this.rosterData;
          this.rosterCreateModal.result.then(
            (result) => {
              console.log("Modal Result", `Closed with: ${result}`);
            },
            (reason) => {
              if (reason == true) {
                this.trackAdmin.storeActivity('Staff Roster/Scheduling', `Update Roster ${report.roster_name}`, this.routeId).subscribe(res => {
                })
                this.getRoster()
              }
            }
          );
        }
        else {
          this.toast.toastNotification1('Something went wrong', 'Roster Operation!')
        }
      });
    }
    else if (type == 'deleted') {
      let params = {
        status: type,
        id: report.id,
        admin_id: this.global.admin.admin_id
      }
      this.service.changeStatus(params).subscribe(({ success, msg }) => {
        if (success) {
          this.toast.toastNotification(msg, 'Roster Operation!')
          this.getRoster()
          this.trackAdmin.storeActivity('Staff Roster/Scheduling', `Delete Roster ${report.roster_name}`, this.routeId).subscribe(res => {
          })
        }
        else {
          const newConfirmBox = new ConfirmBoxInitializer();
          newConfirmBox.setTitle('Confirm Action');
          newConfirmBox.setMessage(msg);

          newConfirmBox.setConfig({
            layoutType: DialogLayoutDisplay.DANGER,
            animationIn: AppearanceAnimation.ZOOM_IN,
            animationOut: DisappearanceAnimation.ZOOM_OUT_ROTATE,
            allowHtmlMessage: true,
            buttonPosition: 'right',
          });

          newConfirmBox.setButtonLabels('Confirm', 'Decline');

          // Simply open the popup and observe button click
          newConfirmBox.openConfirmBox$().subscribe(resp => {
            if (resp.success) {
              let params = {
                status: type,
                id: report.id,
                admin_id: this.global.admin.admin_id,
                confirm: 'yes'
              }
              this.service.changeStatus(params).subscribe(({ success, msg }) => {
                if (success) {
                  this.toast.toastNotification(msg, 'Roster Operation!')
                  this.getRoster()
                  this.trackAdmin.storeActivity('Staff Roster/Scheduling', `Delete Roster ${report.roster_name}`, this.routeId).subscribe(res => {
                  })
                }
              })
            }
          });
        }
      })
    }
    else {
      let params = {
        status: type,
        id: report.id,
        admin_id: this.global.admin.admin_id
      }
      this.service.changeStatus(params).subscribe(({ success, msg }) => {
        if (success) {
          this.trackAdmin.storeActivity('Staff Roster/Scheduling', `Change Roster Status Name: ${report.roster_name}`, this.routeId).subscribe(res => {
          })
          this.toast.toastNotification(msg, 'Roster Operation!')
          this.getRoster()
        }
      })
    }
  }

  accessRoster(roster) {
    this.service.accessRoster(roster.id).subscribe(({ success, roster_name }) => {
      if (success) {
        const slug = this.generateSlug(roster.roster_name);
        if (this.global.admin.admin_user_type === 'super-admin') {
          this.router.navigate(['/operations/job-roster', slug, roster.id]);
        } else {
          const hasAccess = roster.user_id.some(user => user.id === this.global.admin.admin_id);
          if (hasAccess) {
            this.router.navigate(['/operations/job-roster', slug, roster.id]);
          } else {
            this.toast.toastNotification1("You don't have access to this roster", 'Access Denied');
          }
        }
      } else {
        this.toast.toastNotification1(this.global.apiError, 'Access Denied');
      }
    });
  }

  // Helper method to generate a slug
  generateSlug(rosterName: string): string {
    return rosterName.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
  }



  cusName(customerIdArray: { name: string }[]): string {
    if (customerIdArray) {
      const customerNames = customerIdArray.map((customer) => customer.name);
      return customerNames.join(', ');
    }
  }

  activity() {
    this.trackAdmin.storeActivity('Staff Roster/Scheduling', 'Enter in Staff Roster/Scheduling Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }
  filterState(value) {
    this.selectedState = value;
    console.log("Filter State", this.selectedState);
    this.getRoster();
  }

  checkExist(roster) {
    if (this.global.admin.admin_user_type === 'super-admin') {
      return true;
    }

    if (roster && roster.user_id) {
      const hasAccess = roster.user_id.some(user => user.id === this.global.admin.admin_id);
      return hasAccess;
    }

    return false;
  }

}
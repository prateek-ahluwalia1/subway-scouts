import { Component, OnInit } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { RunsheetrosterService } from 'app/services/runsheetroster.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { trigger, transition, style, animate, query, stagger } from '@angular/animations';
import { GlobalVariable } from 'app/shared/global';
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { Router } from '@angular/router';
import { CreateRunsheetRosterComponent } from 'app/modules/admin/models/create-runsheet-roster/create-runsheet-roster.component';

@Component({
  selector: 'app-runsheet-roster',
  templateUrl: './runsheet-roster.component.html',
  styleUrls: ['./runsheet-roster.component.scss'],
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
export class RunsheetRosterComponent implements OnInit {
  activeTab = 'Active';
  runsheetrosterList: any = [];
  rosterCreateModal: any;
  tabs = [
    { id: 1, icon: 'check_circle_outline', name: 'Active' },
    { id: 2, icon: 'visibility_off', name: 'Inactive' },
    { id: 3, icon: 'archive', name: 'Archive' },
  ]

  constructor(private roster: RunsheetrosterService, private modalService: NgbModal, private toast: ToastServiceService,
    private global: GlobalVariable, public router: Router) { }

  ngOnInit(): void {
    this.getRunsheetRoster();
  }

  getTab(tab) {
    this.runsheetrosterList = []
    this.activeTab = tab.name;
    this.getRunsheetRoster();
  }

  getRunsheetRoster() {
    this.roster.getRoster(this.activeTab.toLowerCase()).subscribe(({ success, data }) => {
      if (success) {
        this.runsheetrosterList = data;
      }
    })
  }

  openRosterCreationModel() {
    this.rosterCreateModal = this.modalService.open(CreateRunsheetRosterComponent, {
      windowClass:
        "createRosterModalClass",
      size: 'lg'
    });
    this.rosterCreateModal.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);
        this.getRunsheetRoster();
      },
      (reason) => {
        if (reason) {
          console.log("Modal closed with reason:", reason);
          this.getRunsheetRoster()
        }
      }
    );
  }

  rosterData
  changeStatus(type, roster) {
    if (type == 'edit') {
      this.roster.editRoster(roster.id).subscribe(({ success, data }) => {
        if (success) {
          this.rosterData = data;
          this.rosterCreateModal = this.modalService.open(CreateRunsheetRosterComponent, {
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
              if (reason) {
                this.getRunsheetRoster()
              }
            }
          )
        }
        else {
          this.toast.toastNotification1('Something went wrong', 'Roster Operation!')
        }
      });
    }
    else if (type == 'deleted') {
      let params = {
        status: type,
        id: roster.id,
        admin_id: this.global.admin.admin_id
      }
      this.roster.updateStatus(params).subscribe(({ success, message }) => {
        if (success) {
          this.toast.toastNotification(message, 'Roster Operation!')
          this.getRunsheetRoster();
        }
        else {
          const newConfirmBox = new ConfirmBoxInitializer();
          newConfirmBox.setTitle('Confirm Action');
          newConfirmBox.setMessage(message);

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
                id: roster.id,
                admin_id: this.global.admin.admin_id,
                confirm: 'yes'
              }
              this.roster.updateStatus(params).subscribe(({ success, message }) => {
                if (success) {
                  this.toast.toastNotification(message, 'Roster Operation!')
                  this.getRunsheetRoster()
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
        id: roster.id,
        admin_id: this.global.admin.admin_id
      }
      this.roster.updateStatus(params).subscribe(({ success, message }) => {
        if (success) {
          this.toast.toastNotification(message, 'Roster Operation!')
          this.getRunsheetRoster()
        }
      })
    }
  }

  accessRoster(roster) {
    console.log("print roster", roster)
    this.roster.accessRoster(roster.id).subscribe(({ success }) => {
      if (success) {
        if (this.global.admin.admin_user_type === 'super-admin') {
          this.router.navigate(['/runsheet-job-roster', roster.id]);
        } else {
          const hasAccess = roster.admins.some(user => user.id === this.global.admin.admin_id);
          if (hasAccess) {
            this.router.navigate(['/job-roster', roster.id]);
          } else {
            this.toast.toastNotification1("You don't have access to this roster", 'Access Denied');
          }
        }
      } else {
        this.toast.toastNotification1(this.global.apiError, 'Access Denied');
      }
    });
  }

  checkExist(roster) {
    if (this.global.admin.admin_user_type === 'super-admin') {
      return true;
    }

    if (roster && roster.admins) {
      const hasAccess = roster.admins.some(user => user.id === this.global.admin.admin_id);
      return hasAccess;
    }

    return false;
  }

}

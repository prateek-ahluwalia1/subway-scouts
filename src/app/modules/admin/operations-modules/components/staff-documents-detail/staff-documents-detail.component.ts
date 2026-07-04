import { Router } from '@angular/router';
import { Component, OnInit } from '@angular/core';
import { ToastNotificationInitializer, DialogLayoutDisplay, DisappearanceAnimation, AppearanceAnimation, ToastPositionEnum, } from '@costlydeveloper/ngx-awesome-popup';
import { GlobalVariable } from 'app/shared/global';
import { filter } from 'lodash';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { StaffService } from 'app/services/staff.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GuardLicenseService } from 'app/services/guard-license.service';
import { UserMenuComponent } from 'app/modules/admin/models/user-menu/user-menu.component';

@Component({
  selector: 'app-staff-documents-detail',
  templateUrl: './staff-documents-detail.component.html',
  styleUrls: ['./staff-documents-detail.component.scss']
})

export class StaffDocumentsDetailComponent implements OnInit {

  startPage = 1;
  searchTerm: string;
  documents = [];
  guradList = [];
  filteredGuards: any[] = [];
  id;
  type;
  data;
  routeId

  constructor(private guardLicense: GuardLicenseService, private globals: GlobalVariable, private router: Router,
    private trackAdmin: TrackAdminActivityService, private staffService: StaffService, private modalService: NgbModal,
    private toastService: ToastServiceService) {

    const activatedUrl = this.router.url;
    this.globals.ActivateUrl = activatedUrl
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
    let data = {
      document_type: this.documents,
      status: 'active'
    }
    this.guardLicense.filterGuard(data).subscribe(({ success, data }) => {
      if (success) {
        this.guradList = data;
        this.filteredGuards = data;
      }
    })
  }

  ngOnDestroy() {
    // localStorage.removeItem('routerId');

    // this.trackAdmin.storeActivity('Exit Staff License Details Page', 'Exit Staff License Details Page', this.routeId).subscribe(res => {
    // })
  }

  activity() {
    this.trackAdmin.storeActivity('Staff License Details Page', 'Enter in Staff License Details Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  toggleDocument(document: string) {

    if (this.documents.includes(document)) {
      this.documents.splice(this.documents.indexOf(document), 1);
    } else {
      this.documents.push(document);
    }

    let data = {
      document_type: this.documents
    }

    this.guardLicense.filterGuard(data).subscribe(({ success, data }) => {
      if (success) {
        // console.log(data);
        this.guradList = data;
        this.filteredGuards = data;
      }
    });
  }

  change(type, e) {
    let list = this.guradList;
    let filteredList = []
    // console.log("After filterlist", e, type)
    if (e.checked) {
      list.forEach((guard) => {
        guard.doc.forEach(doc => {
          if (type == doc.name) {
            console.log('matched', guard);
            filteredList.push(guard);
          }
        })
      })
      this.guradList = filteredList
    }
  }

  toastNotification() {

    const newToastNotification = new ToastNotificationInitializer();
    newToastNotification.setTitle('Success');
    newToastNotification.setMessage('Success');

    // Choose layout color type
    newToastNotification.setConfig({
      autoCloseDelay: 3000, // optional
      textPosition: 'right', // optional
      layoutType: DialogLayoutDisplay.SUCCESS, // SUCCESS | INFO | NONE | DANGER | WARNING
      // progressBar: ToastProgressBarEnum.INCREASE, // INCREASE | DECREASE | NONE
      // toastUserViewType: ToastUserViewTypeEnum.SIMPLE, // STANDARD | SIMPLE
      animationIn: AppearanceAnimation.BOUNCE_IN, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      // TOP_LEFT | TOP_CENTER | TOP_RIGHT | TOP_FULL_WIDTH | BOTTOM_LEFT | BOTTOM_CENTER | BOTTOM_RIGHT | BOTTOM_FULL_WIDTH
      toastPosition: ToastPositionEnum.TOP_RIGHT,
      allowHtmlMessage: true,
    });

    // Simply open the popup
    newToastNotification.openToastNotification$();
  }

  viewDoc(doc) {
    window.open(doc.file, '_blank');
  }

  openModel(id) {
    this.staffService.getSpecificStaffData(id).subscribe(
      (res) => {
        if (res.success == true) {
          const modalRef = this.modalService.open(UserMenuComponent, {
            windowClass: "createModalClass",
            fullscreen: true,
            scrollable: true,
          });
          modalRef.componentInstance.fromParent = res.data;
          modalRef.result.then(
            (result) => {
              console.log("Modal Result", `Closed with: ${result}`);
            },
            (reason) => {

            }
          );
        }
      },
      (error) => {
        let status = "Staff Operation";
        let body = "Something went wrong";
        this.toastService.toastNotification1(body, status);
        console.log(error);
      }
    );
  }

  filterGuards() {
    if (!this.searchTerm) {
      this.filteredGuards = this.guradList;
    } 
    else {
      this.filteredGuards = this.guradList.filter(guard => 
        `${guard.first_name} ${guard.middle_name} ${guard.last_name}`
        .toLowerCase()
        .includes(this.searchTerm.toLowerCase())
      );
    }
  }
}


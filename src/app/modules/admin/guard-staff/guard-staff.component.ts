import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { PersonDetailComponent } from "../models/person-detail/person-detail.component";
import { OnBoardingStaffComponent } from "../models/on-boarding-staff/on-boarding-staff.component";
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

@Component({
  selector: 'app-guard-staff',
  templateUrl: './guard-staff.component.html',
  styleUrls: ['./guard-staff.component.scss']
})
export class GuardStaffComponent implements OnInit {

  new_staff_dropdown = false;

  users = [
    // { name: 'Add New Staff', text: 'Lorem ipsum dolor sit amet consectetur adipisicing elit.', url: '', icon: 'supervised_user_circle', image: ''},
    { name: 'Current Staff', text: 'Displays data of the guards who are currently on board', url: 'staff', icon: 'supervised_user_circle', image: '/assets/images/nav-images/currentStaff.png'},
    { name: 'Potential New Staff', text: 'Displays data of the guards who sign up through the app.', url: 'potential-new-staff', icon: 'supervised_user_circle', image: '/assets/images/nav-images/potentialStaff.png'},
  ]

  routeId
  constructor(public router: Router, private modalService: NgbModal, private trackAdmin: TrackAdminActivityService) { 
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Staff Page', 'Exit Staff Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Staff Page', 'Enter in Staff Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  guard(userType: string) {
    const path = `guard-staff/${userType}`;
    this.router.navigate([path]);
  }

    /**Add new Staff Model */
    addNewStaff() {
      this.new_staff_dropdown = !this.new_staff_dropdown;
    }

    createNewStaff(staff) {
      this.new_staff_dropdown = false;
      if (staff == "newStaff") {
        const modalRef = this.modalService.open(PersonDetailComponent, {
          windowClass: "personalDetailOpenModalClass ",
          // fullscreen: true,
          size: "xl",
          animation: false,
        });
        modalRef.componentInstance.fromParent = staff;
        modalRef.result.then(
          (result) => {
            console.log("Modal Result", `Closed with: ${result}`);
          },
          (reason) => {
            let status = this.getDismissReason(reason);
            console.log(status);
          }
        );
      } else {
        const modalRef = this.modalService.open(OnBoardingStaffComponent, {
          centered: true,
          size: "lg",
          windowClass: "onboarding-model",
          animation: true,
        });
        modalRef.componentInstance.fromParent = staff;
        modalRef.result.then(
          (result) => {
            console.log("Modal Result", `Closed with: ${result}`);
          },
          (reason) => {
            let status = this.getDismissReason(reason);
            if (status == "QuickStaff") {
            }
          }
        );
      }
    }

    getDismissReason(reason: any): string {
      if (reason === ModalDismissReasons.ESC) {
        return "by pressing ESC";
      } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
        return "by clicking on a backdrop";
      } else {
        return `${reason}`;
      }
    }

}

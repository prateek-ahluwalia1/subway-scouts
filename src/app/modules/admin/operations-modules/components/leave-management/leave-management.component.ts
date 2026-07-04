import { Component, OnInit } from '@angular/core';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { GlobalVariable } from 'app/shared/global';
import { Router } from '@angular/router';
import { StaffService } from 'app/services/staff.service';
import { trigger, transition, style, animate, query, stagger } from '@angular/animations';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
import { LeaveDetailsComponent } from 'app/modules/admin/models/leave-details/leave-details.component';

@Component({
  selector: 'app-leave-management',
  templateUrl: './leave-management.component.html',
  styleUrls: ['./leave-management.component.scss'],
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('500ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('500ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ]
})

export class LeaveManagementComponent implements OnInit {

  guards: any = []
  filteredGuards: any = [];
  searchQuery: string = '';
  routeId
  adminPermissions: any

  constructor(private modalService: NgbModal, private globals: GlobalVariable, private router: Router, private service: StaffService,
    private trackAdmin: TrackAdminActivityService, private permissionService: PermissionsService) {

    const per = this.permissionService.getPermissionsByTitle('WFM Tools');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Leave Management');
    console.log(this.adminPermissions);
    const activatedUrl = this.router.url;
    this.globals.ActivateUrl = activatedUrl
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
    this.getLeave()
  }

  ngOnDestroy() {
    // localStorage.removeItem('routerId');

    // this.trackAdmin.storeActivity('Exit Leave Management Page', 'Exit Leave Management Page', this.routeId).subscribe(res => {
    // })
  }

  activity() {
    this.trackAdmin.storeActivity('Leave Management Page', 'Enter in Leave Management Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  openModal(guard, type?) {
    console.log(guard);
    const modalRef = this.modalService.open(LeaveDetailsComponent, {
      scrollable: true, windowClass: "create"
      , size: 'small'
    })
    modalRef.componentInstance.fromParent = guard;
    modalRef.componentInstance.type = type;
    modalRef.result.then((result) => {
      console.log("Modal Result", `Closed with: ${result}`)
    }, (reason) => {
      console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)

    });
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else if (reason === 'updated') {
      console.log(reason);
    }
  }

  getLeave() {
    this.service.getstaffLeave().subscribe(({ success, data }) => {
      if (success) {
        this.guards = data
        this.filteredGuards = data;
      }
    })
  }
}

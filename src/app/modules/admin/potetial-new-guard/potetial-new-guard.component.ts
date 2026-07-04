import { Component, OnInit } from '@angular/core';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-potetial-new-guard',
  templateUrl: './potetial-new-guard.component.html',
  styleUrls: ['./potetial-new-guard.component.scss']
})
export class PotetialNewGuardComponent implements OnInit {

  disabldId
  title = "Angular 2 RC.4";
  model = "some text";
  usamn = 'Leave Management'
  guards = [
    { id: 1, name: 'Naveed', email: 'mail@mail.com', phone: '032432434', file: 'null', applydate: '1/27/2023', interviewdate: '1/27/2023', status: 'null', comment: 'null' },
    { id: 2, name: 'Usman Bhatti', email: 'mail@mail.com', phone: '032432434', file: 'null', applydate: '1/27/2023', interviewdate: '1/27/2023', status: 'null', comment: 'null' },
    { id: 3, name: 'Wajhat', email: 'mail@mail.com', phone: '032432434', file: 'null', applydate: '1/27/2023', interviewdate: '1/27/2023', status: 'null', comment: 'null' },
    { id: 4, name: 'Rameez', email: 'mail@mail.com', phone: '032432434', file: 'null', applydate: '1/27/2023', interviewdate: '1/27/2023', status: 'null', comment: 'null' },
  ]

  id; 
  type;
  data;
  routeId
  constructor(private global: GlobalVariable, private trackAdmin: TrackAdminActivityService) {
    this.id = this.global.admin.admin_id
    this.type = this.global.admin.admin_user_type
    console.log(this.id,this.type);
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
   }

  ngOnInit(): void {
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Potential New Staff Page', 'Exit Potential New Staff Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Potential New Staff Page', 'Enter in Potential New Staff Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  ok(e, id) {
    console.log('id', id);
    this.disabldId = id
    console.log(e.target.textContent);

  }
  save(id) {
    console.log('id', id);

  }
}

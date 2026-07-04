import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { GlobalVariable } from 'app/shared/global';
import { CustomerMainComponent } from '../models/customer-main/customer-main.component';
import { CustomerService } from 'app/services/customer.service';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastServiceService } from 'app/services/toast-service.service';
import { UserMenuComponent } from '../models/user-menu/user-menu.component';
import { StaffService } from 'app/services/staff.service';
import { ContractorService } from 'app/services/contractor.service';

@Component({
  selector: 'app-other-user-common-card',
  templateUrl: './other-user-common-card.component.html',
  styleUrls: ['./other-user-common-card.component.scss']
})
export class OtherUserCommonCardComponent implements OnInit {

  id
  type
  rates = [
    { name: 'Personal Detail & Documents', text: 'View and update your documents.', url: '', icon: 'heroicons_outline:currency-dollar', image: '/assets/images/nav-images/DocSettings.png' },
  ]
  constructor(public router: Router, private global: GlobalVariable, public model: NgbModal, private cusservice: CustomerService,
    private staffService: StaffService, private toast: ToastServiceService,private contServer: ContractorService,) { }

  ngOnInit(): void {
    
    this.id = this.global.admin.admin_id
    this.type = this.global.admin.admin_user_type
    console.log(this.id,this.type);

  }


  Rates() {
    if (this.type == 'customer') {
      this.cusservice.getSpecCustomer(this.id).subscribe(res => {
        if (res.success) {
          const modalRef = this.model.open(CustomerMainComponent, { windowClass: "customerMain", size: 'xl', scrollable: true })
          modalRef.componentInstance.fromParent = res;
          modalRef.componentInstance.type = 'customer';
          modalRef.result.then((result) => {
            console.log("Modal Result", `Closed with: ${result}`)
          }, (reason) => {
            console.log(reason);
            if (reason == 'true') {
              this.toast.toastNotification('Customer Updated Successfully!', 'Custoemr Operation!');
            }
            if (reason == 'update') {
              console.log(reason);
            }
          });
        }
      }, (error) => {
        console.log(error);
      });

    }
    else if (this.type == 'guard') {
      this.staffService.getSpecificStaffData(this.id).subscribe(
        (res) => {
          if (res.success == true) {
            const modalRef = this.model.open(UserMenuComponent, {
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
                let rea = this.getDismissReason(reason)
                if (rea == 'update') {
                }
              }
            );
          }
        },
        (error) => {
          let status = "Staff Operation";
          let body = "Something went wrong";
          this.toast.toastNotification1(body, status);
          console.log(error);
        }
      );
    }
    else if(this.type == 'contractor'){
      this.contServer.getSpecContractor(this.id).subscribe(res => {
        if (res.success) {
          const modalRef = this.model.open(CustomerMainComponent, { windowClass: "customerMain", size: 'xl', scrollable: true })
          modalRef.componentInstance.fromParent = res;
          modalRef.componentInstance.type = 'contrctor';
          modalRef.result.then((result) => {
            console.log("Modal Result", `Closed with: ${result}`)
          }, (reason) => {
            console.log(reason);
            if (reason == 'true') {
              this.toast.toastNotification('Contractor Updated Successfully!', 'Contractor Operation!');
            }
            if (reason == 'update') {
              console.log(reason);
            }
          });
        }
      }, (error) => {
        console.log(error);
      });
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

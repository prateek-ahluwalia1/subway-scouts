import { Component, OnInit } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { CreateNewPatrolCarDetailsComponent } from '../models/create-new-patrol-car-details/create-new-patrol-car-details.component';
import { PatrollingService } from 'app/services/patrolling.service';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
  selector: 'app-patrol-car-details',
  templateUrl: './patrol-car-details.component.html',
  styleUrls: ['./patrol-car-details.component.scss']
})
export class PatrolCarDetailsComponent implements OnInit {

  searchText = '';

  constructor(private modalService: NgbModal, private patrollingService: PatrollingService,
    private toast: ToastServiceService) { }

  ngOnInit(): void {

    this.getAllCarDetails();
  }

  addNewCar(){
    const modalRef = this.modalService.open(CreateNewPatrolCarDetailsComponent, { size: 'lg' });
    // modalRef.componentInstance.fromParent = 'new';
    modalRef.result.then(
      (result) => {
        console.log(result);

        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        console.log(reason);
        this.getAllCarDetails();
      }
    );
  }

  getCarDetails: any =[];
  getAllCarDetails(){
    this.patrollingService.getAllCarDetails().subscribe(({ success, data }) => {
      if (success) {
        this.getCarDetails = data;
      }
    })
  }

  editCarDetails(id){
    const modalRef = this.modalService.open(CreateNewPatrolCarDetailsComponent, { size: 'lg' });
    modalRef.componentInstance.fromParent = id;
    modalRef.result.then(
      (result) => {
        console.log(result);

        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        console.log(reason);
        this.getAllCarDetails()
      }
    );
  }

  deleteCarDetails(id){
    this.patrollingService.delCarDetails(id).subscribe(({ success, status, msg }) => {
      if (success) {
        status = "Delete Car Details"
        this.toast.toastNotification(msg, status)
        this.getAllCarDetails()
      }
    })
  }

  get filteredCars() {
    return this.getCarDetails.filter((item) =>
    item.name.toLowerCase().includes(this.searchText.toLowerCase())
    );
  }

}

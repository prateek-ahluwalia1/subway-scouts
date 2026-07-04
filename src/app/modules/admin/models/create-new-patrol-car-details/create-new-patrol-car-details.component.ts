import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { PatrollingService } from 'app/services/patrolling.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import moment from 'moment';
import { CreateNewMechanicComponent } from '../create-new-mechanic/create-new-mechanic.component';

@Component({
  selector: 'app-create-new-patrol-car-details',
  templateUrl: './create-new-patrol-car-details.component.html',
  styleUrls: ['./create-new-patrol-car-details.component.scss']
})
export class CreateNewPatrolCarDetailsComponent implements OnInit {

  patrollingLocations: any[] = [];
  CreatePatrolCar: FormGroup;
  @Input() fromParent;

  constructor(private modalService: NgbActiveModal, private patrollingService: PatrollingService, private fb: FormBuilder,
              public dateAdapter: DateAdapter<Date>, private toast: ToastServiceService, private modal: NgbModal,) 
  { 
    this.dateAdapter.setLocale('en-AU');
  }

  ngOnInit(): void {

    this.getPatrollingLocation();

    this.CreatePatrolCar = this.fb.group({
      name: new FormControl(''),
      // patrol_site_id: new FormControl(''),
      car_model: new FormControl(''),
      rego_num: new FormControl(''),
      rego_exp_date: new FormControl(''),
      insurance_comp_name: new FormControl(''),
      insurance_num: new FormControl(''),
      // insurance: new FormControl(''),
      insurance_exp_date: new FormControl(''),
      mech_name: new FormControl(''),
      // maintenance: new FormControl(''),
      // odometer: new FormControl(''),
      // kilometer: new FormControl('')
      last_serv_date: new FormControl(''),
      next_serv_date: new FormControl('')
    })

    if(this.fromParent){
      this.getSpecificCar(this.fromParent)
    }
  }

  close(){
    this.modalService.dismiss();
  }

  getPatrollingLocation() {
    let data={
      type: 'normal',
    }
    this.patrollingService.getPatrollingLocations(data).subscribe(({ success, data }) => {
      if (success) {
        this.patrollingLocations = data;
      }
    })
  }

  submitDetails(){
    this.CreatePatrolCar.value.rego_exp_date = moment(this.CreatePatrolCar.value.rego_exp_date).format('MM-DD-YYYY')
    this.CreatePatrolCar.value.insurance_exp_date = moment(this.CreatePatrolCar.value.insurance_exp_date).format('MM-DD-YYYY')

    if(this.fromParent){
      this.CreatePatrolCar.value.id = this.fromParent;
      this.patrollingService.UpdateCarDetails(this.CreatePatrolCar.value).subscribe(({ status, success, message }) => {
        if (success) {
          this.close();
          status = "Update Car Details"
          this.toast.toastNotification(message, status)
        }
      })
    }
    else{
      // console.log("Submit data", this.CreatePatrolCar.value)
      this.patrollingService.StoreCarDetails(this.CreatePatrolCar.value).subscribe(({ status, success, message }) => {
        if (success) {
          this.close();
          status = "Car Details"
          this.toast.toastNotification(message, status)
        }
      })
    }
  }

  getSpecificCar(id){
    let data= {
      id:id
    }
    this.patrollingService.editCar(data).subscribe(({success, data}) =>{
      if(success){
        this.CreatePatrolCar.get('name').setValue(data.name)
        this.CreatePatrolCar.get('patrol_site_id').setValue(data.patrol_site_id)
        this.CreatePatrolCar.get('car_model').setValue(data.car_model)
        this.CreatePatrolCar.get('rego_num').setValue(data.rego_num)
        if(data.rego_exp_date){
          let rego_exp_date = moment(data?.rego_exp_date, 'MM-DD-YYYY')
          this.CreatePatrolCar.get('rego_exp_date').setValue(rego_exp_date.format())
        }
        this.CreatePatrolCar.get('insurance').setValue(data.insurance)
        if(data.insurance_exp_date){
          let insurance_exp_date = moment(data?.insurance_exp_date, 'MM-DD-YYYY')
          this.CreatePatrolCar.get('insurance_exp_date').setValue(insurance_exp_date.format())
        }
        this.CreatePatrolCar.get('maintenance').setValue(data.maintenance)
        this.CreatePatrolCar.get('odometer').setValue(data.odometer)
        this.CreatePatrolCar.get('kilometer').setValue(data.kilometer)
      }
    })
  }

  createNewMechanic(){
    const modalRef = this.modal.open(CreateNewMechanicComponent, { centered: true });
    // modalRef.componentInstance.fromParent = 'new';
    modalRef.result.then(
      (result) => {
        console.log(result);

        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        console.log(reason);
        // this.getAllAlarmDetails();
      }
    );
  }

}

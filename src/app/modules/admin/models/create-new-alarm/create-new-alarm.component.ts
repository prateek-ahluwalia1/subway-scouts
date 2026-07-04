import { HttpHeaders } from '@angular/common/http';
import { Component, Input, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { CustomerService } from 'app/services/customer.service';
import { PatrollingService } from 'app/services/patrolling.service';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
  selector: 'app-create-new-alarm',
  templateUrl: './create-new-alarm.component.html',
  styleUrls: ['./create-new-alarm.component.scss']
})
export class CreateNewAlarmComponent implements OnInit {

  CreateAlarmSystem: FormGroup;
  @Input() fromParent;
  alarm_file: boolean = false;

  constructor(private modalService: NgbActiveModal, private patrollingService: PatrollingService,
              private fb: FormBuilder, private toast: ToastServiceService, private cus: CustomerService,) {}

  ngOnInit(): void {

    this.CreateAlarmSystem = this.fb.group({
      name: [''],
      location_id: [''],
      keys: this.fb.array([]),
      alarm_panels: this.fb.array([]),
      car_id: [''],
      file: [''],
      docket_number: ['']
    })

    this.getPatrollingLocation();
    this.getPatrolCar();

    // if(this.fromParent){
    //   this.getSpecificAlarm(this.fromParent)
    // }
  }

  close(){
    this.modalService.dismiss();
  }

  patrollingLocations: any[] = [];
  getPatrollingLocation() {
    let data={
      type: 'normal',
    }
    this.patrollingService.getPatrollingLocations(data).subscribe(({ success, data }) => {
      if (success) {
        this.patrollingLocations = data;
        if(this.fromParent){
          this.getSpecificAlarm(this.fromParent)
        }
      }
    })
  }

  patrolCar: any[] = [];
  getPatrolCar(){
    this.patrollingService.getAllCarDetails().subscribe(({ success, data }) => {
      if (success) {
        this.patrolCar = data;
      }
    })
  }

  get keys() {
    return this.CreateAlarmSystem.get('keys') as FormArray;
  }

  get alarm_panels() {
    return this.CreateAlarmSystem.get('alarm_panels') as FormArray;
  }

  keysArray: any[] = [];
  alarmsArray: any[] = [];
  onSiteSelectionChange(event: any): void {
    const selectedIdx = event.value;
    const selectedLocation = this.patrollingLocations.find(location => location.id === selectedIdx);
    // console.log("Selected site data", selectedLocation)

    this.keysArray = JSON.parse(selectedLocation.keys);

    // console.log("keys data", this.keysArray)

    if (this.keysArray && this.keysArray.length > 0) {
      this.keysArray.forEach(key => {
        this.keys.push(this.fb.group({
          key_name: new FormControl(key.key_name),
          key_number: new FormControl(key.key_number),
          key_path: new FormControl(key.key_path),
        }));
      });
    }

    this.alarmsArray = JSON.parse(selectedLocation.alarm_panels);
    // console.log("Alarm panel Array", this.alarmsArray)

    if (this.alarmsArray && this.alarmsArray.length > 0) {
      this.alarmsArray.forEach(alarm => {
        this.alarm_panels.push(this.fb.group({
          alarm_panel_codes: new FormControl(alarm.alarm_panel_codes),
        }));
      });
    }

  }

  uploadAlarmFile: any;
  uploadAlarmDispatchFile(event){
    this.uploadAlarmFile = event.target.files[0];
    const myFormData = new FormData();
    const headers = new HttpHeaders({
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    })
    myFormData.append('file', this.uploadAlarmFile, this.uploadAlarmFile.name);
    myFormData.append('folder', 'Alarm_Dispatch_File')
    this.cus.uploadImgPdf(myFormData, {
      headers: headers
    }).subscribe(
      response => {
        if (response.success) {
          this.CreateAlarmSystem.get('file').setValue(response.url)
        }
        else {
          this.toast.toastNotification1('Something went wrong please check your file size or connection', 'File Upload!')
        }
      },
      (error) => {
        console.error(error);
      }
    );
  }

  Submit(){

    this.patrollingService.StoreAlarmDetails(this.CreateAlarmSystem.value).subscribe(({ status, success, message }) => {
      if (success) {
        this.close();
        status = "Alarm Dispatch Details"
        this.toast.toastNotification(message, status)
      }
    })
  }

  alarm_file_name
  uploadFileUrl
  getSpecificAlarm(id){
    let data = {
      id:id
    } 
    this.patrollingService.editAlarm(data).subscribe(({success, data}) =>{
      if(success){
        this.CreateAlarmSystem.get('name').setValue(data.name)
        this.CreateAlarmSystem.get('location_id').setValue(data.location_id)
        if(data.location_id){
          const selectedIdx = data.location_id;
          const selectedLocation = this.patrollingLocations.find(location => location.id === selectedIdx);

          this.keysArray = JSON.parse(selectedLocation.keys);
      
          if (this.keysArray && this.keysArray.length > 0) {
            this.keysArray.forEach(key => {
              this.keys.push(this.fb.group({
                key_name: new FormControl(key.key_name),
                key_number: new FormControl(key.key_number),
                key_path: new FormControl(key.key_path),
              }));
            });
          }

          this.alarmsArray = JSON.parse(selectedLocation.alarm_panels);
      
          if (this.alarmsArray && this.alarmsArray.length > 0) {
            this.alarmsArray.forEach(alarm => {
              this.alarm_panels.push(this.fb.group({
                alarm_panel_codes: new FormControl(alarm.alarm_panel_codes),
              }));
            });
          }

        }
        this.CreateAlarmSystem.get('car_id').setValue(data.car_id)
        this.CreateAlarmSystem.get('file').setValue(data.file)
        if (data.file) {
          this.alarm_file = true
          this.alarm_file_name = 'View Uploaded File'
          this.uploadFileUrl = data.file
        }
      }
    })
  }

  viewFile() {
    window.open(this.uploadFileUrl, '_blank')
  }

  fileuploaded: boolean = false
  handleFileInput() {
    this.fileuploaded = false
    this.alarm_file = false
  }

  RandomDocket(){
    const alphanumeric = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    let docketNumber = '';
    const length = 12;
    
    for (let i = 0; i < length; i++) {
      const randomIndex = Math.floor(Math.random() * alphanumeric.length);
      docketNumber += alphanumeric.charAt(randomIndex);
    }
    
    return docketNumber;
  }

  randomValue(){
    const randomNumber = this.RandomDocket();
    this.CreateAlarmSystem.get('docket_number').setValue(randomNumber);
  }

}

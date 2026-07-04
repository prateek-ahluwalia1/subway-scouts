import { Component, ElementRef, Input, OnInit, QueryList, ViewChildren } from '@angular/core';
import { FormArray, FormBuilder, FormGroup } from '@angular/forms';
import { MatSelectChange } from '@angular/material/select';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { CustomerService } from 'app/services/customer.service';
import { PatrollingService } from 'app/services/patrolling.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { MatSelect } from '@angular/material/select';
import { HttpHeaders } from '@angular/common/http';
export class Customers {
  id: number;
  name: string;
}

@Component({
  selector: 'app-create-new-runsheet',
  templateUrl: './create-new-runsheet.component.html',
  styleUrls: ['./create-new-runsheet.component.scss']
})
export class CreateNewRunsheetComponent implements OnInit {

  CreateRunsheet: FormGroup;
  patrollingLocations: any[] = [];
  @Input() fromParent;
  description_file: boolean = false;
  CallsNumber = [
    { name: '1', value: 1},
    { name: '2', value: 2},
    { name: '3', value: 3 },
    { name: '4', value: 4 },
  ];
  customers: Customers[] = [];
  runsheetId: number[] = [];
  @ViewChildren(MatSelect, { read: ElementRef }) matSelects: QueryList<ElementRef>;

  constructor(private modalService: NgbActiveModal, private fb: FormBuilder, private patrollingService: PatrollingService,
    public toast: ToastServiceService, private cus: CustomerService,) {
    this.getAllCustomers();
   }

  ngOnInit(): void {
    this.CreateRunsheet = this.fb.group({
      title: [''],
      state: [''],
      sites: this.fb.array([]),
      description:[''],
      patrol_brief_file: ['']
    })

    if(this.fromParent){
      this.getSpecificRunsheet(this.fromParent)
    }

    console.log("Edit Runsheet", this.fromParent)
  }

  ngAfterViewInit() {
    if (this.matSelects && this.matSelects.length > 0) {
      console.log("MatSelects are available", this.matSelects);
    } else {
      console.log("MatSelects not available or empty");
    }
  }

  getPatrollingLocation(index: number, id) {
    let data={
      customer_id: id,
      type: 'run_sheet',
    }
    this.patrollingService.getPatrollingLocations(data).subscribe(({ success, data }) => {
      if (success) {
        this.patrollingLocations[index] = data;
        // this.patrollingLocation = data;
      }
    })
  }

onSiteSelectionChange(event: any, index: number): void {
  const selectedId: number = event.value;

  if (this.runsheetId.includes(selectedId)) {
    console.log("Site is already selected");
  } 
  else {
    this.runsheetId.push(selectedId);
    console.log("Selected sites", this.runsheetId);
  }
}

isSiteSelected(siteId: number): boolean {
  return this.runsheetId.includes(siteId);
}

  getAllCustomers(){
    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data;
      }
    });
  }

  get sites() {
    return this.CreateRunsheet.get('sites') as FormArray;
  }

  AddSites(){
    this.sites.push(this.fb.group({
      site_id: [''],
      customer_id: ['']
    }));
  }

  close(){
    this.modalService.dismiss();
  }

  Submit(){
    if(this.fromParent){
      let data = {
        id: this.fromParent,
        title: this.CreateRunsheet.value.title,
        state: this.CreateRunsheet.value.state,
        description: this.CreateRunsheet.value.description,
        sites: this.CreateRunsheet.value.sites,
        patrol_brief_file: this.CreateRunsheet.value.patrol_brief_file

      }
      // console.log("After Update", data)
      this.patrollingService.createRunsheet(data).subscribe(({ status, success, message }) => {
        if (success) {
          this.close();
          status = "Update Runsheet"
          this.toast.toastNotification(message, status)
        }
      })
    }
    else{
      let data = {
        title: this.CreateRunsheet.value.title,
        state: this.CreateRunsheet.value.state,
        description: this.CreateRunsheet.value.description,
        sites: this.CreateRunsheet.value.sites,
        patrol_brief_file: this.CreateRunsheet.value.patrol_brief_file
      }
      // console.log("Submit data", data)
      this.patrollingService.createRunsheet(data).subscribe(({ status, success, message }) => {
        if (success) {
          this.close();
          status = "Create Runsheet"
          this.toast.toastNotification(message, status)
        }
      })
    }
  }

  desc_file_name
  getSpecificRunsheet(id){
    this.patrollingService.getSpecific(id).subscribe(({success, data}) =>{
      if(success){
        this.CreateRunsheet.get('title').setValue(data.title)
        this.CreateRunsheet.get('state').setValue(data.state)
        this.CreateRunsheet.get('description').setValue(data.description)
        this.CreateRunsheet.get('patrol_brief_file').setValue(data.patrol_brief_file)
        if (data.patrol_brief_file) {
          this.description_file = true
          this.desc_file_name = 'View Uploaded File'
          this.uploadFileUrl = data.patrol_brief_file
        }
        if (data.run_sheet_details) {
          data.run_sheet_details.forEach((scan, index) => {
            let data={
              customer_id: scan.customer_id,
              runsheet_id: this.fromParent
            }
            this.patrollingService.getPatrollingLocations(data).subscribe(({ success, data }) => {
              if (success) {
                if (!this.patrollingLocations[index]) {
                  this.patrollingLocations[index] = [];
                }
                this.patrollingLocations[index] = data;
              }
            })
            this.sites.push(this.fb.group({
              customer_id: scan.customer_id,
              site_id: scan.site_id,
              no_calls: scan.no_calls,
              id: scan.id
            }));
          })
        }
      }
    })
  }

  removeRunsheet(index){
    const fieldArray = this.sites;
    if (index >= 0 && index < fieldArray.length) {
      const selectedSite = fieldArray.at(index).value;
      const selectedId = selectedSite.id;
      if(selectedId){
        console.log("Selected Id", selectedId)
        let data = {
          runsheet_detail_id: selectedId,
          runsheet_id: this.fromParent
        }
        this.patrollingService.deleteSpecific(data).subscribe(({success, message, status}) =>{
          if(success){
            status = "Delete Runsheet"
            this.toast.toastNotification(message, status)
            fieldArray.removeAt(index);
          }
        })
      }
      else{
        fieldArray.removeAt(index);
      }
    }
  }

  selectedCustomerIds: any = [];
  onCustomerSelectionChange(event: MatSelectChange, index: number) {
    this.selectedCustomerIds[index] = event.value;
    // const selectedCustomerId = this.selectedCustomerIds[index];
    // console.log('Selected Customer ID:', this.selectedCustomerIds[index]);
    // this.getPatrollingLocation(this.selectedCustomerIds[index]);
    this.getPatrollingLocation(index, this.selectedCustomerIds[index]);
  }

  uploadDescriptionFile: any;
  uploadAlarmFile(event) {
    this.uploadDescriptionFile = event.target.files[0];
    const myFormData = new FormData();
    const headers = new HttpHeaders({
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    })
    myFormData.append('file', this.uploadDescriptionFile, this.uploadDescriptionFile.name);
    myFormData.append('folder', 'Runsheet_Description_File')
    this.cus.uploadImgPdf(myFormData, {
      headers: headers
    }).subscribe(
      response => {
        if (response.success) {
          this.CreateRunsheet.get('patrol_brief_file').setValue(response.url)
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

  uploadFileUrl
  viewFile() {
    window.open(this.uploadFileUrl, '_blank')
  }

  fileuploaded: boolean = false
  handleFileInput() {
    this.fileuploaded = false
    this.description_file = false
  }


}

import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-create-new-mechanic',
  templateUrl: './create-new-mechanic.component.html',
  styleUrls: ['./create-new-mechanic.component.scss']
})
export class CreateNewMechanicComponent implements OnInit {

  CreateMechanicDetail: FormGroup;

  constructor(private modalService: NgbActiveModal, private fb: FormBuilder) { }

  ngOnInit(): void {

    this.CreateMechanicDetail = this.fb.group({
      mech_busi_name: new FormControl(''),
      name: new FormControl(''),
      address: new FormControl(''),
      phone: new FormControl(''),
      email: new FormControl('')
    })
  }

  close(){
    this.modalService.dismiss();
  }

    /*AutoComplete Addres input */
    formattedAddress = ''
    options = {
      componentRestrictions: {
        country: ['AU']
      }
    }
  public handleAddressChange(address: any) {
    if (address) {
      this.formattedAddress = address.formatted_address;
      this.CreateMechanicDetail.get('address').setValue(this.formattedAddress);
    }
    else{
      this.CreateMechanicDetail.get('address').setValue('');
    }
  }

  addNew(){
    console.log("Submit data", this.CreateMechanicDetail.value)
  }

}

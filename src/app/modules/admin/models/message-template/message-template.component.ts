import { Component, OnInit } from '@angular/core';
import { AbstractControl, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-message-template',
  templateUrl: './message-template.component.html',
  styleUrls: ['./message-template.component.scss']
})
export class MessageTemplateComponent implements OnInit {

public templateForm:FormGroup
submitted=false;
  constructor(private ngbActiveModal:NgbActiveModal,private formBuilder:FormBuilder,private globals:GlobalVariable) { }

  ngOnInit(): void {
    this.templateForm = this.formBuilder.group({
      heading: ['', Validators.required],
      body: ['', Validators.required],

    })
  }
  onFormSubmit(value){
    this.submitted=true;
    if(!this.templateForm.valid){
        console.log('not valid')
    }
    else{
      this.globals.messageTemplates.push(value);
    this.ngbActiveModal.dismiss();
      this.templateForm.reset()

    }
    

  }
  close(data?) {
   
    this.ngbActiveModal.dismiss(data);
  }
  get errorControl(): { [key: string]: AbstractControl } {
    return this.templateForm.controls;
  }
}

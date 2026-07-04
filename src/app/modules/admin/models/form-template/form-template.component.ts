import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { FormioUtils } from 'angular-formio';
import Formio from 'formiojs/Formio';
@Component({
  selector: 'app-form-template',
  templateUrl: './form-template.component.html',
  // styleUrls: ['./form-template.component.scss']
})
export class FormTemplateComponent implements OnInit {


  @ViewChild('json') jsonElement?: ElementRef;
  public form: Object = { components: [] };
 

  constructor(public ngbActiveModal: NgbActiveModal) { }

  ngOnInit(): void {
  }

  close(data?) {
    this.ngbActiveModal.dismiss(data);
  }
  onChange(event) {
    console.log(event.form);
  }

  saveForm() {
    console.log(this.form);
    // You can make an HTTP request to your Laravel API here to save the form
  }

  
}

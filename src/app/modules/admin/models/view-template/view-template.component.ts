import { Component, Input, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-view-template',
  templateUrl: './view-template.component.html',
  styleUrls: ['./view-template.component.scss']
})
export class ViewTemplateComponent implements OnInit {

  title
  @Input() fromParent;
  @Input() fromParentForm;
  constructor(private active: NgbActiveModal) { }

  ngOnInit(): void {
    const modalContent = document.querySelector('.modal-body');
    if (this.fromParent) {
      if (this.fromParent.body.html) {
        modalContent.innerHTML = this.fromParent.body.html;
        this.title = this.fromParent.title
      }
      else {
        modalContent.innerHTML = this.fromParent.body;
        this.title = this.fromParent.title;
      }
    }
    else{
      console.log('else',this.fromParentForm.components[0]);
      
      modalContent.innerHTML = this.fromParentForm.components[0];
    }
    
  }

  dismiss(data) {
    this.active.close()
  }

}

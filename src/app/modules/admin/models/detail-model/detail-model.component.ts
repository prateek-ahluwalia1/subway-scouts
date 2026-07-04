import { Component, Input, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { GlobalVariable } from 'app/shared/global';
@Component({
  selector: 'app-detail-model',
  templateUrl: './detail-model.component.html',
  styleUrls: ['./detail-model.component.scss']
})
export class DetailModelComponent implements OnInit {

  @Input() public document;
  date: string;
 
  constructor(
    public  modalService: NgbActiveModal,public globals:GlobalVariable
  ) { 
    let d=new Date();
    this.date=d.toLocaleDateString();
    console.log("date ",this.date);
    
  }

  ngOnInit(): void {
    console.log("document detail in detail page",this.document);

    
    
    
    
    
  }

  close(){
    this.modalService.dismiss();
  }

  clear(){
    console.log("befor ",this.globals.documentsList);
    
this.globals.documentsList.splice(this.document.index,1);
console.log("after ",this.globals.documentsList);
this.close();

  }

}

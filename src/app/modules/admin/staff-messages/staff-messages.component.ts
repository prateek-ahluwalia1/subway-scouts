import { SelectionModel } from '@angular/cdk/collections';
import { Component, OnInit, ViewChild } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatTableDataSource } from '@angular/material/table';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { GlobalVariable } from 'app/shared/global';
import {PageEvent} from '@angular/material/paginator';

import { MessageTemplateComponent } from '../models/message-template/message-template.component';
export interface PeriodicElement {
  image: string;
  name: string;
  status: string;
  date: string;
}
const ELEMENT_DATA: PeriodicElement[] = [
  { image: 'image', name: 'Naveed', status: 'delivered', date: '23/05/2022',  },
  { image: 'image', name: 'Usman', status: 'delivered', date: '23/05/2022',  },

 
];
@Component({
  selector: 'app-staff-messages',
  templateUrl: './staff-messages.component.html',
  styleUrls: ['./staff-messages.component.scss']
})
export class StaffMessagesComponent implements OnInit {
  length = 50;
  pageSize = 10;
  pageIndex = 0;
  pageSizeOptions = [5, 10, 25];

  hidePageSize = false;
  showPageSizeOptions = true;
  showFirstLastButtons = true;
  disabled = false;

  pageEvent: PageEvent;
  handlePageEvent(e: PageEvent) {
    this.pageEvent = e;
    this.length = e.length;
    this.pageSize = e.pageSize;
    this.pageIndex = e.pageIndex;
  }
   setPageSizeOptions(setPageSizeOptionsInput: string) {
    if (setPageSizeOptionsInput) {
      this.pageSizeOptions = setPageSizeOptionsInput.split(',').map(str => +str);
    }
  }
  guards=[
    'Naveed',
    'Bhatti',
    'Wajahat',
    "Raees"
  ]
  filters=[
    'Naveed',
    'Bhatti',
    'Wajahat',
    "Raees"
  ]
  filtersShown=false;
  messageTemplates=[
    {heading:'Hello 1',body:'This is test message 1',id:1},
    {heading:'Hello 2',body:'This is test message 2',id:2,},
    {heading:'Hello 3',body:'This is test message 3',id:3},
  
  ]

  dataSource = new MatTableDataSource<PeriodicElement>(ELEMENT_DATA);
  selection = new SelectionModel<PeriodicElement>(true, []);
  
activeTemplate={
  id:'',
  heading:'',
  body:''
}

@ViewChild('paginator') paginator: MatPaginator;
  constructor(private modalService:NgbModal,public globals:GlobalVariable) { }
  displayedColumns: string[] = ['image', 'name', 'date', 'status'];

  ngOnInit(): void {
  }
  ngAfterViewInit() {
  
    this.dataSource.paginator = this.paginator;
}
  selectMessage(template){
    this.activeTemplate=template
    console.log(template)
  }
  openTemplateModal(){

    const modalRef = this.modalService.open(MessageTemplateComponent, {
      scrollable: true, windowClass: "create"
      , size: 'lg'
    })
    modalRef.componentInstance.fromParent = '';
    modalRef.result.then((result) => {
      console.log("Modal Result", `Closed with: ${result}`)
    }, (reason) => {
      console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)

    });

  }
  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else if (reason === 'updated') {
      console.log(reason);
    
    }
  }
  showFilters(){
    if(this.filtersShown){
      this.filtersShown=false;
    }
    else{
      this.filtersShown=true
    }
  }
}

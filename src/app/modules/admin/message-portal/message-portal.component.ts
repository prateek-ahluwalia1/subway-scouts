import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-message-portal',
  templateUrl: './message-portal.component.html',
  styleUrls: ['./message-portal.component.scss']
})
export class MessagePortalComponent implements OnInit {
show:boolean=true;
  sideBar=[{"name":"Create New"},{"name":"Sent Items"},{"name":"Templates"}]
  selectedIndex: number= 0;

  constructor() { }
  ngOnInit(): void {
  }
  

  setIndex(index: number) {
     this.selectedIndex = index;
  }
  isShow(){
    this.show=!this.show;
  }

}

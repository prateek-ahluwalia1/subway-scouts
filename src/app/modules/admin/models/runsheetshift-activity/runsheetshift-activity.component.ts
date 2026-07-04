import { Component, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-runsheetshift-activity',
  templateUrl: './runsheetshift-activity.component.html',
  styleUrls: ['./runsheetshift-activity.component.scss']
})
export class RunsheetshiftActivityComponent implements OnInit {

  sideCards = [
    {
      text: "Sign In/Out Details",
      logo: "../../../../../assets/icons/jobshiftActivity/Group 465.png",
      background: "#BFFFFB",
      color: " #027069",
    },
    {
      text: "Break Details",
      logo: "../../../../../assets/icons/jobshiftActivity/Group.png",
      background: "#FFFCCA",
      color: " #9C8928",
    },
    {
      text: "Green Call",
      logo: "../../../../../assets/icons/jobshiftActivity/Vector-2.png",
      background: "#BBF5B1",
      color: " #105652",
    },
    {
      text: "Welfare Call",
      logo: "../../../../../assets/icons/jobshiftActivity/Vector.png",
      background: "#BFD1F3",
      color: " #4B689F",
    },
    {
      text: "Tracker",
      logo: "../../../../../assets/icons/jobshiftActivity/group-646.png",
      background: "#D6D6D6",
      color: " #767676",
    },
    {
      text: "Incident Report",
      logo: "../../../../../assets/icons/jobshiftActivity/Vector-1.png",
      background: "#F2CCC0",
      color: " #C20606",
    },
    {
      text: "Operation Notes",
      logo: "../../../../../assets/icons/jobshiftActivity/Group 463.png",
      background: "#FFFCCA",
      color: " #9C8928",
    },
    {
      text: "Shift Activity",
      logo: "../../../../../assets/icons/jobshiftActivity/Group 466.png",
      background: "#BBF5B1",
      color: " #105652",
    },
    {
      text: "Shift Task",
      logo: "../../../../../assets/icons/createSite/Group.png",
      background: "#BFFFFB",
      color: " #027069",
    },
    
    {
      text: "Foot Patrol Report",
      logo: "../../../../../assets/icons/jobshiftActivity/Vector-1.png",
      background: "#F2CCC0",
      color: "#C20606",
    },
    {
      text: "Rating",
      logo: "../../../../../assets/icons/star.png",
      background: "#FFFCCA",
      color: "#9C8928",
    },
   
  ];

  borderColors: string[] = [
    "3px solid #027069",
    "3px solid #9C8928",
    "3px solid #105652",
    "3px solid #4B689F",
    "3px solid #767676",
    "3px solid #C20606",
    "3px solid #9C8928",
    "3px solid #105652",
    "3px solid #027069",
    "3px solid #C20606",
    "3px solid #9C8928",

  ];

  selectedCardIndex: number = 0;
  isEditing: boolean = false

  constructor(public modalService: NgbActiveModal,) { }

  ngOnInit(): void {
  }

  selectCard(index: number) {
    this.selectedCardIndex = index;
    if (this.selectedCardIndex == 5) {
      this.isEditing = false
    }
    if (this.selectedCardIndex == 7) {
      // this.getShiftActivity()
    }

    if (this.selectedCardIndex == 6) {
      // this.getNotes()
    }
    if (this.selectedCardIndex == 1) {
      // this.getBreakDetail()
    }

    if (this.selectedCardIndex == 8) {
      // this.getShiftTask()
    }

    if (this.selectedCardIndex == 9) {
      this.isEditing = false
      // this.getFootPatrol()
    }

  }

  close() {
    this.modalService.dismiss();
  }

}

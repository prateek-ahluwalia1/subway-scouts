import { Component, OnInit } from '@angular/core';
import { ModalDismissReasons, NgbActiveModal, NgbDatepickerModule, NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-notes-for-shift',
  templateUrl: './notes-for-shift.component.html',
  styleUrls: ['./notes-for-shift.component.scss']
})
export class NotesForShiftComponent implements OnInit {
  closeResult = '';
  constructor(private modalService: NgbModal, private acticeModel: NgbActiveModal) { }

  ngOnInit(): void {
  }
  open(content) {
		this.modalService.open(content, { ariaLabelledBy: 'modal-basic-title' }).result.then(
			(result) => {
				this.closeResult = `Closed with: ${result}`;
			},
			(reason) => {
				this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
			},
		);
	}

	private getDismissReason(reason: any): string {
		if (reason === ModalDismissReasons.ESC) {
			return 'by pressing ESC';
		} else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
			return 'by clicking on a backdrop';
		} else {
			return `with: ${reason}`;
		}
	}

  closeComponent(){
    this.acticeModel.dismiss()
  }

}

import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { PatrollingService } from 'app/services/patrolling.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { GlobalVariable } from 'app/shared/global';
import { MatDialog } from '@angular/material/dialog';
import { MatTableDataSource } from '@angular/material/table';
import { SiteShiftsComponent } from 'app/modules/admin/site-shifts/site-shifts.component';
import { CreateNewRunsheetComponent } from 'app/modules/admin/models/create-new-runsheet/create-new-runsheet.component';

export interface PeriodicElement {
  title: string;
  action: string;
}

const ELEMENT_DATA: PeriodicElement[] = [

];

@Component({
  selector: 'app-runsheet',
  templateUrl: './runsheet.component.html',
  styleUrls: ['./runsheet.component.scss']
})
export class RunsheetComponent implements OnInit {

  getRunsheet: any = [];
  @ViewChild('deletecontent') deletecontent: TemplateRef<any>;
  modalRef;
  AddDeleteReason: FormGroup;
  runsheet_id;
  searchText = '';
  dataSource = new MatTableDataSource<PeriodicElement>(ELEMENT_DATA);

  constructor(private modalService: NgbModal, private patrollingService: PatrollingService, private toast: ToastServiceService,
    private fb: FormBuilder, private global: GlobalVariable, public dialog: MatDialog) { }


  ngOnInit(): void {
    this.getAllRunsheet();

    this.AddDeleteReason = this.fb.group({
      reason: new FormControl('', Validators.required)
    })
  }

  createNewRunsheet() {
    const modalRef = this.modalService.open(CreateNewRunsheetComponent, { size: 'xl' });
    modalRef.componentInstance.fromParent = 'new';
    modalRef.result.then(
      (result) => {
        console.log(result);

        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        console.log(reason);
        this.getAllRunsheet();
      }
    );
  }

  getAllRunsheet() {
    this.patrollingService.getAllRunsheet().subscribe(({ success, data }) => {
      if (success) {
        this.getRunsheet = data;
      }
    })
  }

  editRunsheet(id) {
    const modalRef = this.modalService.open(CreateNewRunsheetComponent, { size: 'xl' });
    modalRef.componentInstance.fromParent = id;
    modalRef.result.then(
      (result) => {
        console.log(result);

        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        console.log(reason);
        this.getAllRunsheet()
      }
    );
  }

  delRunsheetModal(id) {
    this.runsheet_id = id;
    this.modalRef = this.modalService.open(this.deletecontent);
  }

  deletedata(id) {
    let data = {
      id: this.runsheet_id,
      reason: this.AddDeleteReason.value.reason,
      admin_id: this.global.admin.admin_id
    }

    this.patrollingService.deleteRunsheet(data).subscribe(({ message, success }) => {
      if (success) {
        this.toast.toastNotification1(message, 'Runsheet Operation!');
        this.AddDeleteReason.reset();
        this.getAllRunsheet();
      }
      else {
        this.dialog.open(SiteShiftsComponent, {
          width: '500px',
          data: {
            message: message
          }
        }).afterClosed().subscribe(response => {
          if (response === 'yes') {
            let data = {
              id: this.runsheet_id,
              reason: this.AddDeleteReason.value.reason,
              is_confirm: 'yes',
              admin_id: this.global.admin.admin_id
            }
            this.patrollingService.deleteRunsheet(data).subscribe(({ message, success }) => {
              if (success) {
                this.toast.toastNotification1(message, 'Runsheet Operation!')
                this.AddDeleteReason.reset();
                this.getAllRunsheet();
              }
            });
          }
        });
        // this.AddDeleteReason.reset();
        // this.toast.toastNotification1(message, 'Runsheet Operation!');
      }
    },
      (error) => {
        this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Runsheet Operation!');
      }
    );
    this.modalRef.close();
  }

  deleteReason;
  deletedRunsheets(deleterunsheet) {
    this.patrollingService.getdelRunsheetReason().subscribe(({ success, data }) => {
      if (success) {
        this.deleteReason = data;
      }
    })
    this.modalService.open(deleterunsheet, { size: 'lg', scrollable: true });
  }

  get filteredRunsheet() {
    return this.getRunsheet?.filter((runsheet) =>
      runsheet.title?.toLowerCase().includes(this.searchText.toLowerCase())
    );
  }


}

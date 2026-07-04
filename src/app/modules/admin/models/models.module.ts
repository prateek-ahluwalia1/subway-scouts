import { MatSelectModule } from "@angular/material/select";
import { MatDatepickerModule } from '@angular/material/datepicker';
import { NgbActiveModal, NgbPopover } from "@ng-bootstrap/ng-bootstrap";
import { MatMenuModule } from "@angular/material/menu";
import { NgModule } from "@angular/core";
import { DatePipe } from '@angular/common';

@NgModule({
  declarations: [
  
  
  ],
  providers: [MatDatepickerModule, MatMenuModule, NgbPopover, NgbActiveModal, MatSelectModule, DatePipe],
  imports: [
   
  ],
  exports: [
  ],
})
export class ModelsModule { }

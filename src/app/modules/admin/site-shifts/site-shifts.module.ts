import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';
import { BrowserModule } from '@angular/platform-browser';
import { SiteShiftsComponent } from './site-shifts.component';



@NgModule({
  declarations: [SiteShiftsComponent],
  imports: [
    CommonModule,
    MatIconModule,
    BrowserModule
  ]
})
export class SiteShiftsModule { }

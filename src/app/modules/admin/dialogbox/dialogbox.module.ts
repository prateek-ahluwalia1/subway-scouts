import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DialogboxComponent } from './dialogbox.component';
import {MatIconModule} from '@angular/material/icon';
import { BrowserModule } from '@angular/platform-browser';


@NgModule({
  declarations: [
    DialogboxComponent
  ],
  imports: [
    CommonModule,
    MatIconModule,
    BrowserModule
  ]
})
export class DialogboxModule { }

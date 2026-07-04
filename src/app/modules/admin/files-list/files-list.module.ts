import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { FilesListComponent } from './files-list.component';
import { AdminModule } from '../admin.module';


const exampleRoutes: Route[] = [
  {
    path: '',
    component: FilesListComponent
  }
]


@NgModule({
  declarations: [FilesListComponent],
  imports: [
    RouterModule.forChild(exampleRoutes), AdminModule
  ]
})
export class FilesListModule { }

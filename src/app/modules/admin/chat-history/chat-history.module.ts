import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { ChatHistoryComponent } from './chat-history.component';
import { MaterialModule } from 'app/shared/material.module';


const exampleRoutes: Route[]=[
  {
    path:'',
    component: ChatHistoryComponent
  }]
@NgModule({
  declarations: [ChatHistoryComponent],
  imports: [
  RouterModule.forChild(exampleRoutes), MaterialModule
  ]
})
export class ChatHistoryModule { }

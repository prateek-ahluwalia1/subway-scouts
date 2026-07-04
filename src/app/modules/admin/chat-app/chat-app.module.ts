import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ChatAppComponent } from './chat-app.component';
import { Route, RouterModule } from '@angular/router';
import { MaterialModule } from 'app/shared/material.module';
import { AdminComponent } from './admin/admin.component';
import { InternalUsersComponent } from './internal-users/internal-users.component';
import { CardComponent } from './card/card.component';
import { chatAppRoutes } from './chat-app.routing';
@NgModule({
  declarations: [ChatAppComponent, AdminComponent, InternalUsersComponent, CardComponent],
  imports: [
    CommonModule, RouterModule.forChild(chatAppRoutes), MaterialModule
  ]
})
export class ChatAppModule { }

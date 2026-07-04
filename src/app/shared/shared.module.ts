import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { TitleFilterPipe } from './title-filter.pipe';

@NgModule({
    declarations:[TitleFilterPipe],
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule
    ],
    exports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,TitleFilterPipe
    ]
})
export class SharedModule
{
}

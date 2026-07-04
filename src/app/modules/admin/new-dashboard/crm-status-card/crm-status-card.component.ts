import { Component, Input, ChangeDetectionStrategy, OnInit } from '@angular/core';

@Component({
  selector: 'app-crm-status-card',
  templateUrl: './crm-status-card.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CrmStatusCardComponent {

  @Input() title: string;
  @Input() value: number;
  @Input() percentage: number | null = null;
  @Input() percentageClass: string = '';
  @Input() routerLink: string = '';

}

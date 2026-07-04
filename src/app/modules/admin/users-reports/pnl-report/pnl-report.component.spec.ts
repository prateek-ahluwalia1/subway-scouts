import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PnlReportComponent } from './pnl-report.component';

describe('PnlReportComponent', () => {
  let component: PnlReportComponent;
  let fixture: ComponentFixture<PnlReportComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PnlReportComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PnlReportComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

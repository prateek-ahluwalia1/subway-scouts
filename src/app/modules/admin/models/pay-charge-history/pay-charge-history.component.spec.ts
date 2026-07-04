import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PayChargeHistoryComponent } from './pay-charge-history.component';

describe('PayChargeHistoryComponent', () => {
  let component: PayChargeHistoryComponent;
  let fixture: ComponentFixture<PayChargeHistoryComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PayChargeHistoryComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PayChargeHistoryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

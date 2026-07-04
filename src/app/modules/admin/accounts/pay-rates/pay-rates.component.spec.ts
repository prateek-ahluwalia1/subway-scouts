import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PayRatesComponent } from './pay-rates.component';

describe('PayRatesComponent', () => {
  let component: PayRatesComponent;
  let fixture: ComponentFixture<PayRatesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PayRatesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PayRatesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

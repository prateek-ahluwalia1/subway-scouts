import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ChargeRatesComponent } from './charge-rates.component';

describe('ChargeRatesComponent', () => {
  let component: ChargeRatesComponent;
  let fixture: ComponentFixture<ChargeRatesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ChargeRatesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ChargeRatesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AddChargeRateComponent } from './add-charge-rate.component';

describe('AddChargeRateComponent', () => {
  let component: AddChargeRateComponent;
  let fixture: ComponentFixture<AddChargeRateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AddChargeRateComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AddChargeRateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

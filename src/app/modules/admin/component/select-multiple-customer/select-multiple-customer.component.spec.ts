import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SelectMultipleCustomerComponent } from './select-multiple-customer.component';

describe('SelectMultipleCustomerComponent', () => {
  let component: SelectMultipleCustomerComponent;
  let fixture: ComponentFixture<SelectMultipleCustomerComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SelectMultipleCustomerComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SelectMultipleCustomerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

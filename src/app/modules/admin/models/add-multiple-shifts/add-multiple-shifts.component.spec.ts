import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AddMultipleShiftsComponent } from './add-multiple-shifts.component';

describe('AddMultipleShiftsComponent', () => {
  let component: AddMultipleShiftsComponent;
  let fixture: ComponentFixture<AddMultipleShiftsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AddMultipleShiftsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AddMultipleShiftsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CheckAvailableStaffComponent } from './check-available-staff.component';

describe('CheckAvailableStaffComponent', () => {
  let component: CheckAvailableStaffComponent;
  let fixture: ComponentFixture<CheckAvailableStaffComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CheckAvailableStaffComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CheckAvailableStaffComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

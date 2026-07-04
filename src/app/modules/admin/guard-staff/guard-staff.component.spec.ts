import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GuardStaffComponent } from './guard-staff.component';

describe('GuardStaffComponent', () => {
  let component: GuardStaffComponent;
  let fixture: ComponentFixture<GuardStaffComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ GuardStaffComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GuardStaffComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

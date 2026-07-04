import { ComponentFixture, TestBed } from '@angular/core/testing';

import { OnBoardingStaffComponent } from './on-boarding-staff.component';

describe('OnBoardingStaffComponent', () => {
  let component: OnBoardingStaffComponent;
  let fixture: ComponentFixture<OnBoardingStaffComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ OnBoardingStaffComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(OnBoardingStaffComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

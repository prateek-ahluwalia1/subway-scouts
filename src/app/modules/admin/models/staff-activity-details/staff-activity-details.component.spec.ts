import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StaffActivityDetailsComponent } from './staff-activity-details.component';

describe('StaffActivityDetailsComponent', () => {
  let component: StaffActivityDetailsComponent;
  let fixture: ComponentFixture<StaffActivityDetailsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ StaffActivityDetailsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StaffActivityDetailsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

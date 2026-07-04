import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LogUserActivitiesComponent } from './log-user-activities.component';

describe('LogUserActivitiesComponent', () => {
  let component: LogUserActivitiesComponent;
  let fixture: ComponentFixture<LogUserActivitiesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LogUserActivitiesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LogUserActivitiesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

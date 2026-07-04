import { ComponentFixture, TestBed } from '@angular/core/testing';

import { JobTrackerComponent } from './job-tracker.component';

describe('JobTrackerComponent', () => {
  let component: JobTrackerComponent;
  let fixture: ComponentFixture<JobTrackerComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ JobTrackerComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(JobTrackerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

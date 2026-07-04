import { ComponentFixture, TestBed } from '@angular/core/testing';

import { JobRosterComponent } from './job-roster.component';

describe('JobRosterComponent', () => {
  let component: JobRosterComponent;
  let fixture: ComponentFixture<JobRosterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ JobRosterComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(JobRosterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

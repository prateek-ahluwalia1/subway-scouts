import { ComponentFixture, TestBed } from '@angular/core/testing';

import { JobshiftActivityComponent } from './jobshift-activity.component';

describe('JobshiftActivityComponent', () => {
  let component: JobshiftActivityComponent;
  let fixture: ComponentFixture<JobshiftActivityComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ JobshiftActivityComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(JobshiftActivityComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

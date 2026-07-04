import { TestBed } from '@angular/core/testing';

import { JobTrackerService } from './job-tracker.service';

describe('JobTrackerService', () => {
  let service: JobTrackerService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(JobTrackerService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

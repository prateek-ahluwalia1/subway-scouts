import { TestBed } from '@angular/core/testing';

import { TestRosterService } from './test-roster.service';

describe('TestRosterService', () => {
  let service: TestRosterService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(TestRosterService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RunsheetJobRosterComponent } from './runsheet-job-roster.component';

describe('RunsheetJobRosterComponent', () => {
  let component: RunsheetJobRosterComponent;
  let fixture: ComponentFixture<RunsheetJobRosterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RunsheetJobRosterComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RunsheetJobRosterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

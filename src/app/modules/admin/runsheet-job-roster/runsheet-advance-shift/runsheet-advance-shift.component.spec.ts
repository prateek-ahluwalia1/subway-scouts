import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RunsheetAdvanceShiftComponent } from './runsheet-advance-shift.component';

describe('RunsheetAdvanceShiftComponent', () => {
  let component: RunsheetAdvanceShiftComponent;
  let fixture: ComponentFixture<RunsheetAdvanceShiftComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RunsheetAdvanceShiftComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RunsheetAdvanceShiftComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

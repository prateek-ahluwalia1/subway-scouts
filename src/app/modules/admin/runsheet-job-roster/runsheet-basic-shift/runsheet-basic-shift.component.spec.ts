import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RunsheetBasicShiftComponent } from './runsheet-basic-shift.component';

describe('RunsheetBasicShiftComponent', () => {
  let component: RunsheetBasicShiftComponent;
  let fixture: ComponentFixture<RunsheetBasicShiftComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RunsheetBasicShiftComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RunsheetBasicShiftComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RunsheetRosterComponent } from './runsheet-roster.component';

describe('RunsheetRosterComponent', () => {
  let component: RunsheetRosterComponent;
  let fixture: ComponentFixture<RunsheetRosterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RunsheetRosterComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RunsheetRosterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

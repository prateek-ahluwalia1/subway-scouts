import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RunsheetOneWeekComponent } from './runsheet-one-week.component';

describe('RunsheetOneWeekComponent', () => {
  let component: RunsheetOneWeekComponent;
  let fixture: ComponentFixture<RunsheetOneWeekComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RunsheetOneWeekComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RunsheetOneWeekComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

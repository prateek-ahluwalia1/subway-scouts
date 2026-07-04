import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RunsheetTwoWeekComponent } from './runsheet-two-week.component';

describe('RunsheetTwoWeekComponent', () => {
  let component: RunsheetTwoWeekComponent;
  let fixture: ComponentFixture<RunsheetTwoWeekComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RunsheetTwoWeekComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RunsheetTwoWeekComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

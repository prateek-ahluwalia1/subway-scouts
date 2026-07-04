import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RunsheetshiftActivityComponent } from './runsheetshift-activity.component';

describe('RunsheetshiftActivityComponent', () => {
  let component: RunsheetshiftActivityComponent;
  let fixture: ComponentFixture<RunsheetshiftActivityComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RunsheetshiftActivityComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RunsheetshiftActivityComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

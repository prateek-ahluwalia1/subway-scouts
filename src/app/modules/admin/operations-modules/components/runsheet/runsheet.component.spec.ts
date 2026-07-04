import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RunsheetComponent } from './runsheet.component';

describe('RunsheetComponent', () => {
  let component: RunsheetComponent;
  let fixture: ComponentFixture<RunsheetComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RunsheetComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RunsheetComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CopyDaywiseShiftsComponent } from './copy-daywise-shifts.component';

describe('CopyDaywiseShiftsComponent', () => {
  let component: CopyDaywiseShiftsComponent;
  let fixture: ComponentFixture<CopyDaywiseShiftsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CopyDaywiseShiftsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CopyDaywiseShiftsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

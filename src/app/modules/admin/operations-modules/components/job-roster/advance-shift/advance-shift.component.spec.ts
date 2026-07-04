import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdvanceShiftComponent } from './advance-shift.component';

describe('AdvanceShiftComponent', () => {
  let component: AdvanceShiftComponent;
  let fixture: ComponentFixture<AdvanceShiftComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AdvanceShiftComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AdvanceShiftComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

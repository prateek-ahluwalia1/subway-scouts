import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BasicShiftPopoverComponent } from './basic-shift-popover.component';

describe('BasicShiftPopoverComponent', () => {
  let component: BasicShiftPopoverComponent;
  let fixture: ComponentFixture<BasicShiftPopoverComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BasicShiftPopoverComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BasicShiftPopoverComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

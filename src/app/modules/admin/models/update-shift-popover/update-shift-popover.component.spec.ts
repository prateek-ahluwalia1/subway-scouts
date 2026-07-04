import { ComponentFixture, TestBed } from '@angular/core/testing';

import { UpdateShiftPopoverComponent } from './update-shift-popover.component';

describe('UpdateShiftPopoverComponent', () => {
  let component: UpdateShiftPopoverComponent;
  let fixture: ComponentFixture<UpdateShiftPopoverComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ UpdateShiftPopoverComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(UpdateShiftPopoverComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

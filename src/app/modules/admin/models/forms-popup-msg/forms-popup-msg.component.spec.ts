import { ComponentFixture, TestBed } from '@angular/core/testing';

import { FormsPopupMsgComponent } from './forms-popup-msg.component';

describe('FormsPopupMsgComponent', () => {
  let component: FormsPopupMsgComponent;
  let fixture: ComponentFixture<FormsPopupMsgComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ FormsPopupMsgComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(FormsPopupMsgComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

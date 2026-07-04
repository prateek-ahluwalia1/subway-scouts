import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StaffMessagesComponent } from './staff-messages.component';

describe('StaffMessagesComponent', () => {
  let component: StaffMessagesComponent;
  let fixture: ComponentFixture<StaffMessagesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ StaffMessagesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StaffMessagesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

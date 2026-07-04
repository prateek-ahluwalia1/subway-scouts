import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SMSPortalHistoryComponent } from './smsportal-history.component';

describe('SMSPortalHistoryComponent', () => {
  let component: SMSPortalHistoryComponent;
  let fixture: ComponentFixture<SMSPortalHistoryComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SMSPortalHistoryComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SMSPortalHistoryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

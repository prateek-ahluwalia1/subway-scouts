import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SmsPortalComponent } from './sms-portal.component';

describe('SmsPortalComponent', () => {
  let component: SmsPortalComponent;
  let fixture: ComponentFixture<SmsPortalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SmsPortalComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SmsPortalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

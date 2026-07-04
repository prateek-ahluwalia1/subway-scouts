import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EmailPortalComponent } from './email-portal.component';

describe('EmailPortalComponent', () => {
  let component: EmailPortalComponent;
  let fixture: ComponentFixture<EmailPortalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EmailPortalComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EmailPortalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

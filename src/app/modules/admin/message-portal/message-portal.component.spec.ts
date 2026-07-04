import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MessagePortalComponent } from './message-portal.component';

describe('MessagePortalComponent', () => {
  let component: MessagePortalComponent;
  let fixture: ComponentFixture<MessagePortalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ MessagePortalComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MessagePortalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

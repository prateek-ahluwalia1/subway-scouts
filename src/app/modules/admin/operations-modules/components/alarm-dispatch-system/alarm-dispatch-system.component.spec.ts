import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AlarmDispatchSystemComponent } from './alarm-dispatch-system.component';

describe('AlarmDispatchSystemComponent', () => {
  let component: AlarmDispatchSystemComponent;
  let fixture: ComponentFixture<AlarmDispatchSystemComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AlarmDispatchSystemComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AlarmDispatchSystemComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

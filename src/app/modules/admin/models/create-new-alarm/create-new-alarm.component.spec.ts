import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateNewAlarmComponent } from './create-new-alarm.component';

describe('CreateNewAlarmComponent', () => {
  let component: CreateNewAlarmComponent;
  let fixture: ComponentFixture<CreateNewAlarmComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CreateNewAlarmComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CreateNewAlarmComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

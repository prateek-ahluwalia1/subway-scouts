import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StaffLogsComponent } from './staff-logs.component';

describe('StaffLogsComponent', () => {
  let component: StaffLogsComponent;
  let fixture: ComponentFixture<StaffLogsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ StaffLogsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StaffLogsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

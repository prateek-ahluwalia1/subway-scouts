import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CallingHistoryComponent } from './calling-history.component';

describe('CallingHistoryComponent', () => {
  let component: CallingHistoryComponent;
  let fixture: ComponentFixture<CallingHistoryComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CallingHistoryComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CallingHistoryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

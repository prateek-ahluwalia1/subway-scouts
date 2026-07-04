import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LiveOperationComponent } from './live-operation.component';

describe('LiveOperationComponent', () => {
  let component: LiveOperationComponent;
  let fixture: ComponentFixture<LiveOperationComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LiveOperationComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LiveOperationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

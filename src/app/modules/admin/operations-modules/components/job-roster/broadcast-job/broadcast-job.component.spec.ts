import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BroadcastJobComponent } from './broadcast-job.component';

describe('BroadcastJobComponent', () => {
  let component: BroadcastJobComponent;
  let fixture: ComponentFixture<BroadcastJobComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BroadcastJobComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BroadcastJobComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

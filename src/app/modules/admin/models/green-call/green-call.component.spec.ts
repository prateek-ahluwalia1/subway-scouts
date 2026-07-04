import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GreenCallComponent } from './green-call.component';

describe('GreenCallComponent', () => {
  let component: GreenCallComponent;
  let fixture: ComponentFixture<GreenCallComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ GreenCallComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GreenCallComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

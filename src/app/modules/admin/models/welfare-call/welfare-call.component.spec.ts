import { ComponentFixture, TestBed } from '@angular/core/testing';

import { WelfareCallComponent } from './welfare-call.component';

describe('WelfareCallComponent', () => {
  let component: WelfareCallComponent;
  let fixture: ComponentFixture<WelfareCallComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ WelfareCallComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(WelfareCallComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

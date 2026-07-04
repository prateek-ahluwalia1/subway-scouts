import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EntoAdvanceDetailComponent } from './ento-advance-detail.component';

describe('EntoAdvanceDetailComponent', () => {
  let component: EntoAdvanceDetailComponent;
  let fixture: ComponentFixture<EntoAdvanceDetailComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EntoAdvanceDetailComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EntoAdvanceDetailComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

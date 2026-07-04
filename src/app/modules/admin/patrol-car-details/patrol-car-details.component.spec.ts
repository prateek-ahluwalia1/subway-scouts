import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PatrolCarDetailsComponent } from './patrol-car-details.component';

describe('PatrolCarDetailsComponent', () => {
  let component: PatrolCarDetailsComponent;
  let fixture: ComponentFixture<PatrolCarDetailsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PatrolCarDetailsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PatrolCarDetailsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

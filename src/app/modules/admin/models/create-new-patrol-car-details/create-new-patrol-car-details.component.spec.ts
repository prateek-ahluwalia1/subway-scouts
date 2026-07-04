import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateNewPatrolCarDetailsComponent } from './create-new-patrol-car-details.component';

describe('CreateNewPatrolCarDetailsComponent', () => {
  let component: CreateNewPatrolCarDetailsComponent;
  let fixture: ComponentFixture<CreateNewPatrolCarDetailsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CreateNewPatrolCarDetailsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CreateNewPatrolCarDetailsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

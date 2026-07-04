import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AddAwardRateComponent } from './add-award-rate.component';

describe('AddAwardRateComponent', () => {
  let component: AddAwardRateComponent;
  let fixture: ComponentFixture<AddAwardRateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AddAwardRateComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AddAwardRateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

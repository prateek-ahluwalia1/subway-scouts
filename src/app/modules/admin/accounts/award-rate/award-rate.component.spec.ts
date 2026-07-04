import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AwardRateComponent } from './award-rate.component';

describe('AwardRateComponent', () => {
  let component: AwardRateComponent;
  let fixture: ComponentFixture<AwardRateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AwardRateComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AwardRateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EntoDesignWeeklyComponent } from './ento-design-weekly.component';

describe('EntoDesignWeeklyComponent', () => {
  let component: EntoDesignWeeklyComponent;
  let fixture: ComponentFixture<EntoDesignWeeklyComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EntoDesignWeeklyComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EntoDesignWeeklyComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

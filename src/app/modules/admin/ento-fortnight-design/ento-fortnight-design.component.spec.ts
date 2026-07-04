import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EntoFortnightDesignComponent } from './ento-fortnight-design.component';

describe('EntoFortnightDesignComponent', () => {
  let component: EntoFortnightDesignComponent;
  let fixture: ComponentFixture<EntoFortnightDesignComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EntoFortnightDesignComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EntoFortnightDesignComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

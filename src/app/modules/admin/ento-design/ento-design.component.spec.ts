import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EntoDesignComponent } from './ento-design.component';

describe('EntoDesignComponent', () => {
  let component: EntoDesignComponent;
  let fixture: ComponentFixture<EntoDesignComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EntoDesignComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EntoDesignComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

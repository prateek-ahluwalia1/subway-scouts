import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SelectMultipleGuardsComponent } from './select-multiple-guards.component';

describe('SelectMultipleGuardsComponent', () => {
  let component: SelectMultipleGuardsComponent;
  let fixture: ComponentFixture<SelectMultipleGuardsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SelectMultipleGuardsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SelectMultipleGuardsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

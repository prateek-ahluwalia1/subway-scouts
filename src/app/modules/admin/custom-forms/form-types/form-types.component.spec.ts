import { ComponentFixture, TestBed } from '@angular/core/testing';

import { FormTypesComponent } from './form-types.component';

describe('FormTypesComponent', () => {
  let component: FormTypesComponent;
  let fixture: ComponentFixture<FormTypesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ FormTypesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(FormTypesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

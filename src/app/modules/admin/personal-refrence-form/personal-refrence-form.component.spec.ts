import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PersonalRefrenceFormComponent } from './personal-refrence-form.component';

describe('PersonalRefrenceFormComponent', () => {
  let component: PersonalRefrenceFormComponent;
  let fixture: ComponentFixture<PersonalRefrenceFormComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PersonalRefrenceFormComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PersonalRefrenceFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

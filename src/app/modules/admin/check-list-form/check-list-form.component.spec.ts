import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CheckListFormComponent } from './check-list-form.component';

describe('CheckListFormComponent', () => {
  let component: CheckListFormComponent;
  let fixture: ComponentFixture<CheckListFormComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CheckListFormComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CheckListFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

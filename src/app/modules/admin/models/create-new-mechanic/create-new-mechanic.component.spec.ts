import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateNewMechanicComponent } from './create-new-mechanic.component';

describe('CreateNewMechanicComponent', () => {
  let component: CreateNewMechanicComponent;
  let fixture: ComponentFixture<CreateNewMechanicComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CreateNewMechanicComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CreateNewMechanicComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

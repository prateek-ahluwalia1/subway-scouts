import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AddPayrateComponent } from './add-payrate.component';

describe('AddPayrateComponent', () => {
  let component: AddPayrateComponent;
  let fixture: ComponentFixture<AddPayrateComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AddPayrateComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AddPayrateComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

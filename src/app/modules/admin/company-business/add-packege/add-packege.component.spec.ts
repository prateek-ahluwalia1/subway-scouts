import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AddPackegeComponent } from './add-packege.component';

describe('AddPackegeComponent', () => {
  let component: AddPackegeComponent;
  let fixture: ComponentFixture<AddPackegeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AddPackegeComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AddPackegeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

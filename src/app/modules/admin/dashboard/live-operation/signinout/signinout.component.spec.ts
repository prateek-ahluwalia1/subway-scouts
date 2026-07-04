import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SigninoutComponent } from './signinout.component';

describe('SigninoutComponent', () => {
  let component: SigninoutComponent;
  let fixture: ComponentFixture<SigninoutComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SigninoutComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SigninoutComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

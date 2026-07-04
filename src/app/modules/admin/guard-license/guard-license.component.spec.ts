import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GuardLicenseComponent } from './guard-license.component';

describe('GuardLicenseComponent', () => {
  let component: GuardLicenseComponent;
  let fixture: ComponentFixture<GuardLicenseComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ GuardLicenseComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(GuardLicenseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

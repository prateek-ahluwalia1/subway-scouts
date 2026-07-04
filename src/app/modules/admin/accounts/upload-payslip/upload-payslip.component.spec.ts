import { ComponentFixture, TestBed } from '@angular/core/testing';

import { UploadPayslipComponent } from './upload-payslip.component';

describe('UploadPayslipComponent', () => {
  let component: UploadPayslipComponent;
  let fixture: ComponentFixture<UploadPayslipComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ UploadPayslipComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(UploadPayslipComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

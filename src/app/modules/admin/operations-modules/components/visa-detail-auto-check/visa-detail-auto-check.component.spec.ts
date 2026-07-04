import { ComponentFixture, TestBed } from '@angular/core/testing';

import { VisaDetailAutoCheckComponent } from './visa-detail-auto-check.component';

describe('VisaDetailAutoCheckComponent', () => {
  let component: VisaDetailAutoCheckComponent;
  let fixture: ComponentFixture<VisaDetailAutoCheckComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ VisaDetailAutoCheckComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(VisaDetailAutoCheckComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { ComponentFixture, TestBed } from '@angular/core/testing';

import { VisaDetailManualCheckComponent } from './visa-detail-manual-check.component';

describe('VisaDetailManualCheckComponent', () => {
  let component: VisaDetailManualCheckComponent;
  let fixture: ComponentFixture<VisaDetailManualCheckComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ VisaDetailManualCheckComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(VisaDetailManualCheckComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

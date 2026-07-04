import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CopyShiftBulkComponent } from './copy-shift-bulk.component';

describe('CopyShiftBulkComponent', () => {
  let component: CopyShiftBulkComponent;
  let fixture: ComponentFixture<CopyShiftBulkComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CopyShiftBulkComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CopyShiftBulkComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

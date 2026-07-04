import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StaffDocumentsDetailComponent } from './staff-documents-detail.component';

describe('StaffDocumentsDetailComponent', () => {
  let component: StaffDocumentsDetailComponent;
  let fixture: ComponentFixture<StaffDocumentsDetailComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ StaffDocumentsDetailComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StaffDocumentsDetailComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

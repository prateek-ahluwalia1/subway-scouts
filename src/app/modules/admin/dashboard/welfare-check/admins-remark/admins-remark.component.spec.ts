import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminsRemarkComponent } from './admins-remark.component';

describe('AdminsRemarkComponent', () => {
  let component: AdminsRemarkComponent;
  let fixture: ComponentFixture<AdminsRemarkComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AdminsRemarkComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AdminsRemarkComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

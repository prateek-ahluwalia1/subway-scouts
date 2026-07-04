import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CopyDaySiteComponent } from './copy-day-site.component';

describe('CopyDaySiteComponent', () => {
  let component: CopyDaySiteComponent;
  let fixture: ComponentFixture<CopyDaySiteComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CopyDaySiteComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CopyDaySiteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

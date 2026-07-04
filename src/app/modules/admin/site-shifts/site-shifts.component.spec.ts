import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SiteShiftsComponent } from './site-shifts.component';

describe('SiteShiftsComponent', () => {
  let component: SiteShiftsComponent;
  let fixture: ComponentFixture<SiteShiftsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SiteShiftsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SiteShiftsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

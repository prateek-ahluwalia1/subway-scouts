import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RosterFourComponent } from './roster-four.component';

describe('RosterFourComponent', () => {
  let component: RosterFourComponent;
  let fixture: ComponentFixture<RosterFourComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RosterFourComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RosterFourComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

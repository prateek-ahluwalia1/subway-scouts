import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RosterThreeComponent } from './roster-three.component';

describe('RosterThreeComponent', () => {
  let component: RosterThreeComponent;
  let fixture: ComponentFixture<RosterThreeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RosterThreeComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RosterThreeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

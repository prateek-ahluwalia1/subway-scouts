import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RosterTypesComponent } from './roster-types.component';

describe('RosterTypesComponent', () => {
  let component: RosterTypesComponent;
  let fixture: ComponentFixture<RosterTypesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RosterTypesComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RosterTypesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

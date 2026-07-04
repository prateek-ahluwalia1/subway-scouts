import { ComponentFixture, TestBed } from '@angular/core/testing';

import { FortnightSelectionComponent } from './fortnight-selection.component';

describe('FortnightSelectionComponent', () => {
  let component: FortnightSelectionComponent;
  let fixture: ComponentFixture<FortnightSelectionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ FortnightSelectionComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(FortnightSelectionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

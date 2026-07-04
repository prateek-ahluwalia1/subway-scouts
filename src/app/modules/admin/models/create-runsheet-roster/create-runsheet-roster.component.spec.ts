import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateRunsheetRosterComponent } from './create-runsheet-roster.component';

describe('CreateRunsheetRosterComponent', () => {
  let component: CreateRunsheetRosterComponent;
  let fixture: ComponentFixture<CreateRunsheetRosterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CreateRunsheetRosterComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CreateRunsheetRosterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

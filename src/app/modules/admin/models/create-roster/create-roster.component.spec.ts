import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateRosterComponent } from './create-roster.component';

describe('CreateRosterComponent', () => {
  let component: CreateRosterComponent;
  let fixture: ComponentFixture<CreateRosterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CreateRosterComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CreateRosterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

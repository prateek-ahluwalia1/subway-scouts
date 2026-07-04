import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateNewRunsheetComponent } from './create-new-runsheet.component';

describe('CreateNewRunsheetComponent', () => {
  let component: CreateNewRunsheetComponent;
  let fixture: ComponentFixture<CreateNewRunsheetComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CreateNewRunsheetComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CreateNewRunsheetComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

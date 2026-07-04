import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SubContractorRemindersComponent } from './sub-contractor-reminders.component';

describe('SubContractorRemindersComponent', () => {
  let component: SubContractorRemindersComponent;
  let fixture: ComponentFixture<SubContractorRemindersComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SubContractorRemindersComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SubContractorRemindersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

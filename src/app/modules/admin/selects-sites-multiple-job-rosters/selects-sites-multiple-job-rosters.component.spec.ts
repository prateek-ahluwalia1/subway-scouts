import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SelectsSitesMultipleJobRostersComponent } from './selects-sites-multiple-job-rosters.component';

describe('SelectsSitesMultipleJobRostersComponent', () => {
  let component: SelectsSitesMultipleJobRostersComponent;
  let fixture: ComponentFixture<SelectsSitesMultipleJobRostersComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SelectsSitesMultipleJobRostersComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SelectsSitesMultipleJobRostersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

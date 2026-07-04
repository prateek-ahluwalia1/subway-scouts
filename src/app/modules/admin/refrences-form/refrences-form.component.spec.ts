import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RefrencesFormComponent } from './refrences-form.component';

describe('RefrencesFormComponent', () => {
  let component: RefrencesFormComponent;
  let fixture: ComponentFixture<RefrencesFormComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ RefrencesFormComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RefrencesFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

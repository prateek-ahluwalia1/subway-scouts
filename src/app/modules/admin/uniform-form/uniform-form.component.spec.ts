import { ComponentFixture, TestBed } from '@angular/core/testing';

import { UniformFormComponent } from './uniform-form.component';

describe('UniformFormComponent', () => {
  let component: UniformFormComponent;
  let fixture: ComponentFixture<UniformFormComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ UniformFormComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(UniformFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

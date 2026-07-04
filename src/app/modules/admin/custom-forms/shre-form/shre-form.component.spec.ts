import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ShreFormComponent } from './shre-form.component';

describe('ShreFormComponent', () => {
  let component: ShreFormComponent;
  let fixture: ComponentFixture<ShreFormComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ShreFormComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ShreFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

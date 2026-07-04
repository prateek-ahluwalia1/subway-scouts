import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DetailModelComponent } from './detail-model.component';

describe('DetailModelComponent', () => {
  let component: DetailModelComponent;
  let fixture: ComponentFixture<DetailModelComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ DetailModelComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DetailModelComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

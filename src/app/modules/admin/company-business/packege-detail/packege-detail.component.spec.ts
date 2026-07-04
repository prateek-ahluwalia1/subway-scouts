import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PackegeDetailComponent } from './packege-detail.component';

describe('PackegeDetailComponent', () => {
  let component: PackegeDetailComponent;
  let fixture: ComponentFixture<PackegeDetailComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PackegeDetailComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PackegeDetailComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

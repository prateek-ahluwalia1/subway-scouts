import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PotetialNewGuardComponent } from './potetial-new-guard.component';

describe('PotetialNewGuardComponent', () => {
  let component: PotetialNewGuardComponent;
  let fixture: ComponentFixture<PotetialNewGuardComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PotetialNewGuardComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PotetialNewGuardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PhSettingComponent } from './ph-setting.component';

describe('PhSettingComponent', () => {
  let component: PhSettingComponent;
  let fixture: ComponentFixture<PhSettingComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ PhSettingComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PhSettingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

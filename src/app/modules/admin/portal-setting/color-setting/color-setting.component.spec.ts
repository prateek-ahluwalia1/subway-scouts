import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ColorSettingComponent } from './color-setting.component';

describe('ColorSettingComponent', () => {
  let component: ColorSettingComponent;
  let fixture: ComponentFixture<ColorSettingComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ColorSettingComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ColorSettingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

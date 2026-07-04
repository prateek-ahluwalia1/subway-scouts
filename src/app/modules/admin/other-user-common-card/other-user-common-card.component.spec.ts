import { ComponentFixture, TestBed } from '@angular/core/testing';

import { OtherUserCommonCardComponent } from './other-user-common-card.component';

describe('OtherUserCommonCardComponent', () => {
  let component: OtherUserCommonCardComponent;
  let fixture: ComponentFixture<OtherUserCommonCardComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ OtherUserCommonCardComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(OtherUserCommonCardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

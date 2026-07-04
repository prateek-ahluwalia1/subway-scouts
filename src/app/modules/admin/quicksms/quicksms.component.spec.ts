import { ComponentFixture, TestBed } from '@angular/core/testing';

import { QuicksmsComponent } from './quicksms.component';

describe('QuicksmsComponent', () => {
  let component: QuicksmsComponent;
  let fixture: ComponentFixture<QuicksmsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ QuicksmsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(QuicksmsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

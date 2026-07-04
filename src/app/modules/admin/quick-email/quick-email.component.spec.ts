import { ComponentFixture, TestBed } from '@angular/core/testing';

import { QuickEmailComponent } from './quick-email.component';

describe('QuickEmailComponent', () => {
  let component: QuickEmailComponent;
  let fixture: ComponentFixture<QuickEmailComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ QuickEmailComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(QuickEmailComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

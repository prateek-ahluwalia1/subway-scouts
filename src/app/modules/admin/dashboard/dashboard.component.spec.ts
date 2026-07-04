import { ComponentFixture, TestBed } from '@angular/core/testing';
import { DashboardComponent } from './dashboard.component';
import { By } from '@angular/platform-browser';
import { NO_ERRORS_SCHEMA } from '@angular/core';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { ServiceService } from 'app/services/service.service';
import { GlobalVariable } from 'app/shared/global';
import { ToasterService } from '../models/toaster/toaster.service';

describe('DashboardComponent', () => {
  let component: DashboardComponent;
  let fixture: ComponentFixture<DashboardComponent>;
  let mockGlobalVariable: Partial<GlobalVariable>;

  beforeEach(async () => {
    mockGlobalVariable = {
      // Provide a mock admin object with admin_id
      admin: {
        admin_id: 12345
      }
    };

    await TestBed.configureTestingModule({
      declarations: [DashboardComponent],
      imports: [HttpClientTestingModule],
      providers: [
        ServiceService,ToasterService,
        { provide: GlobalVariable, useValue: mockGlobalVariable } // Use the mock GlobalVariable
      ],
      schemas: [NO_ERRORS_SCHEMA]
    }).compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(DashboardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create the dashboard component', () => {
    expect(component).toBeTruthy();
  });

  it('should render the correct number of staff cards', () => {
    component.total_staff = 100;
    component.casualTimeStaff = 25;
    component.partTimeStaff = 25;
    component.fullTimeStaff = 50;

    fixture.detectChanges();

    const staffCards = fixture.debugElement.queryAll(By.css('.widget-chart2'));
    expect(staffCards.length).toBe(4);
  });

  it('should display the correct staff numbers', () => {
    component.total_staff = 100;
    component.casualTimeStaff = 25;
    component.partTimeStaff = 25;
    component.fullTimeStaff = 50;

    fixture.detectChanges();

    const totalStaffElement = fixture.debugElement.query(By.css('.widget-numbers span'));
    expect(totalStaffElement.nativeElement.textContent).toContain('100');
  });

  it('should display the chart with correct categories', () => {
    component.ChartLineOptions = {
      series: [
        { name: 'Active Sites', data: [1, 2, 3] },
        { name: 'Active Staff', data: [4, 5, 6] }
      ],
      xaxis: { categories: ['Jan', 'Feb', 'Mar'] },
    };

    fixture.detectChanges();

    const chartElement = fixture.debugElement.query(By.css('apx-chart'));
    expect(chartElement).toBeTruthy();
  });

  it('should call toggleFullScreen method when button is clicked', () => {
    spyOn(component, 'toggleFullScreen');

    const button = fixture.debugElement.query(By.css('.btn')).nativeElement;
    button.click();

    expect(component.toggleFullScreen).toHaveBeenCalled();
  });

  // Additional tests...
});

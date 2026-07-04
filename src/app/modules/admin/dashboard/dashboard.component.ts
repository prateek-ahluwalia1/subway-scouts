import { ChangeDetectorRef, Component, OnInit, OnDestroy, ViewChild, ChangeDetectionStrategy, Inject } from '@angular/core';
import { trigger, transition, style, animate, query, stagger } from '@angular/animations';
import { ApexGrid, ApexMarkers, ApexOptions, ApexTitleSubtitle } from 'ng-apexcharts';
import { ServiceService } from 'app/services/service.service';
import { GlobalVariable } from 'app/shared/global';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

import {
  ApexAxisChartSeries,
  ApexChart,
  ChartComponent,
  ApexDataLabels,
  ApexPlotOptions,
  ApexYAxis,
  ApexLegend,
  ApexStroke,
  ApexXAxis,
  ApexFill,
  ApexTooltip
} from "ng-apexcharts";
import { Subject, filter, interval, takeUntil, timer } from 'rxjs';
import { ToasterService } from '../models/toaster/toaster.service';

export type ActiveAndWorkingStff = {
  series: ApexAxisChartSeries;
  chart: ApexChart;
  dataLabels: ApexDataLabels;
  plotOptions: ApexPlotOptions;
  yaxis: ApexYAxis;
  xaxis: ApexXAxis;
  fill: ApexFill;
  tooltip: ApexTooltip;
  stroke: ApexStroke;
  legend: ApexLegend;
};

export type ChartColumnOptions = {
  series: ApexAxisChartSeries;
  chart: ApexChart;
  dataLabels: ApexDataLabels;
  plotOptions: ApexPlotOptions;
  yaxis: ApexYAxis;
  xaxis: ApexXAxis;
  fill: ApexFill;
  tooltip: ApexTooltip;
  stroke: ApexStroke;
  legend: ApexLegend;
};

export type ChartLineOptions = {
  series: ApexAxisChartSeries;
  chart: ApexChart;
  xaxis: ApexXAxis;
  stroke: ApexStroke;
  dataLabels: ApexDataLabels;
  markers: ApexMarkers;
  colors: string[];
  yaxis: ApexYAxis;
  grid: ApexGrid;
  legend: ApexLegend;
  title: ApexTitleSubtitle;
};

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['../new-dashboard/new-dashboard.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('700ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('700ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ],
})

export class DashboardComponent implements OnInit, OnDestroy {

  routeId: any;
  currentStaff
  activeStaff
  @ViewChild("activeAndworkingStaff") chart: ChartComponent;
  public ActiveAndWorkingStff: Partial<ActiveAndWorkingStff>;
  @ViewChild('staffAnalytics') staffAnalytics: ChartComponent;
  public ChartColumnOptions: Partial<ChartColumnOptions>;
  @ViewChild('chartColumn') chartColumn: ChartComponent;
  @ViewChild("chart-col-2") chartCol2: ChartComponent;
  public ChartLineOptions: Partial<ChartLineOptions>;
  @ViewChild('chartLine') chartLine: ChartComponent;
  upcoming_jobs: any;
  activeSites: any;
  activeJobCount: any;
  completedJobCount: any;
  missedJobCount: any;
  ongoing: any;
  casualTimeStaff: any;
  fullTimeStaff: any;
  partTimeStaff: any;
  total_staff: any;
  chartGithubIssues: ApexOptions = null;
  apiInterval: any;
  apiInterval1: any;
  fullscreenView = false;
  @ViewChild('fullScreen') divRef;
  graph: any;
  private _unsubscribeAll: Subject<any> = new Subject<any>();

  constructor(
    private serivce: ServiceService,
    private globals: GlobalVariable,
    private trackAdmin: TrackAdminActivityService,
    private serivce1: ServiceService,
    private _changeDetectorRef: ChangeDetectorRef,
    @Inject(ToasterService) private toasterService
  ) {
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
    this.callApisSequentially()
      .catch((error) => {
        console.error('An error occurred:', error);
      });

    this.scheduleApiCall();
    this.serivce1.graphData$
      .pipe(filter(res => res),
        takeUntil(this._unsubscribeAll))
      .subscribe((res: any) => {
        this.appUsage(res?.appUsages.data)
        this.initCharCol2(res?.dashboardCustomerSitesCount)
        this.initBarVerticle(res?.getBeforeSixMonthsShiftsAndHoursCount.data)
        // active and working staff
        this.currentStaff = res?.stafCount.currentStaff
        this.activeStaff = res?.stafCount.activeStaff
        const activeStaffData = [];
        const workingStaffData = [];

        res?.stafCount.graph.forEach(item => {
          activeStaffData.push(item.active_guard);
          workingStaffData.push(item.current_guard);
        });
        this.ActiveAndWorkingStff = {
          series: [
            {
              name: "Active Staff",
              data: activeStaffData
            },
            {
              name: "Working Staff",
              data: workingStaffData
            },
          ],
          chart: {
            type: "bar",
            // height: 350,
            height: this.isFullScreen ? 740 : 475,
            toolbar: {
              show: false,
            },
          },
          plotOptions: {
            bar: {
              horizontal: false,
              columnWidth: "55%"
            }
          },
          dataLabels: {
            enabled: false
          },
          stroke: {
            show: true,
            width: 2,
            colors: ["transparent"]
          },
          xaxis: {
            categories: this.getShortDayNames()
          },
          fill: {
            opacity: 1
          },
          tooltip: {
            y: {
              formatter: function (val) {
                return val.toString();
              }
            }
          }
        };
        // jobs count
        this.upcoming_jobs = res?.dashboardJobsCount.upcoming_jobs;
        this.activeSites = res?.dashboardJobsCount.activeSites;
        this.activeJobCount = res?.dashboardJobsCount.activeJobCount;
        this.completedJobCount = res?.dashboardJobsCount.completedJobCount;
        this.missedJobCount = res?.dashboardJobsCount.missedJobCount;
        this.ongoing = res?.dashboardJobsCount.ongoing;
        // staff count
        this.casualTimeStaff = res?.getStaffByStaffType.casualTimeStaff
        this.fullTimeStaff = res?.getStaffByStaffType.fullTimeStaff
        this.partTimeStaff = res?.getStaffByStaffType.partTimeStaff
        this.total_staff = res?.getStaffByStaffType.total_staff
        this._changeDetectorRef.markForCheck();
      });

    this.apiInterval1 = setInterval(async () => {
      this.publishUnpublish(this.globals.start, this.globals.end);
    }, 45000);


    this.crmNotification()

  }

  async scheduleApiCall() {
    // Then schedule it to run every 30 seconds
    this.apiInterval = setInterval(async () => {
      try {
        await this.callApisSequentially();
      } catch (error) {
        console.error('An error occurred:', error);
      }
    }, 40000);
  }

  async callApisSequentially() {
    try {
      await this.getGraphData();
      await this.serivce.liveDashboardData().subscribe();
      await this.staffConfirmation(this.globals.start, this.globals.end);

    } catch (error) {
      throw error;
    }
  }

  getGraphData() {
    this.serivce1.graphData().subscribe()
  }

  // Active and working staff chart
  getShortDayNames() {
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const shortDayNames = [];
    const today = new Date();

    for (let i = 6; i >= 0; i--) {
      const date = new Date(today);
      date.setDate(today.getDate() - i);
      const dayIndex = date.getDay();
      shortDayNames.push(dayNames[dayIndex]);
    }

    return shortDayNames;
  }


  initBarVerticle(data) {
    const currentDate = new Date();
    const currentMonth = currentDate.getMonth();
    const months = [];

    for (let i = 5; i >= 0; i--) {
      const monthIndex = (currentMonth - i + 12) % 12; // Handle wrap-around for previous year
      const monthName = this.getMonthName(monthIndex);
      months.push(monthName);
    }
    const reversedTotalHours = data?.total_hours.slice().reverse();
    const reversedTotalShifts = data?.total_shifts.slice().reverse();
    this.ChartColumnOptions = {
      series: [
        {
          name: 'Total Shifts',
          data: reversedTotalShifts,
        },
        {
          name: 'Total Hours',
          data: reversedTotalHours,
        },
      ],
      chart: {
        toolbar: {
          show: false,
        },
        type: 'bar',
        height: this.isFullScreen ? 740 : 350,
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '55%',
        },
      },
      dataLabels: {
        enabled: false,
      },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent'],
      },
      xaxis: {
        categories: months,
      },
      fill: {
        opacity: 1,
      },
    };
  }

  getMonthName(month: number): string {
    const monthNames = [
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    return monthNames[month];
  }

  initCharCol2(data) {
    this.ChartLineOptions = {
      series: [
        {
          name: "Active Sites",
          data: data.sitesCount
        },
        {
          name: "Active Staff",
          data: data.staffCount
        }
      ],
      chart: {
        // height: 350,
        height: this.isFullScreen ? 740 : 350,
        type: "line",
        dropShadow: {
          enabled: true,
          color: "#000",
          top: 18,
          left: 7,
          blur: 10,
          opacity: 0.2
        },
        toolbar: {
          show: false
        }
      },
      colors: ["#77B6EA", "#545454"],
      dataLabels: {
        enabled: true
      },
      stroke: {
        curve: "smooth"
      },

      grid: {
        borderColor: "#e7e7e7",
        row: {
          colors: ["#f3f3f3", "transparent"], // takes an array which will be repeated on columns
          opacity: 0.5
        }
      },
      markers: {
        size: 1
      },
      xaxis: {
        categories: data.months,
      },
      legend: {
        position: "top",
        horizontalAlign: "right",
        floating: true,
        offsetY: -25,
        offsetX: -5
      }
    };
    this._changeDetectorRef.markForCheck()

  }

  // App Usage grpah
  appUsage(data) {
    const labels = data.map(item => {
      const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
      return monthNames[parseInt(item.month, 10) - 1];
    }).reverse();

    const totalJobsData = data.map(item => item.total_jobs).reverse();
    const completedJobsData = data.map(item => item.complete_jobs).reverse();

    this.chartGithubIssues = {
      chart: {
        fontFamily: 'inherit',
        foreColor: 'inherit',
        height: '100%',
        type: 'line',
        toolbar: {
          show: false
        },
        zoom: {
          enabled: false
        }
      },
      title: {
        text: 'App Usage Over Time',
        align: 'left',
        style: {
          fontSize: '15px',
          fontWeight: 'bold',
          color: 'inherit'
        }
      },
      colors: ['#64748B', '#94A3B8'],
      dataLabels: {
        enabled: true,
        enabledOnSeries: [0, 1],
        background: {
          borderWidth: 0
        }
      },
      grid: {
        borderColor: 'var(--fuse-border)'
      },
      labels: labels,
      legend: {
        show: false
      },
      series: [
        {
          name: 'Total Jobs',
          type: 'line',
          data: totalJobsData
        },
        {
          name: 'Completed Jobs',
          type: 'line',
          data: completedJobsData
        }
      ],
      states: {
        hover: {
          filter: {
            type: 'darken',
            value: 0.75
          }
        }
      },
      stroke: {
        width: [3, 3]
      },
      tooltip: {
        followCursor: true,
        theme: 'dark'
      },
      xaxis: {
        axisBorder: {
          show: false
        },
        axisTicks: {
          color: 'var(--fuse-border)'
        },
        labels: {
          style: {
            colors: 'var(--fuse-text-secondary)'
          }
        },
        tooltip: {
          enabled: false
        }
      },
      yaxis: {
        labels: {
          offsetX: -16,
          style: {
            colors: 'var(--fuse-text-secondary)'
          }
        }
      }
    };
  }


  ngOnDestroy() {
    clearInterval(this.apiInterval);
    clearInterval(this.apiInterval1);
    this.trackAdmin.storeActivity('Dashboard', 'Exit Dashboard Page', this.routeId).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Dashboard Page', 'Enter in Dashboard Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }





  isFullScreen = false

  toggleFullScreen() {

    const fullscreenDiv = document.getElementById('fullscreenDiv');
    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      fullscreenDiv.requestFullscreen();
    }
    this.isFullScreen = !this.isFullScreen;
    if (this.chartColumn) {
      this.chartColumn.updateOptions({
        chart: {
          height: this.isFullScreen ? 740 : 350,
        },
      });
    }
  }

  toggleScreen2() {

    const fullscreenDiv2 = document.getElementById('fullscreenDiv2');

    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      fullscreenDiv2.requestFullscreen();
    }

    this.isFullScreen = !this.isFullScreen;
    if (this.chartLine) {
      this.chartLine.updateOptions({
        chart: {
          height: this.isFullScreen ? 740 : 350,
        },
      });
    }
  }

  toggleScreen3() {
    const fullscreenDiv3 = document.getElementById('fullscreenDiv3');

    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      fullscreenDiv3.requestFullscreen();
    }

    this.isFullScreen = !this.isFullScreen;
    if (this.staffAnalytics) {
      this.staffAnalytics.updateOptions({
        chart: {
          height: this.isFullScreen ? 650 : 350,
        },
      });
    }
  }
  toggleScreen4() {
    const fullscreenDiv4 = document.getElementById('fullscreenDiv4');

    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      fullscreenDiv4.requestFullscreen();
    }

    this.isFullScreen = !this.isFullScreen;
  }

  toggleFullScreen5() {
    const fullscreenDiv5 = document.getElementById('fullscreenDiv5');

    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      fullscreenDiv5.requestFullscreen();
    }

    this.isFullScreen = !this.isFullScreen;
  }


  dateFromPublishUnpublish(evet) {
    this.publishUnpublish(this.globals.start, this.globals.end)
  }
  publishUnpublish(start, end) {
    let data = {
      start: start,
      end: end
    }
    this.serivce.publishUnpublish(data).subscribe()
  }

  staffConfirmation(start, end) {
    let data = {
      start: start,
      end: end
    }
    this.serivce.getConfirm(data).subscribe()
  }


  crmNotification() {
    this.serivce.crmNotification(this.globals.admin.admin_id).subscribe((res) => {
      if (res.success && res.unseen) {
        this.toasterService.dispatchToaster('You are assigned to a new lead', 'Crm  Notification');
        let toasterWaitFor$ = timer(10000);
        let toasterInterval$ = interval(1000).pipe(
          takeUntil(toasterWaitFor$)
        );
        let subscription = toasterInterval$.subscribe(
          res => console.log('Toaster wait for ', res),
          err => console.log('Toaster wait for ', err),
          () => {
            this.toasterService.dismissToaster()
          }
        );
      }


    })
  }


  toggleScreen6() {
    const fullscreenDiv4 = document.getElementById('fullscreenDiv6');

    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      fullscreenDiv4.requestFullscreen();
    }

    this.isFullScreen = !this.isFullScreen;
  }

}

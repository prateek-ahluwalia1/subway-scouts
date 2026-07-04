import { Component, OnInit, ViewChild, AfterViewInit, ViewEncapsulation, ElementRef } from '@angular/core';
import {
  ChartComponent, ApexAxisChartSeries, ApexChart, ApexFill,
  ApexYAxis, ApexTooltip, ApexTitleSubtitle, ApexXAxis, ApexStroke, ApexDataLabels, ApexGrid, ApexLegend, ApexNonAxisChartSeries, ApexResponsive, ApexMarkers
} from "ng-apexcharts";
import Swiper from "swiper";
import { trigger, transition, style, animate, query, stagger } from '@angular/animations';
import { AgentService } from 'app/services/crm/agent.service';
import { ServiceService } from 'app/services/service.service';
import { PageEvent } from '@angular/material/paginator';
import { Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GlobalVariable } from 'app/shared/global';
import { DateAdapter } from '@angular/material/core';
import { DashbardReportPdfService } from './dashboard-report-pdf.service';
import html2canvas from 'html2canvas';
import { NgxSpinnerService } from 'ngx-spinner';

export type chartOptionsNegativeLeads = {
  series: ApexAxisChartSeries;
  chart: ApexChart;
  xaxis: ApexXAxis;
  stroke: ApexStroke;
  tooltip: ApexTooltip;
  dataLabels: ApexDataLabels;
  yaxis: ApexYAxis;
  fill: ApexFill;
  title: ApexTitleSubtitle;
  grid: ApexGrid;
};


export type ChartTopLossOptions = {
  series: ApexAxisChartSeries;
  chart: ApexChart;
  markers: ApexMarkers;
  stroke: ApexStroke;
  dataLabels: ApexDataLabels;
  title: ApexTitleSubtitle;
  xaxis: ApexXAxis;
  yaxis: ApexYAxis;

};
export type ChartOptions = {
  series: ApexNonAxisChartSeries;
  chart: ApexChart;
  responsive: ApexResponsive[];
  labels: any;
  title: ApexTitleSubtitle;
  legend: ApexLegend;
  dataLabels?: {
    enabled?: boolean;
    formatter?: (val: number, opts: any) => string | number;
  };
  tooltip?: {
    y?: {
      formatter: (val: number, opts: any) => string | number;
    }
  };
};

@Component({
  selector: 'app-new-dashboard',
  templateUrl: './new-dashboard.component.html',
  styleUrls: ['./new-dashboard.component.css'],
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
  encapsulation: ViewEncapsulation.None // Add this line
})
export class NewDashboardComponent implements OnInit, AfterViewInit {
  max_leads: number = 100;
  routeId
  totalCountCurrentMonth
  percentageChange
  dealClosed
  contactInFuture
  grossAmount
  expectedLoss
  leadStatus = [
    { name: 'All', active: true },
    { name: 'Attempted to Contact', active: false },
    { name: 'Contact in Future', active: false },
    { name: 'Contacted', active: false },
    { name: 'Junk Lead', active: false },
    { name: 'Lost Lead', active: false },
    { name: 'Not Contacted', active: false },
    { name: 'Pre-Qualified', active: false },
    { name: 'Won', active: false },
    { name: 'Qualified', active: false },
    { name: 'Not Qualified', active: false },
  ];
  length: number;
  pageSize: number = 10;
  pageIndex: number = 0;
  pageSizeOptions: number[] = [5, 10, 25];
  pageEvent: PageEvent;
  hidePageSize = false;
  showPageSizeOptions = true;

  all_leads
  contacted_leads
  lost_leads
  won_leads
  @ViewChild('owlCarousel') owlCarousel: any;
  crmCustomer: any = []
  @ViewChild('chartNegative') chartNegative: ChartComponent;
  @ViewChild('pieChartGraph', { static: false }) pieChartGraph: ElementRef;
  @ViewChild('pieChart') pieChart: ChartComponent;
  public chartOptionsNegativeLeads: Partial<chartOptionsNegativeLeads>;

  activeTab = 'first'

  filteredCrmCustomer: any[] = []
  isLoading: boolean = true;
  monthlySale: any;
  progressData: { value: number; label: string; isPercentage: boolean; isNegative: boolean; arrowDirection: 'up' | 'down' }[] = [];

  private apiInterval: any;

  toggleTable: boolean = false;

  @ViewChild("chart-top-loss") chart: ChartComponent;
  // @ViewChild('swiperContainer') swiperContainer: any;

  @ViewChild('swiperContainer') swiperContainer: ElementRef;
  private swiper: Swiper;


  public chartTopLossOptions: Partial<ChartTopLossOptions>;
  public chartOptions1: Partial<ChartOptions>;

  months = [
    { name: 'January', value: 1 },
    { name: 'February', value: 2 },
    { name: 'March', value: 3 },
    { name: 'April', value: 4 },
    { name: 'May', value: 5 },
    { name: 'June', value: 6 },
    { name: 'July', value: 7 },
    { name: 'August', value: 8 },
    { name: 'September', value: 9 },
    { name: 'October', value: 10 },
    { name: 'November', value: 11 },
    { name: 'December', value: 12 },
  ];
  selectedMonth: number | null = null;
  currentMonth: number = new Date().getMonth() + 1;

  adminData: any;
  adminId: any;
  adminType: any;


  @ViewChild('chartContainer', { static: false }) chartContainer: ElementRef;
  constructor(
    private service: AgentService,
    private service1: ServiceService, private global: GlobalVariable,
    private router: Router, private trackAdmin: TrackAdminActivityService,
    public dateAdapter: DateAdapter<Date>,
    private _dashboardReportPdf: DashbardReportPdfService,
    private spinner: NgxSpinnerService
  ) {
    this.dateAdapter.setLocale("en-AU");
    this.selectedMonth = this.currentMonth
    this.global.showCrmTab = true
    this.global.selectTabCrm = 'dashboard'

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }


    var adminDataJson = localStorage.getItem('admin')
    this.adminData = JSON.parse(adminDataJson);
    this.adminType = this.adminData.admin_user_type;
    this.adminId = this.adminData.admin_id
  }

  ngOnInit(): void {

    this.swiper = new Swiper(".swiper-container", {
      slidesPerView: 3,
      spaceBetween: 20,
      slidesPerGroup: 3,
      // Add any other Swiper options you need
    });

    this.callApisSequentially()

    this.scheduleApiCall()

  }

  navigateToPrev(): void {
    this.swiper.slidePrev();
  }

  navigateToNext(): void {
    this.swiper.slideNext();
  }

  async scheduleApiCall() {
    this.apiInterval = setInterval(() => {
      this.callApisSequentially()
        .catch((error) => {
          console.error('An error occurred:', error);
        });
    }, 50000);
  }

  async callApisSequentially() {
    try {
      await this.dealsThisMonth()
      await this.getDashboardPercent()
      await this.initchartOptionsNegative()
      await this.getCrmCustomer()
      await this.leadStatusCount()
      await this.getLeadsComparision()
    } catch (error) {
      throw error;

    }
  }

  crm_performance: any;
  initchartOptionsNegative() {
    let data = {
      month: this.selectedMonth
    };
    this.service1.getCrmDashboardData(data).subscribe(({ success, data }) => {
      if (success) {
        const { createdLeads, dealsContacted, dealsWon, dealsLoss, months } = data;
        this.crm_performance = data;
        this.chartOptionsNegativeLeads = {
          series: [
            {
              name: "Leads Created",
              data: createdLeads
            },
            {
              name: "Leads Won",
              data: dealsWon
            },
            {
              name: "Leads Contacted",
              data: dealsContacted
            },
            {
              name: "Leads Lost",
              data: dealsLoss
            }
          ],
          chart: {
            toolbar: {
              show: false,
            },
            type: "area",
            height: this.isFullScreen ? 800 : 350,
            zoom: {
              enabled: false
            }
          },
          dataLabels: {
            enabled: true,  // Enable data labels
            formatter: function (val: number) {  // Format the data label
              return val.toString();
            },
            style: {
              fontSize: '12px',
              colors: ['#000']
            },
            background: {
              enabled: true,
              foreColor: '#fff',
              borderRadius: 2,
              padding: 4,
              borderColor: '#333',
              borderWidth: 1
            },
            dropShadow: {
              enabled: true,
              top: 1,
              left: 1,
              blur: 1,
              color: '#333',
              opacity: 0.45
            }
          },
          stroke: {
            curve: "straight"
          },
          title: {
            text: "CRM Performance Overview",
            align: "left",
            style: {
              fontSize: "14px"
            }
          },
          xaxis: {
            type: "category",
            categories: months,
            axisBorder: {
              show: false
            },
            axisTicks: {
              show: false
            }
          },
          yaxis: {
            tickAmount: 4,
            floating: false,
            labels: {
              style: {
                colors: "#8e8da4"
              },
              offsetY: -7,
              offsetX: 0
            },
            axisBorder: {
              show: false
            },
            axisTicks: {
              show: false
            }
          },
          fill: {
            opacity: 0.5
          },
          tooltip: {
            x: {
              format: "yyyy"
            },
            fixed: {
              enabled: false,
              position: "topRight"
            }
          },
          grid: {
            yaxis: {
              lines: {
                offsetX: -30
              }
            },
            padding: {
              left: 20
            }
          }
        };
      }
    });
  }


  getMonthData(index: number, value: number) {
    const currentDate = new Date();
    const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - 11 + index, 1);
    return { x: date, y: value };
  }



  getMonthName(month: number): string {
    const monthNames = [
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    return monthNames[month];
  }


  ngAfterViewInit() {
    new Swiper(".swiper-container", {
      slidesPerView: 3,
      spaceBetween: 20, // Adjust this value as needed for the desired spacing between slides
      slidesPerGroup: 3, // Keep this value consistent with the number of visible slides (slidesPerView)
      // freeMode: true,
      // autoplay: {
      //   delay: 2000, // Adjust the delay (in milliseconds) between slides
      //   disableOnInteraction: true, // Allow user interaction (like clicks) to stop autoplay
      // },

      breakpoints: {
        200: {
          slidesPerView: 1,
          spaceBetween: 15,
          slidesPerGroup: 1
        },
        620: {
          slidesPerView: 2,
          spaceBetween: 15,
          slidesPerGroup: 2
        },
        968: {
          slidesPerView: 3,
          spaceBetween: 15,
          slidesPerGroup: 3
        },
        1124: {
          slidesPerView: 3,
          spaceBetween: 15,
          slidesPerGroup: 3
        }
      }
    });


  }

  getTopLoss() {
    let data = {
      month: this.selectedMonth
    };
    const monthNames = [
      "January", "February", "March", "April", "May", "June", "July",
      "August", "September", "October", "November", "December"
    ];
    this.service1.getBigLoss(data).subscribe(res => {
      const newData = res?.map(item => {
        return { x: monthNames[item.month - 1], y: parseInt(item.Amount?.replace(/[^\d]/g, '')) }; // Extracting month name and value
      });

      this.chartTopLossOptions = {
        series: [
          {
            name: "top-loss",
            data: newData
          }
        ],
        chart: {
          toolbar: {
            show: false,
          },
          type: "line",
          // height: 350
        },
        stroke: {
          curve: "stepline"
        },
        dataLabels: {
          enabled: false
        },
        title: {
          text: "Top Loss Of Every Month",
          align: "left"
        },
        markers: {
          hover: {
            sizeOffset: 4
          }
        },
        xaxis: {
          type: 'category',
          categories: monthNames,
          labels: {
            rotate: -45,
            formatter: function (value) {
              return value;
            }
          }
        },
        yaxis: {
          labels: {
            formatter: function (value) {
              return "$" + value;
            }
          }
        }
      };
    });
  }






  getCrmCustomer() {

    // this.service.getList('customer', this.selectedMonth,).subscribe(({ success, data }) => {
    //   if (success) {
    //     this.crmCustomer = data
    //     this.filterLeads('All');
    //     this.isLoading = false;
    //   }
    //   else if (!success) {
    //     this.isLoading = false;
    //   }
    // })

    this.service.getDashboardList('customer', this.selectedMonth,).subscribe(({ success, data }) => {
      if (success) {
        this.crmCustomer = data
        this.filterLeads('All');
        this.isLoading = false;
      }
      else if (!success) {
        this.isLoading = false;
      }
    })
  }


  handlePageEvent(e: PageEvent) {
    this.pageEvent = e;
    this.length = e.length;
    this.pageSize = e.pageSize;
    this.pageIndex = e.pageIndex;
  }

  dealsThisMonth() {
    let data = {
      month: this.selectedMonth
    };

    this.service1.dealsThisMonth(data).subscribe(({ success, contactInFuture, dealClosed, percentageChange, totalCountCurrentMonth, monthlySale, expectedLoss, grossAmount }) => {
      if (success) {
        this.contactInFuture = contactInFuture
        this.dealClosed = dealClosed
        this.percentageChange = percentageChange
        this.totalCountCurrentMonth = totalCountCurrentMonth
        this.monthlySale = monthlySale
        this.grossAmount = grossAmount
        this.expectedLoss = expectedLoss
      }
    })
  }


  onClickNavigate(url) {
    this.router.navigate([url]);
  }

  ngOnDestroy() {
    this.global.showCrmTab = false
    clearInterval(this.apiInterval);
    this.trackAdmin.storeActivity('Exit Dashboard Page', 'Exit Dashboard Page', this.routeId).subscribe(res => {
    })
    localStorage.removeItem('routerId');
  }


  activity() {
    this.trackAdmin.storeActivity('Dashboard', 'Enter in Crm Dashboard Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  filterLeads(statusName: string) {
    this.leadStatus.forEach(leadStatusItem => {
      leadStatusItem.active = leadStatusItem.name === statusName;
    });

    if (statusName === 'All') {
      this.filteredCrmCustomer = []
      this.filteredCrmCustomer = this.crmCustomer;
    } else {
      this.filteredCrmCustomer = []
      this.filteredCrmCustomer = this.crmCustomer.filter(item => item.lead_status === statusName);
    }
  }


  getDashboardPercent() {
    let data = {
      month: this.selectedMonth
    };
    this.service1.getPercent(data).subscribe(({ created, won, lost, revenue }) => {
      created = Math.round(created);
      won = Math.round(won);
      lost = Math.round(lost);
      revenue = Math.round(revenue);
      this.progressData = [
        { value: created, label: 'Leads Created', isPercentage: true, isNegative: created < 0, arrowDirection: created >= 0 ? 'up' : 'down' },
        { value: won, label: 'Leads Won', isPercentage: true, isNegative: won < 0, arrowDirection: won >= 0 ? 'up' : 'down' },
        { value: lost, label: 'Leads Lost', isPercentage: true, isNegative: lost < 0, arrowDirection: lost >= 0 ? 'up' : 'down' },
        { value: revenue, label: 'Revenue Difference', isPercentage: false, isNegative: revenue < 0, arrowDirection: revenue >= 0 ? 'up' : 'down' }
      ];
    });
  }

  //expand chart

  isFullScreen = false;
  toggleScreen1() {
    const fullscreenDiv1 = document.getElementById('fullscreenDiv1');

    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      fullscreenDiv1.requestFullscreen();
    }

    this.isFullScreen = !this.isFullScreen;
  }
  toggleScreen2() {
    const fullscreenDiv2 = document.getElementById('fullscreenDiv2');

    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      fullscreenDiv2.requestFullscreen();
    }

    this.isFullScreen = !this.isFullScreen;
    if (this.chartNegative) {
      this.chartNegative.updateOptions({
        chart: {
          height: this.isFullScreen ? 800 : 350,
        },
      });
    }
  }



  trackById(index: number, item: any): number {
    return item.id; // Assuming item has an 'id' field
  }

  onMonthChange(value: any) {
    this.selectedMonth = value;
    this.callApisSequentially();
  }


  activeTabs(type) {
    let isCurrentMonth = true;
    localStorage.setItem('selectedTab', type)
    localStorage.setItem('currentMonth', isCurrentMonth.toString());
    this.global.selectTabCrm = type;
  }

  toggleScreen3() {
    const fullscreenDiv3 = document.getElementById('fullscreenDiv3');

    if (document.fullscreenElement) {
      document.exitFullscreen();
    } else {
      fullscreenDiv3.requestFullscreen();
    }

    this.isFullScreen = !this.isFullScreen;
    if (this.pieChart) {
      this.pieChart.updateOptions({
        chart: {
          height: this.isFullScreen ? 800 : 350,
        },
      });
    }
  }

  leadStatusCount() {
    let data = {
      month: this.selectedMonth,
      admin_type: this.adminType,
      admin_id: this.adminId
    };
    this.service1.leadsCount(data).subscribe(({ success, data }) => {
      if (success) {
        this.all_leads = data.all_leads
        this.lost_leads = data.lost_leads
        this.contacted_leads = data.contacted_leads
        this.won_leads = data.won_leads
      }
    })
  }

  getValidNumber(value: any): number {
    return Number.isFinite(value) ? value : 0;
  }

  pieChartValue: any
  getLeadsComparision() {
    let data = {
      month: this.selectedMonth
    };
    this.service1.getLeadsComparision(data).subscribe(({ success, current_month, previous_month }) => {
      if (success && current_month && previous_month) {
        this.pieChartValue = {
          current_month: current_month,
          previous_month: previous_month
        }
        const seriesCurrent = [
          this.getValidNumber(current_month.all_leads),
          this.getValidNumber(current_month.won_leads),
          this.getValidNumber(current_month.contacted_leads),
          this.getValidNumber(current_month.lost_leads)
        ];

        const seriesPrevious = [
          this.getValidNumber(previous_month.all_leads),
          this.getValidNumber(previous_month.won_leads),
          this.getValidNumber(previous_month.contacted_leads),
          this.getValidNumber(previous_month.lost_leads)
        ];

        this.chartOptions1 = {
          series: seriesCurrent,
          chart: {
            type: "pie",
            width: '100%'
          },
          title: {
            text: "Current Month Performance",
            align: "left",
            style: {
              fontSize: "16px",
              fontWeight: "bold"
            }
          },
          legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '14px',
            markers: {
              width: 10,
              height: 10
            }
          },
          responsive: [
            {
              breakpoint: 1024,
              options: {
                chart: {
                  width: 600
                },
              }
            },
            {
              breakpoint: 768,
              options: {
                chart: {
                  width: 480
                },
              }
            },
            {
              breakpoint: 480,
              options: {
                chart: {
                  width: 300
                },
              }
            }
          ],
          labels: ["All Leads", "Won Leads", "Contacted Leads", "Lost Leads"],
          dataLabels: {
            enabled: true,
            formatter: (val, opts) => {
              const curVal = seriesCurrent[opts.seriesIndex] ?? 'N/A';
              const prevVal = seriesPrevious[opts.seriesIndex] ?? 'N/A';
              return `${curVal} (${prevVal})`;
            }
          },
          tooltip: {
            y: {
              formatter: (value, opts) => {
                const label = opts.w?.config?.labels[opts.seriesIndex];
                const curVal = seriesCurrent[opts.seriesIndex] ?? 'N/A';  // Using nullish coalescing to provide a fallback
                const prevVal = seriesPrevious[opts.seriesIndex] ?? 'N/A';
                return `Current: ${curVal}, Previous: ${prevVal}`;
              }
            }
          }
        };
      } else {
        console.error('Data is missing or the structure is incorrect');
      }
    }, error => {
      console.error("Error fetching data from API:", error);
    });
  }

  async generateReport(): Promise<void> {
    this.spinner.show();
    try {
      const chart1Canvas = await html2canvas(this.chartContainer.nativeElement);
      const chart2Canvas = await html2canvas(this.pieChartGraph.nativeElement);

      const chart1Image = chart1Canvas.toDataURL('image/png');
      const chart2Image = chart2Canvas.toDataURL('image/png');
      const dealsData = {
        totalCountCurrentMonth: this.totalCountCurrentMonth,
        percentageChange: this.percentageChange,
        monthlySale: this.monthlySale,
        grossAmount: this.grossAmount,
        expectedLoss: this.expectedLoss,
        dealClosed: this.dealClosed,
        contactInFuture: this.contactInFuture,
        all_leads: this.all_leads,
        lost_leads: this.lost_leads,
        contacted_leads: this.contacted_leads,
        won_leads: this.won_leads,
        selectedMonth: this.selectedMonth,
        crmPerformance: this.crm_performance,
        pieChartValue: this.pieChartValue
      };
      await this._dashboardReportPdf.generatePDF([chart1Image, chart2Image], dealsData, this.progressData);
    } catch (error) {
      this.spinner.hide();
      console.error("Error generating report:", error);
    } finally {
      this.spinner.hide();
    }
  }
}
import { AircalService } from './../../../services/aircal.service';
import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { Chart, registerables } from 'chart.js';
import moment from 'moment';
import AircallPhone from 'aircall-everywhere';
import { DomSanitizer } from '@angular/platform-browser';
import { DatePipe } from '@angular/common';
import { FormControl, FormGroup } from '@angular/forms';
import { GlobalVariable } from 'app/shared/global';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';



@Component({
  selector: 'app-call-dashboard',
  templateUrl: './call-dashboard.component.html',
  styleUrls: ['./call-dashboard.component.scss']
})
export class CallDashboardComponent implements OnInit {


  range = new FormGroup({
    start: new FormControl<Date | null>(null),
    end: new FormControl<Date | null>(null),
  });
  //calls
  inboundCalls: any = []
  outboundCalls: any = []
  missed_call = []
  totalCalls
  //
  from: Date = new Date();
  to: Date = new Date();

  //aircall
  private aircallPhone: AircallPhone;
  showMobile: boolean = false;
  showMobileDial: boolean = false;

  //
  lineChart: any;
  doughnutChart: any;
  progress = 85;
  circumference = 2 * Math.PI * 52;
  endDate
  startDate
  show = false
  guards = [
    { id: 1, name: "Customer 1" },
    { id: 2, name: "Customer 2" },
    { id: 3, name: "Customer 3" },
  ];
  filteredItems = [];
  searchTerm: string;
  contacts = [

  ];

  @ViewChild('lineCanvas') lineCanvas: ElementRef;

  id; 
  type;
  data;
  routeId
  constructor(public aircallService: AircalService, private sanitizer: DomSanitizer, private datePipe: DatePipe,
    private global: GlobalVariable, private trackAdmin: TrackAdminActivityService) {
    this.from.setDate(this.from.getDate()); // Set the default date to one week from now
    this.to.setDate(this.to.getDate() + 7); // Set the default date to one week from now
    this.to = new Date(this.from.getFullYear(), this.from.getMonth() - 1, this.from.getDate());

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }

  }
  calls: any[] = [];
  ngOnInit(): void {

    this.range.get('end').valueChanges.subscribe(value => {
      // run your API call here using the selected value
      const from = Math.floor(this.range.value.start.getTime() / 1000);
      const to = Math.floor(value.getTime() / 1000);
      this.getStatics(from, to)
    });
    this.filteredItems = this.contacts;
    this.getStatics()

  }
  ngAfterViewInit() {

    this.lineChartFunction();
    this.pieChartFunction();
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Call Dashboard Page', 'Exit Call Dashboard Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Call Dashboard Page', 'Enter in Call Dashboard Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  lineChartFunction() {
    this.originalCanvasHeight = '55vh',
    Chart.register(...registerables);
    // Now we need to supply a Chart element reference with an object that defines the type of chart we want to use, and the type of data we want to display.
    this.lineChart = new Chart(this.lineCanvas.nativeElement, {
      type: 'line',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'
        ],
        datasets: [{
          label: 'Inbound Calls',
          data: [65, 59, 80, 81, 56, 55, 40],
          fill: false,
          borderColor: '#235943',
          backgroundColor: '#bbf5b1',
          tension: 0.1
        },
        {
          label: 'Outbound Calls',
          data: [16, 32, 58, 78, 15, 20],
          fill: false,
          borderColor: '#027069',
          backgroundColor: '#bffffb',
          tension: 0.1
        }]
      },
      options: {
        maintainAspectRatio: false,
        layout: {
          padding: {
            left: 7,
            right: 0,
            top: 0,
            bottom: 0,
          },
        },
        // animation: {
        //   duration: 5000
        // }
      },
    });
  }
  pieChartFunction() {
    Chart.register(...registerables);
    const canvas = <HTMLCanvasElement>document.getElementById('doughnutCanvas') as HTMLCanvasElement | null;
    const ctx = canvas?.getContext('2d');


    this.doughnutChart = new Chart(ctx, {
      type: 'doughnut',
      data: {

        datasets: [
          {
            label: 'Total Shifts',
            data: [54.5, 35.5, 10.5],
            backgroundColor: [
              '#FFDADA',
              '#BFFFFB',
              '#BBF5B1',
            ],

          },

        ],
      },
      options: {
        maintainAspectRatio: false,
        layout: {
          padding: {
            left: 7,
            right: 0,
            top: 0,
            bottom: 0,
          },
        },
        // animation: {
        //   duration: 5000
        // }
      },
    });
  }

  search() {
    if (!this.searchTerm || this.searchTerm.length === 0) {
      this.filteredItems = this.contacts;
      return;
    }
    const results = this.contacts.filter(item => {
      return item.name.toLowerCase().indexOf(this.searchTerm.toLowerCase()) > -1;
    });
    this.filteredItems = results.length === 1 ? results : [];
  }

  loadPhone() {
    this.showMobile = !this.showMobile
    this.aircallPhone = new AircallPhone({
      domToLoadPhone: '#phone',
      onLogin: (settings) => {
        // Set data and status when user is logged in
        console.log('#user-info', settings, '// user information');
        console.log('#phone-loading', 'success', 'Phone is loaded and ready to use!');
      },
      onLogout: () => {
        // Reset data and status when user is logged out
        console.log('#user-info', '', '// user information');
        console.log('#phone-loading', 'danger', 'Phone is not loaded or logged in');
      }
    });
  }


  myHtml
  showDialer(contact) {
    this.showMobileDial = !this.showMobileDial

    this.aircallPhone = new AircallPhone({
      domToLoadPhone: '#phone-dial',
      onLogin: (settings) => {
        console.log('User is logged in');
        this.aircallPhone.isLoggedIn(response => {
          if (response) {
            console.log('User is logged in');
            this.aircallPhone.send('dial_number', { phone_number: contact.raw_digits }, (success, data) => {
              console.log(success, data);
            });
          } else {
            console.log('User is not logged in');
          }
        });
      },
      onLogout: () => {
        console.log('User is logged out');
      },
    });
  }

  onDateInput(type, e) {
    console.log(type, e);

    if (type == 'from') {
      const date = new Date(this.to);
      const formattedDate = date.toISOString().substring(0, 10);
      const to = Date.parse(formattedDate) / 1000;
      const from = Date.parse(e) / 1000;
      this.getStatics(from, to)
    }
    else {
      const date = new Date(this.from);
      const formattedDate = date.toISOString().substring(0, 10);
      const from = Date.parse(formattedDate) / 1000;
      const to = Date.parse(e) / 1000;
      this.getStatics(from, to)
    }

  }


  //statics data
  getStatics(from?, to?) {
    this.aircallService.statics(from, to).subscribe(({ calls, meta }) => {
      console.log(calls, meta);
      calls.forEach(element => {
        if (element.direction == 'outbound') {
          element.img2 = 'assets/icons/dashboard-icons/outgoing.png'
        }
        if (element.direction == 'inbound') {
          element.img2 = 'assets/icons/dashboard-icons/incoming.png'
        }
      });
      this.totalCalls = meta.count
      this.contacts = calls
      this.outboundCalls = calls.filter(obj => obj.direction == "outbound");
      this.inboundCalls = calls.filter(obj => obj.direction == "inbound" && obj.answered_at);
      this.missed_call = calls.filter(obj => !obj.answered_at);
    }
    )
  }

  isFullScreen
  originalCanvasHeight: string;
  toggleScreen3() {
    const fullscreenDiv3 = document.getElementById('fullscreenDiv3');
    
    if (document.fullscreenElement) {
      document.exitFullscreen();
      this.lineCanvas.nativeElement.style.height = this.originalCanvasHeight; // Restore original height
    } else {
      fullscreenDiv3.requestFullscreen();
      this.originalCanvasHeight = this.lineCanvas.nativeElement.style.height; // Store original height
      this.lineCanvas.nativeElement.style.height = '100vh'; // Set canvas height to full viewport height
    }
    
    this.isFullScreen = !this.isFullScreen;
    this.lineChart.resize(); // Redraw the chart with the new canvas size
  }

}

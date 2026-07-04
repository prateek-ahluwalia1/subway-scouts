import { T } from '@angular/cdk/keycodes';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { PermissionsService } from 'app/services/permissions.service';
import { ReportsService } from 'app/services/reports.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import moment from 'moment';
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
import { TDocumentDefinitions } from 'pdfmake/interfaces';
pdfMake.vfs = pdfFonts.pdfMake.vfs;
import { Chart , registerables } from 'chart.js';

@Component({
  selector: 'app-leave-report',
  templateUrl: './leave-report.component.html',
  styleUrls: ['../users-reports.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})

export class LeaveReportComponent implements OnInit {

  auditReport: any[] = []
  dateRange: any;
  searchTerm = '';
  adminPermissions: any;
  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  constructor(private resportService: ReportsService, private cdr: ChangeDetectorRef,
    private permissionService: PermissionsService,
    private trackAdmin: TrackAdminActivityService, private toast: ToastServiceService) {
    const per = this.permissionService.getPermissionsByTitle('Reports');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Audit Report');
  }

  ngOnInit(): void {}

  getAuditReport() {
    // const startDate = moment(this.dateRange.start).format('YYYY-MM-DD');
    // const endDate = moment(this.dateRange.end).format('YYYY-MM-DD');
    // let data = {
    //   start: startDate,
    //   end: endDate,
    // }

  
    // this.resportService.getAuditReport(data).subscribe(({ success, data }) => {
    //   if (success) {
    //     this.auditReport = data
    //     this.cdr.markForCheck()
    //   }
    //   else
    //     this.toast.toastNotification1('Audit Report!', 'Data not found.')
    // })
  }

  downloadAudit(id) {
    this.resportService.downloadAudit(id).subscribe(({ success, path }) => {
      if (success) {
        this.downloadPdf(path, `${'audit_report'}.pdf`)
        this.trackAdmin.storeActivity('Invoice Report', `Download a audit file`, localStorage.getItem('routerId')).subscribe()
      }
    })
  }

  downloadPdf(url: string, fileName: string) {
    const link = document.createElement('a');
    link.setAttribute('target', '_blank');
    link.setAttribute('href', url);
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    link.remove();
  }

  getStartEnd(event: any) {
    this.dateRange = event
  }

  delAudit(id) {
    this.resportService.delAudit(id).subscribe(({ success, message }) => {
      if (success) {
        this.getAuditReport()
        this.toast.toastNotification('Audit Report!', message)
        this.trackAdmin.storeActivity('Invoice Report', `Delete a audit file`, localStorage.getItem('routerId')).subscribe()
      }
      else {
        this.getAuditReport()
        this.toast.toastNotification1('Audit Report!', message)
      }
    })
  }

  getBase64Image(imgPath: string): Promise<string> {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.crossOrigin = 'Anonymous';

      img.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);

        const dataURL = canvas.toDataURL('image/png');
        resolve(dataURL);
      };

      img.onerror = () => {
        reject(new Error('Error loading image'));
      };

      img.src = imgPath;
    });
  }

  exprt_(){
    window.alert('Working on it')
  }

  async export() {
    try {
      const mainImageURL = await this.getBase64Image('/assets/images/cover.png');
      const logoImageURL = await this.getBase64Image('/assets/images/logo/scouts.png');
      const monthlyChart = await this.generateMonthlyChart();
      const docDefinition: TDocumentDefinitions = {
        pageSize: 'A4',
        pageOrientation: 'portrait',
        
        content: [
          {
            image: mainImageURL,
            width: 595, 
            height: 842, 
            absolutePosition: { x: 0, y: 0 }
          },
          {
            image: logoImageURL,
            width: 150,
            height: 120,
            absolutePosition: { x: 220, y: 500 },
            margin:[0,1,0,2]
          },
          {
            text: 'Monthly Report',
            fontSize: 50,
            color: 'black',
            absolutePosition: { x: 150, y: 600 },
            margin:[0,1,0,1]
          },
          {
            text: 'State Guard',
            fontSize: 46,
            color: 'black',
            absolutePosition: { x: 190, y: 650 },
            margin:[0,1,0,1]
          },
          {
            text: 'SUMMERHILL SHOPPING CENTRE',
            fontSize:20,
            color: 'black',
            absolutePosition: { x: 160, y: 720 },
            margin:[0,1,0,1]
          },
          {
            text: 'JULY 2023',
            fontSize: 27,
            color: 'black',
            absolutePosition: { x: 220, y: 750 },
            margin:[0,1,0,1]
          },
          {
            pageBreak: 'before',
            columns: [
              {
                width: "100%",
                table: {
                  widths: ['100%'],
                  heights: [10, 60],
                  body: [
                    [
                      {
                        text: "Executive Summary",
                        fontSize:20,
                        bold:true,
                        alignment: "left",
                        fillColor:'#BFBFBF'
                      },
                    ],
                    [
                      {
                        text: [
                          { text: 'July 2023 has focused on active reporting and services and ensuring our staff are continuously uplifting the service.\n', bold: true },
                          { text: 'Our staff have been instructed to actively report incidents and the record of incidents has improved over the last two months since the commencement of new reporting.\n', bold: true },
                          { text: 'We are currently seeing consistent issues with mainly external tenants.\n', bold: true },
                          { text: 'We continue to uplift and improve the service through innovation and sourcing of staff and training.\n', bold: true },
                          { text: 'We have new team members this month and continuously work on quality staff retention despite the sometimes volatile interactions with the public that our staff have to deal with.\n', bold: true }
                        ],
                        alignment: "justify",
                        margin: [2, 2, 4, 2],
                      },
                    ],
                  ],
                },
                layout: {
                  hLineColor: function (i, node) {
                    return i === 0 || i === node.table.body.length ? "#CCC" : "#CCC";
                  },
                  vLineColor: function (i, node) {
                    return i === 0 || i === node.table.body.length ? "#CCC" : "#CCC";
                  },
                  vLineWidth: function (i, node) {
                    return i === 0 || i === node.table.widths.length ? 1 : 0;
                  },
                },
              }, 
            ],
          },

          {
            columns: [
              {
                width: "100%",
                table: {
                  widths: ['100%'],
                  heights: [10, 60],
                  body: [
                    [
                      {
                        text: "REPORTING BY MONTH JULY",
                        fontSize:20,
                        bold:true,
                        alignment: "center",
                        fillColor:'#BFBFBF'
                      },
                    ],
                    [
                      {
                        image: monthlyChart, //graph
                        width: 500,
                        height: 300,
                        
                        margin: [2, 2, 4, 2],
                      },
                    ],
                  ],
                },
                layout: {
                  hLineColor: function (i, node) {
                    return i === 0 || i === node.table.body.length ? "#CCC" : "#CCC";
                  },
                  vLineColor: function (i, node) {
                    return i === 0 || i === node.table.body.length ? "#CCC" : "#CCC";
                  },
                  vLineWidth: function (i, node) {
                    return i === 0 || i === node.table.widths.length ? 1 : 0;
                  },
                },
              },
            ],

            margin:[0,10,0,10],
          },

          {
            columns: [
              {
                width: "100%",
                table: {
                  widths: ['100%'],
                  heights: [10, 60],
                  body: [
                    [
                      {
                        text: "REPORTING BY MONTH JULY",
                        fontSize:20,
                        bold:true,
                        alignment: "center",
                        fillColor:'#BFBFBF'
                      },
                    ],
                    [
                      {
                        image: monthlyChart, //graph
                        width: 500,
                        height: 300,
                        
                        margin: [2, 2, 4, 2],
                      },
                    ],
                  ],
                },
                layout: {
                  hLineColor: function (i, node) {
                    return i === 0 || i === node.table.body.length ? "#CCC" : "#CCC";
                  },
                  vLineColor: function (i, node) {
                    return i === 0 || i === node.table.body.length ? "#CCC" : "#CCC";
                  },
                  vLineWidth: function (i, node) {
                    return i === 0 || i === node.table.widths.length ? 1 : 0;
                  },
                },
              },
            ],
            margin:[0,10,0,10],
          },
        ],

        defaultStyle: {
          margin: [10, 10, 10, 10] 
        },

        pageMargins: [15, 15, 15, 15] 
      };
      pdfMake.createPdf(docDefinition).download('Monthly_report');
    } 
    catch (error) {
      console.error(error);
      alert('Failed to load the image. Please try again.');
    }
  }

  async generateMonthlyChart(): Promise<string> {
    return new Promise((resolve, reject) => {
      Chart.register(...registerables);
      const canvas = document.createElement('canvas');
      canvas.width = 800;
      canvas.height = 400;
      document.body.appendChild(canvas);
  
      const ctx = canvas.getContext('2d') as CanvasRenderingContext2D;
      const chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
          datasets: [{
            label: 'Dataset 1',
            // backgroundColor: 'rgba(255, 99, 132, 0.2)',
            // borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1,
            data: [5, 3, 3, 2, 1, 1, 1]
          }]
        },
        options: {
          responsive: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1 
              }
            },
            
            x: {
              ticks: {
                autoSkip: false,
                maxRotation: 40,
                minRotation: 40
              }
            }
          },
          plugins: {
            tooltip: {
              mode: 'index',
              intersect: false
            },
            legend: {
              position: 'top',
              align:'start'
            }
          },
          layout: {
            padding: {
              left: 50,
              right: 0,
              top: 0,
              bottom: 0
            }
          }
        }
      });
  
      setTimeout(() => {
        chart.data.datasets.forEach((dataset, datasetIndex) => {
          const meta = chart.getDatasetMeta(datasetIndex);
          meta.data.forEach((bar, index) => {
            const data = dataset.data[index];
            const xPos = bar.x;
            const yPos = bar.y + 10; 
            ctx.save()
            ctx.textAlign = 'center';
            ctx.fillStyle = 'black'; 
            ctx.font = '15px Arial'; 
            ctx.fillText(data.toString(), xPos, yPos);
            ctx.restore();
          });
        });
        const base64Image = canvas.toDataURL('image/png');
        document.body.removeChild(canvas);
        resolve(base64Image);
      }, 1000); 
    });
  }
}

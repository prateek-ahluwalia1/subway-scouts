import { Component, OnInit, ViewChild } from '@angular/core';
import { CustomerService } from 'app/services/customer.service';
import { ReportsService } from 'app/services/reports.service';
import { GlobalVariable } from 'app/shared/global';
import { DateAdapter } from '@angular/material/core';
import { MatSelect, MatSelectChange } from '@angular/material/select';
import { AgentService } from 'app/services/crm/agent.service';
import { ServiceService } from 'app/services/service.service';
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
import { TDocumentDefinitions } from 'pdfmake/interfaces';
pdfMake.vfs = pdfFonts.pdfMake.vfs;

export class Customers {
  id: number;
  name
}
@Component({
  selector: 'app-pnl-report',
  templateUrl: './pnl-report.component.html',
  styleUrls: ['../users-reports.component.scss']
})
export class PnlReportComponent implements OnInit {

  currentMonth: number = new Date().getMonth() + 1;
  selectedMonth: number | null = null;
  previewData: any[] = []


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
  @ViewChild('selects') selects: MatSelect;
  totalPL:number = 0;
  errorMessage: string;
totalActualRevenue: number = 0;
  totalBookingExpense: number = 0;


  constructor(
    public globals: GlobalVariable, 
    private cus: CustomerService,
    private resportService: ReportsService,
    private service: AgentService,
    public dateAdapter: DateAdapter<Date>, 
    public server: ServiceService,

  ) { 
    this.selectedMonth = this.currentMonth
    this.dateAdapter.setLocale('en-AU');

  }

  ngOnInit(): void {
  }


  export(type) {
    let apiCalled = false; // Variable to track if the API has been called
  
    const handleExport = () => {
      if (!apiCalled) {
        let data = { month: this.selectedMonth };
        this.resportService.getCrmTotalLeads(data).subscribe((res: any) => {
          this.previewData = res.data.total_leads;
          this.calculateTotals();
          apiCalled = true; // Set API called to true after successful call
          if (type === 'pdf') {
            this.downloadPDF();
          } else if (type === 'preview') {
            // Handle preview logic here, e.g., display preview modal
          }
        });
      } else {
        if (type === 'pdf') {
          this.downloadPDF();
        } else if (type === 'preview') {
          // Handle preview logic here, e.g., display preview modal
        }
      }
    };
  
    handleExport();
  }
  

  onMonthChange(event: MatSelectChange) {
    this.selectedMonth = event.value;
    
  }
  
  calculateGrossProfitAndLoss(leads: any[]): { grossProfit: number, grossLoss: number } {
    let grossProfit = 0;
    let grossLoss = 0;
  
    leads.forEach(lead => {
      if (lead.lead_status === 'Won') {
        const actualRevenue = lead.actual_revenue ? parseFloat(lead.actual_revenue.replace(/\D/g, '')) : 0;
        const bookingExpense = lead.booking_expense ? parseFloat(lead.booking_expense.replace(/\D/g, '')) : 0;
        const diff = actualRevenue - bookingExpense;
  
        if (!isNaN(diff)) {
          if (diff > 0) {
            grossProfit += diff;
          } else {
            grossLoss += Math.abs(diff);
          }
        }
      }
    });
  
    return { grossProfit, grossLoss };
  }

  calculateTotals() {
    this.totalActualRevenue = this.previewData.reduce((total, item) => {
      return total + (parseFloat(item.actual_revenue) || 0);
    }, 0);

    this.totalBookingExpense = this.previewData.reduce((total, item) => {
      return total + (parseFloat(item.booking_expense) || 0);
    }, 0);
  }


  downloadPDF() {
    const getBase64Image = (imgPath: string): Promise<string> => {
      return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = "Anonymous";
  
        img.onload = () => {
          const canvas = document.createElement("canvas");
          canvas.width = img.width;
          canvas.height = img.height;
  
          const ctx = canvas.getContext("2d");
          ctx.drawImage(img, 0, 0);
  
          const dataURL = canvas.toDataURL("image/png");
          resolve(dataURL);
        };
  
        img.onerror = () => {
          reject(new Error("Error loading image"));
        };
  
        img.src = imgPath;
      });
    };
  
    // Retrieve logo data URL asynchronously
    getBase64Image("/assets/images/logo/scouts.png").then((logoDataURL) => {
      const docDefinition: TDocumentDefinitions = {
        pageSize: 'A4',
        content: [
          {
            table: {
              widths: ['30%', '40%','30%'],
              body: [
                [
                  
                  {
                    text: ''
                  },
                  {
                    image: logoDataURL,
                    width: 100,
                    height: 80,
                    alignment:'center'
                  },
                  {
                    text:''
                  }
                ],
              ],
              
            },
            layout: {
              defaultBorder: false,
            },
          },
          {
            text: 'PnL Report',
            style: 'header'
          },
          this.getTableContent(),
          
          
        ],
        styles: {
          header: {
            fontSize: 18,
            bold: true,
            margin: [0, 0, 0, 10],
  
          },
          tableHeader: {
            bold: true,
            fontSize: 10,
            color: 'black',
            fillColor: '#a0e2d2',
  
          },
          tableCell: {
            fontSize: 8
          },
          grossCell: {
            fontSize: 8,
            bold: true,
            fillColor: '#a0e2d2',
            color: 'black'
          },
          customCol: {
            fillColor: '#BFBFBF',
            color: 'black',
            fontSize: 8,
  
          },
          totalValues: {
            fontSize: 8,
            color: 'black',
            fillColor: '#a9d08e',
            bold: true
          }
        }
      };
  
      pdfMake.createPdf(docDefinition).download('crm-overview');
    }).catch((error) => {
      console.error("Error loading logo:", error);
    });
  }
  
  
  
  getTableContent() {
    this.calculateTotals();
  const grossValues = this.calculateGrossProfitAndLoss(this.previewData);
    return {
      table: {
        headerRows: 1,
        widths: [
          'auto', '13%', 'auto', 'auto', 'auto', 
          'auto', 'auto', 'auto', 'auto'
        ],
        body: [
          [
            { text: 'Name', style: 'tableHeader' },
            { text: 'Email', style: 'tableHeader' },
            { text: 'Phone', style: 'tableHeader' },
            { text: 'Lead Source', style: 'tableHeader' },
            { text: 'Lead Status', style: 'tableHeader' },
            { text: 'Annual Revenue', style: 'tableHeader' },
            { text: 'Manual Revenue', style: 'tableHeader' },
            { text: 'Actual Revenue', style: 'tableHeader' },
            { text: 'Booking Expense', style: 'tableHeader' }
          ],
          ...this.previewData.map(item => [
            { text: item.name || 'N/A', style: 'tableCell' },
            { text: item.email || 'N/A', style: 'tableCell' },
            { text: item.phone || 'N/A', style: 'tableCell' },
            { text: item.lead_source || 'N/A', style: 'tableCell' },
            { text: item.lead_status || 'N/A', alignment: 'center', style: 'tableCell' },
            { text: item.annual_revenue || 'N/A',  alignment: 'center',style: 'tableCell' },
            { text: item.manual_revenue || 'N/A', alignment: 'center', style: 'tableCell' },
            { text: `$${item.actual_revenue || 0}`, alignment: 'right', style: 'customCol' },
            { text: `$${item.booking_expense || 0}`, alignment: 'right', style: 'customCol' }
          ]),
          [
            { text: '', colSpan: 7 }, {}, {}, {}, {}, {}, {},
            { text: `$${this.totalActualRevenue}`, alignment: 'right', style: 'totalValues' },
            { text: `$${this.totalBookingExpense}`, alignment: 'right', style: 'totalValues' }
          ],
          [
            { text: 'Gross Profit',colSpan: 7 ,style: 'tableCell',alignment:'center',bold:true },  {}, {}, {},{}, {}, {},
            { text: `$${grossValues.grossProfit}`, colSpan:2,alignment: 'center',  style: 'grossCell' },{}
          ],
          [
            { text: 'Gross Loss', colSpan: 7 ,style: 'tableCell',alignment:'center',bold:true },  {}, {}, {},{}, {}, {},
            { text: `$${grossValues.grossLoss}`,colSpan:2, alignment: 'center', style: 'grossCell' },{}
          ]
        ],
        dontBreakRows: true,

      },
      
      
      
    };
  }

  
  
}

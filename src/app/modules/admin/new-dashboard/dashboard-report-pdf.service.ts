import { Injectable } from '@angular/core';
import * as pdfMake from 'pdfmake/build/pdfmake';
import * as pdfFonts from 'pdfmake/build/vfs_fonts';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { GlobalVariable } from 'app/shared/global';
import { DatePipe } from '@angular/common';

(pdfMake as any).vfs = pdfFonts.pdfMake.vfs;

@Injectable({
    providedIn: 'root'
})
export class DashbardReportPdfService {
    leadData: any[] = []
    datePipe = new DatePipe('en-US');

    httpOptions = {
        headers: new HttpHeaders({
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
        }),
    };
    constructor(private __http: HttpClient, private global: GlobalVariable) { }

    generatePDF(chartImages: string[], dealsData: any, progressData: any[]): void {

        const crmPerformance = dealsData.crmPerformance;
        const currentMonth = dealsData.pieChartValue.current_month;
        const previousMonth = dealsData.pieChartValue.previous_month;
        // Get the current and previous month names
        const currentMonthName = this.datePipe.transform(new Date(), 'MMM');
        const previousMonthDate = new Date();
        previousMonthDate.setMonth(previousMonthDate.getMonth() - 1);
        const previousMonthName = this.datePipe.transform(previousMonthDate, 'MMM');

        this.__http.post<any>(`${this.global.baseUrlCrm}total-lead`, { month: dealsData.selectedMonth }, this.httpOptions)
            .subscribe(res => {
                console.log(res);
                this.leadData = res.data.total_leads
                const generateTableBody = () => {

                    const headerRow = [
                        "ID",
                        "Name",
                        "Email",
                        "Phone",
                        "Lead Status",
                        "Created at",
                    ];


                    const bodyRows = [...this.leadData.map(item => [item.id.toString(), item.name, item.email, item.phone, item.lead_status, this.datePipe.transform(item.created_at, 'MM-dd-yyyy')])]

                    const tableBody = [
                        headerRow,
                        ...bodyRows,

                    ];



                    return tableBody;
                };

                const docDefinition: any = {
                    pageSize: "A4",

                    content: [
                        {
                            text: 'CRM Performance Overview',
                            style: 'header'
                        },
                        {
                            image: chartImages[0],
                            width: 500,
                            margin: [0, 0, 0, 20]
                        },
                        {
                            text: 'Chart Data Values',
                            style: 'subheader'
                        },
                        {
                            table: {
                                widths: ['*', '*', '*', '*', '*'],
                                body: [
                                    [
                                        { text: 'Month', style: 'tableHeader' },
                                        { text: 'Leads Created', style: 'tableHeader', fillColor: '#d4e6f1' },
                                        { text: 'Leads Won', style: 'tableHeader', fillColor: '#d5f5e3' },
                                        { text: 'Leads Contacted', style: 'tableHeader', fillColor: '#fcf3cf' },
                                        { text: 'Leads Lost', style: 'tableHeader', fillColor: '#f5b7b1' }
                                    ],
                                    ...crmPerformance.months.map((month, index) => [
                                        { text: month, style: 'tableData' },
                                        { text: crmPerformance.createdLeads[index], style: 'tableData', fillColor: '#ebf5fb' },
                                        { text: crmPerformance.dealsWon[index], style: 'tableData', fillColor: '#e8f8f5' },
                                        { text: crmPerformance.dealsContacted[index], style: 'tableData', fillColor: '#fef9e7' },
                                        { text: crmPerformance.dealsLoss[index], style: 'tableData', fillColor: '#f9ebea' }
                                    ])
                                ]
                            },
                            layout: 'lightHorizontalLines',
                            margin: [0, 0, 0, 20]
                        },
                        {
                            text: `Comparison Between Current(${currentMonthName}) and Previous(${previousMonthName}) Month`,
                            style: 'subheader'
                        },
                        {
                            table: {
                                widths: ['*', '*'],
                                body: [
                                    ['Metric', `Current(${currentMonthName}) Month`],
                                    ['Leads Created', progressData[0].value + '%'],
                                    ['Leads Won', progressData[1].value + '%'],
                                    ['Leads Lost', progressData[2].value + '%'],
                                    ['Revenue Difference', '$' + progressData[3].value]
                                ]
                            },
                            layout: 'lightHorizontalLines',
                            margin: [0, 0, 0, 20]
                        },
                        {
                            image: chartImages[1],
                            width: 400,
                            height: 320,
                            margin: [0, 0, 0, 10]
                        },
                        {
                            text: `Current(${currentMonthName}) Month vs Previous(${previousMonthName}) Month`,
                            style: 'header',
                            margin: [0, 20, 0, 10]
                        },
                        {
                            table: {
                                widths: ['*', '*', '*'],
                                body: [
                                    [
                                        { text: 'Leads', style: 'metricTitle', fillColor: '#f2f2f2' },
                                        { text: `Current(${currentMonthName}) Month`, style: 'metricTitle', fillColor: '#f2f2f2' },
                                        { text: `Previous(${previousMonthName}) Month`, style: 'metricTitle', fillColor: '#f2f2f2' }
                                    ],
                                    [
                                        { text: 'Leads Created', style: 'metricValue' },
                                        { text: currentMonth.all_leads, style: 'metricValue', fillColor: '#e6f7ff' },
                                        { text: previousMonth.all_leads, style: 'metricValue', fillColor: '#e6f7ff' }
                                    ],
                                    [
                                        { text: 'Leads Contacted', style: 'metricValue' },
                                        { text: currentMonth.contacted_leads, style: 'metricValue', fillColor: '#e6f7ff' },
                                        { text: previousMonth.contacted_leads, style: 'metricValue', fillColor: '#e6f7ff' }
                                    ],
                                    [
                                        { text: 'Leads Won', style: 'metricValue' },
                                        { text: currentMonth.won_leads, style: 'metricValue', fillColor: '#e6f7ff' },
                                        { text: previousMonth.won_leads, style: 'metricValue', fillColor: '#e6f7ff' }
                                    ],
                                    [
                                        { text: 'Leads Lost', style: 'metricValue' },
                                        { text: currentMonth.lost_leads, style: 'metricValue', fillColor: '#e6f7ff' },
                                        { text: previousMonth.lost_leads, style: 'metricValue', fillColor: '#e6f7ff' }
                                    ],
                                ]
                            },
                            layout: {
                                fillColor: function (rowIndex: number, node: any, columnIndex: number) {
                                    return (rowIndex % 2 === 0) ? '#f9f9f9' : null;
                                },
                                hLineWidth: function (i: number, node: any) {
                                    return 0;
                                },
                                vLineWidth: function (i: number, node: any) {
                                    return 0;
                                },
                                paddingLeft: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingRight: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingTop: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingBottom: function (i: number, node: any) {
                                    return 10;
                                }
                            },
                            margin: [0, 0, 0, 10]
                        },
                        {
                            text: 'Leads Overview',
                            style: 'header',
                            margin: [0, 20, 0, 10]
                        },
                        {
                            table: {
                                widths: ['*', '1%', '*',],
                                body: [

                                    [
                                        { text: 'Deals Created This Month', style: 'metricTitle', fillColor: '#f2f2f2', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: 'Revenue This Month', style: 'metricTitle', fillColor: '#f2f2f2', border: [false, false, false, false] }
                                    ],
                                    [
                                        { text: dealsData.totalCountCurrentMonth, style: 'metricValue', fillColor: '#e6f7ff', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: '$ ' + dealsData.monthlySale, style: 'metricValue', fillColor: '#e6f7ff', border: [false, false, false, false] }
                                    ],
                                    [{ text: '', margin: [0, 0, 0, 1], border: [false, false, false, false], colSpan: 2 }, {}, {}],
                                    [
                                        { text: 'Deals Closing This Month', style: 'metricTitle', fillColor: '#f2f2f2', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: 'Contact In Future', style: 'metricTitle', fillColor: '#f2f2f2', border: [false, false, false, false] }
                                    ],
                                    [
                                        { text: dealsData.dealClosed, style: 'metricValue', fillColor: '#e6f7ff', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },

                                        { text: dealsData.contactInFuture, style: 'metricValue', fillColor: '#e6f7ff', border: [false, false, false, false] }
                                    ]
                                ]
                            },
                            layout: {
                                fillColor: function (rowIndex: number, node: any, columnIndex: number) {
                                    return (rowIndex % 2 === 0) ? '#ffffff' : null;
                                },
                                hLineWidth: function (i: number, node: any) {
                                    return 0;
                                },
                                vLineWidth: function (i: number, node: any) {
                                    return 0;
                                },
                                paddingLeft: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingRight: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingTop: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingBottom: function (i: number, node: any) {
                                    return 10;
                                },

                            },
                            margin: [0, 0, 0, 10]
                        },
                        {
                            text: 'Company Health',
                            style: 'header',
                            margin: [0, 20, 0, 10]
                        },
                        {
                            table: {
                                widths: ['*', '1%', '*'],
                                body: [
                                    [
                                        { text: 'Gross Profit Of Month', style: 'metricTitle', fillColor: '#f2f2f2', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: 'Expected Loss Value Of Month', style: 'metricTitle', fillColor: '#f2f2f2', border: [false, false, false, false] }
                                    ],
                                    [
                                        { text: '$ ' + dealsData.grossAmount, style: 'metricValue', fillColor: '#e6f7ff', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: '$ ' + dealsData.expectedLoss, style: 'metricValue', fillColor: '#e6f7ff', border: [false, false, false, false] }
                                    ]
                                ]
                            },
                            layout: {
                                fillColor: function (rowIndex: number, node: any, columnIndex: number) {
                                    return (rowIndex % 2 === 0) ? '#ffffff' : null;
                                },
                                hLineWidth: function (i: number, node: any) {
                                    return 0;
                                },
                                vLineWidth: function (i: number, node: any) {
                                    return 0;
                                },
                                paddingLeft: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingRight: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingTop: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingBottom: function (i: number, node: any) {
                                    return 10;
                                }
                            },
                            margin: [0, 0, 0, 10]
                        },
                        {
                            text: 'Leads Summary',
                            style: 'header',
                            margin: [0, 20, 0, 10]
                        },
                        {
                            table: {
                                widths: ['*', '1%', '*', '1%', '*', '1%', '*'],
                                body: [
                                    [
                                        { text: 'Leads', style: 'metricTitle', fillColor: '#d4e6f1', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: 'Won', style: 'metricTitle', fillColor: '#d5f5e3', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: 'Lost', style: 'metricTitle', fillColor: '#f5b7b1', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: 'Contacts', style: 'metricTitle', fillColor: '#fcf3cf', border: [false, false, false, false] }
                                    ],
                                    [
                                        { text: dealsData.all_leads, style: 'metricValue', fillColor: '#ebf5fb', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: dealsData.won_leads, style: 'metricValue', fillColor: '#e8f8f5', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: dealsData.lost_leads, style: 'metricValue', fillColor: '#f9ebea', border: [false, false, false, false] },
                                        { text: '', fillColor: '#ffffff' },
                                        { text: dealsData.contacted_leads, style: 'metricValue', fillColor: '#fef9e7', border: [false, false, false, false] }
                                    ]
                                ]
                            },
                            layout: {
                                fillColor: function (rowIndex: number, node: any, columnIndex: number) {
                                    return (rowIndex % 2 === 0) ? '#f9f9f9' : null;
                                },
                                hLineWidth: function (i: number, node: any) {
                                    return 0;
                                },
                                vLineWidth: function (i: number, node: any) {
                                    return 0;
                                },
                                paddingLeft: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingRight: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingTop: function (i: number, node: any) {
                                    return 10;
                                },
                                paddingBottom: function (i: number, node: any) {
                                    return 10;
                                }
                            },
                            margin: [0, 0, 0, 10]
                        },
                        {
                            text: 'Lead Data',
                            style: 'header',
                            margin: [0, 20, 0, 10]
                        },
                        {
                            table: {
                                headerRows: 1,
                                widths: ["10%", "auto", "auto", "auto", "auto", "auto"],
                                body: generateTableBody(),
                                fontSize: 5
                            },
                            layout: {
                                hLineWidth: function (i, node) {
                                    return i === 0 || i === 1 || i === node.table.body.length ? 2 : 1;
                                },
                                vLineWidth: function () {
                                    return 1;
                                },
                                hLineColor: function (i) {
                                    return i === 0 || i === 1 ? "#000" : "#ccc";
                                },
                                vLineColor: function () {
                                    return "#ccc";
                                },
                            },
                        }
                    ],
                    styles: {
                        header: {
                            fontSize: 18,
                            bold: true,
                            margin: [0, 0, 0, 10]
                        },
                        subheader: {
                            fontSize: 16,
                            bold: true,
                            margin: [0, 10, 0, 5]
                        },
                        tableHeader: {
                            fontSize: 14,
                            bold: true,
                            alignment: 'center',
                            fillColor: '#f2f2f2',
                            margin: [0, 5, 0, 5],
                            color: '#333'
                        },
                        tableData: {
                            fontSize: 12,
                            alignment: 'center',
                            margin: [0, 5, 0, 5],
                            color: '#555'
                        },
                        metricTitle: {
                            fontSize: 14,
                            bold: true,
                            alignment: 'center',
                            margin: [0, 5, 0, 5],
                            color: '#333'
                        },
                        metricValue: {
                            fontSize: 12,
                            alignment: 'center',
                            margin: [0, 5, 0, 5],
                            color: '#555'
                        },
                        tableBody: {
                            fontSize: 5
                        }
                    },
                    defaultStyle: {
                        alignment: 'left'
                    }
                };

                pdfMake.createPdf(docDefinition).download('CRM_Overview.pdf');
            });
    }
}

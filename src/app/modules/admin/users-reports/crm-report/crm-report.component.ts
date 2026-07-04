import { Component, OnInit } from "@angular/core";
import { ReportsService } from "app/services/reports.service";
import { CommonServiceService } from "../common-service.service";
import { ToastServiceService } from "app/services/toast-service.service";
import { MatSelectChange } from "@angular/material/select";
import { ServiceService } from "app/services/service.service";

@Component({
  selector: "app-crm-report",
  templateUrl: "./crm-report.component.html",
  styleUrls: ["../users-reports.component.scss"],
})
export class CrmReportComponent implements OnInit {
  currentMonth: number = new Date().getMonth() + 1;
  selectedMonth: number | null = null;
  selectedAgents: any[] = [];
  agents: any[] = [];
  errorMessage: string;
  previewData: any[] = [];

  months = [
    { name: "January", value: 1 },
    { name: "February", value: 2 },
    { name: "March", value: 3 },
    { name: "April", value: 4 },
    { name: "May", value: 5 },
    { name: "June", value: 6 },
    { name: "July", value: 7 },
    { name: "August", value: 8 },
    { name: "September", value: 9 },
    { name: "October", value: 10 },
    { name: "November", value: 11 },
    { name: "December", value: 12 },
  ];
  constructor(
    private resportService: ReportsService,
    private _commonService: CommonServiceService,
    private toast: ToastServiceService,
    public server: ServiceService
  ) {
    this.selectedMonth = this.currentMonth;
  }

  ngOnInit(): void {
    this.getSalePerson();
  }

  getReport() {
    this.resportService
      .getCrmReport()
      .subscribe(({ success, path, message }) => {
        if (success) {
          this._commonService.downloadPdf(path, "crm_report.pdf");
          this.toast.toastNotification(message, "CRM Overview Report!");
        } else {
          this.toast.toastNotification1(
            "Something went wrong",
            "CRM Overview Report!"
          );
        }
      });
  }

  onMonthChange(event: MatSelectChange) {
    this.selectedMonth = event.value;
  }

  getSalePerson() {
    const data = { type: "saleperson" };
    this.server.getAdmin("active", data).subscribe(
      (users) => {
        this.agents = users.data;
      },
      (error) => (this.errorMessage = <any>error)
    );
  }
  onAgentSelectionChange(selectedAgents) {
    this.selectedAgents = this.getAgentIds(selectedAgents.value);
  }
  getAgentIds(agents: any[]): number[] {
    return agents.map((agent) => agent.id);
  }

  export(type) {
    let data = {
      agent_ids: this.selectedAgents,
      month: this.selectedMonth,
    };
    this.resportService.getCrmTotalLeads(data).subscribe((res: any) => {
      this.previewData = res.data.total_leads;
    });
  }

  calculateGrossProfitAndLoss(leads: any[]): {
    grossProfit: number;
    grossLoss: number;
  } {
    let grossProfit = 0;
    let grossLoss = 0;

    leads.forEach((lead) => {
      if (lead.lead_status === "Won") {
        const sumActual = parseInt(lead.actual_revenue.replace(/\D/g, ""), 10);
        const bookingExpense = parseInt(
          lead.booking_expense.replace(/\D/g, ""),
          10
        );
        const diff = sumActual - bookingExpense;

        if (diff > 0) {
          grossProfit += diff;
        } else {
          grossLoss += diff;
        }
      }
    });

    return { grossProfit, grossLoss };
  }
}

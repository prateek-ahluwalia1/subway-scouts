import { Component, OnInit, ViewChild } from '@angular/core';
import { CustomerService } from 'app/services/customer.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { DateAdapter, MatOption } from '@angular/material/core';
import { FormControl } from '@angular/forms';
import { MatSelect } from '@angular/material/select';
import { AgentService } from 'app/services/crm/agent.service';
import { ServiceService } from 'app/services/service.service';


export class Customers {
  id: number;
  name: string;
}
@Component({
  selector: 'app-crm-customer',
  templateUrl: './crm-customer.component.html',
  styleUrls: ['../users-reports.component.scss'],
})
export class CrmCustomerComponent implements OnInit {
  @ViewChild('selects') selects: MatSelect;
  @ViewChild('selectsPersons') selectsPersons: MatSelect;
  totalPL: number = 0;
  customers: Customers[] = [];
  previewData: any[] = []
  assignedOperationNames: any[] = []
  filtered_assigned: any[] = []
  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  startCreate = '';
  endCreate = '';
  startUpdate = '';
  endUpdate = '';
  agents: any[] = [];
  selectedAgents: any[] = [];
  errorMessage: string;
  dateRange: { start: Date, end: Date };
  creationDateRange: { start: Date, end: Date };
  updationDateRange: { start: Date, end: Date };
  leadStatus = [
    { name: 'Attempted to Contact', color: '#ADD9FF' },
    { name: 'Contact in Future', color: '#F8E199' },
    { name: 'Contacted', color: '#FFD6BC' },
    { name: 'Junk Lead', color: '#EB4D4D' },
    { name: 'Lost Lead', color: '#C63D2F' },
    { name: 'Pre-Qualified', color: '#FFC6C6' },
    { name: 'Won', color: '#1A5D1A' },
    { name: 'Won Completed', color: '#1A5D1A' },
    { name: 'Won Voucher', color: '#1A5D1A' },
    { name: 'Qualified', color: '#FFC6ff' },
    { name: 'Not Qualified', color: '#F6C1FF' },
  ];
  lead_status = new FormControl(null)
  assigned_person = new FormControl(null)
  allSelected = false;
  allSelectedPersons = false;
  companylist: any[] = [];
  selectedCompanies: string[] = [];
  customersIds
  fromMultiCustomer
  sitesList: any;

  constructor(public globals: GlobalVariable, private cus: CustomerService,
    private service: AgentService,
    public dateAdapter: DateAdapter<Date>,
    public server: ServiceService,

  ) {

    this.dateAdapter.setLocale('en-AU');

  }

  ngOnInit(): void {
    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
      }
    })

    this.getSalePerson();
    this.getCompanyList();

  }


  export(type) {
    const formatDateRange = (start: Date | null, end: Date | null) => {
      const startDate = start ? moment(start).format("MM-DD-YYYY") : '';
      const endDate = end ? moment(end).format("MM-DD-YYYY") : '';
      return startDate && endDate ? `${startDate} - ${endDate}` : '';
    };
    const dateRange = formatDateRange(this.dateRange?.start, this.dateRange?.end);
    const creationDateRange = formatDateRange(this.creationDateRange?.start, this.creationDateRange?.end);
    const updationDateRange = formatDateRange(this.updationDateRange?.start, this.updationDateRange?.end);
    let data = {
      file_type: type,
      lead_status: this.lead_status.value || '',
      clientname: this.customersIds,
      assigned_opertaion_person: this.assigned_person.value || '',
      agent_ids: this.selectedAgents,
      companies: this.selectedCompanies,
      travel_Data_range: '',
      created_date_range: creationDateRange,
      last_update_range: updationDateRange,
      // date: dateRange,

    };
    if (type === 'excel') {

      this.service.getLeadsReport(data).subscribe((res) => {
        console.log(res);
        if (res.success) {
          this.downloadExcelFile(res.path, "Leads Report");
        }
      });
    }
    else {
      console.log(type);
      this.service.getLeadsReport(data).subscribe(({ success, data }) => {
        if (success) {
          this.previewData = data
          this.assignedOperationNames = this.extractAssignedOperationNames(data);
          if (this.previewData.length === 0) {
            this.assigned_person.reset();
          }
          this.calculatePL();
        }
      });

    }
  }


  downloadExcelFile(fileUrl: string, fileName: string) {
    const link = document.createElement("a");
    link.href = fileUrl;
    link.target = "_blank";
    link.download = fileName;
    link.click();
  }



  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
  }

  onDateRangeChange(event: { start: Date, end: Date }, rangeType: string) {
    if (rangeType === 'dateRange') {
      this.dateRange = event;
    } else if (rangeType === 'creationDateRange') {
      this.creationDateRange = event;
    } else if (rangeType === 'updationDateRange') {
      this.updationDateRange = event;
    }
  }

  toggleAllSelection() {
    if (this.allSelected) {
      this.selects.options.forEach((item: MatOption) => item.select());
    } else {
      this.selects.options.forEach((item: MatOption) => item.deselect());
    }
  }

  toggleAllSelectionPersons() {
    if (this.allSelectedPersons) {
      this.selectsPersons.options.forEach((item: MatOption) => item.select());
    } else {
      this.selectsPersons.options.forEach((item: MatOption) => item.deselect());
    }
  }

  calculatePL() {
    this.totalPL = 0;
    this.previewData.forEach(item => {
      item.pl = (item.actual_revenue || 0) - (item.booking_price || 0);
      if (!isNaN(item.pl)) {
        this.totalPL += item.pl;
      }
    });
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

  getCompanyList() {
    this.service.getCompanyList().subscribe((response: any) => {
      this.companylist = response.data.map((site_name: string, index: number) => {
        return { id: index + 1, site_name };
      });
    });
  }

  receiveDataFromChildSite(data) {
    this.selectedCompanies = data?.map(item => item.site_name);
  }

  extractAssignedOperationNames(data) {
    let ids = new Set(this.filtered_assigned.map(item => item.id));
    data.forEach(item => {
      if (item.assign_operation && item.assign_operation.id && item.assign_operation.name) {
        if (!ids.has(item.assign_operation.id)) {
          this.filtered_assigned.push({
            id: item.assign_operation.id,
            name: item.assign_operation.name
          });
          ids.add(item.assign_operation.id);
        }
      }
    });
    return this.filtered_assigned;
  }



}

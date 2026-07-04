import { Component, OnInit } from '@angular/core';
import { AgentService } from 'app/services/crm/agent.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-all-leads',
  templateUrl: './all-leads.component.html',
  styleUrls: ['./all-leads.component.scss']
})
export class AllLeadsComponent implements OnInit {
  logo
  leadStatus = [
    { name: 'Attempted to Contact' },
    { name: 'Contact in Future' },
    { name: 'Contacted' },
    { name: 'Lost Lead' },
    { name: 'Not Contacted' },
    { name: 'Pre-Qualified' },
    { name: 'Won' },
    { name: 'Qualified' },
    { name: 'Not Qualified' },
  ];
  leads: any = []
  constructor(private global: GlobalVariable, private service: AgentService,) { }

  ngOnInit(): void {
    this.global.showCrmTab = true
    const logo = JSON.parse(localStorage.getItem('admin'))
    // this.businessName = title.title
    this.logo = logo?.apiKeys?.logo
    this.getCrmCustomer()
  }

  ngOnDestroy(): void {
    this.global.showCrmTab = false
  }

  getCrmCustomer() {
    this.service.getList('customer').subscribe(({ success, data }) => {
      if (success) {
        console.log(data);
        
        this.leads = data
      }
    })
  }
}

// crm.component.ts
import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-crm',
  templateUrl: './crm.component.html',
  styleUrls: ['./crm.component.scss']
})
export class CrmComponent implements OnInit {

  crm = [
    { name: 'Leads', text: 'The list of leads who are not currently using our system.', url: 'lead', icon: 'heroicons_outline:clipboard-list', image: '/assets/images/nav-images/crm-customer.jpg' },
    { name: 'Sales Person', text: 'A list of individuals who actively engage in promoting the system.', url: 'sales-person', icon: 'heroicons_outline:phone-missed-call', image: '/assets/images/nav-images/salesperson.jpg' },
    { name: 'Create Stagging ', text: 'Lorem ipsum adipisicing elit. Quaerat, sunt!', url: 'stagging', icon: 'heroicons_outline:phone-missed-call', image: '/assets/images/nav-images/agent.jpeg' },
  ];

  constructor(public router: Router) { }

  ngOnInit(): void {
  }

  crmHead(url: string) {
    console.log(url);
    if (url == 'stagging') {
      this.router.navigate([url]);
    }
    else {
      this.router.navigate(['/crm', url]);
    }

  }
}

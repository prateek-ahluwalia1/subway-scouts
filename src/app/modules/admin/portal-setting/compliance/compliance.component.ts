import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-compliance',
  templateUrl: './compliance.component.html',
  styleUrls: ['./compliance.component.scss']
})
export class ComplianceComponent implements OnInit {

  complianceItems: string[] = [
    'Name',
    'Email',
    'Driver License Back',
    'Driver License Front',
    'Medicare',
    'Passport',
    'Security Licence',
    'Vaccination',
    'Visa'
  ];

  compliance: { [key: string]: boolean } = {};

  constructor() {
    this.complianceItems.forEach(item => {
      if (item === 'Name' || item === 'Email') {
        this.compliance[item] = true;  
      } else {
        this.compliance[item] = false;
      }
    });
  }

  ngOnInit(): void { }

  saveCompliance() {
    console.log('Compliance data saved:', this.compliance);
  }
}

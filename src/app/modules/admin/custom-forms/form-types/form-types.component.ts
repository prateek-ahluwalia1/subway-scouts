import { Location } from '@angular/common';
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-form-types',
  templateUrl: './form-types.component.html',
  styleUrls: ['./form-types.component.scss']
})
export class FormTypesComponent implements OnInit {

  formItems = [
    {
      icon: '➕',
      title: 'Start From Scratch',
      description: 'A blank slate is all you need',
      route: '/myforms/form-builder'
    },
    {
      icon: '📑',
      title: 'Use Template',
      description: 'Choose from 10,000+ premade forms',
      route: '/myforms/templates-list'
    },
    {
      icon: '📥',
      title: 'Import Form',
      description: 'Import an existing form',
      route: '/myforms/form-import'
    },
    {
      icon: '✍️',
      title: 'Signable Document',
      description: 'Collect e-signatures',
      route: '/myforms/signed-forms'
    }
  ];

  constructor(private location: Location) { }

  ngOnInit(): void { }

  goBack(): void {
    this.location.back();
  }
}

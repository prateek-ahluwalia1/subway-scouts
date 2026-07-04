import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-card',
  templateUrl: './card.component.html',
  styleUrls: ['./card.component.scss']
})
export class CardComponent implements OnInit {

  userRole: string = '';
  chatsUser = [
    { name: 'Admins', url: 'admins', icon: 'bar_chart', image: '/assets/images/nav-images/staffreport.jpg' },
    { name: 'Staff', url: 'staff', icon: 'bar_chart', image: '/assets/images/nav-images/staffreport.jpg' },
    { name: 'Customers', url: 'customer', icon: 'bar_chart', image: '/assets/images/nav-images/staffreport.jpg' },
    { name: 'Contractors', url: 'contractor', icon: 'bar_chart', image: '/assets/images/nav-images/staffreport.jpg' },
  ];

  filteredChatsUser: any[] = [];
  constructor() {
    const role = JSON.parse(localStorage.getItem('admin'));
    this.userRole = role?.admin_user_type
  }

  ngOnInit(): void {
    this.filteredChatsUser = this.getFilteredChatsUser();
  }


  getFilteredChatsUser(): any[] {
    if (this.userRole === 'super-admin') {
      return this.chatsUser; // Show all cards for super-admin
    } else if (this.userRole === 'admin') {
      return this.chatsUser.filter(user => user.url === 'admins'); // Show only 'Admins' card for admin
    } else if (this.userRole === 'guard') {
      return this.chatsUser.filter(user => user.url === 'staff'); // Show only 'Staff' card for guard
    } else {
      return []; // For other roles or unauthenticated users, return an empty array or handle accordingly
    }
  }

}

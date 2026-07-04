import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { MatDrawer } from '@angular/material/sidenav';
import { Item } from 'app/modules/admin/file-manager/file-manager.types';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.scss'],
  styles: [`  .input[type="text"] {
    display: block;
    color: rgb(34, 34, 34);
    background: linear-gradient(142.99deg, rgba(217, 217, 217, 0.63) 15.53%, rgba(243, 243, 243, 0.63) 88.19%);
    box-shadow: 0px 12px 24px -1px rgba(0, 0, 0, 0.18);
    border-color: rgba(7, 4, 14, 0);
    border-radius: 50px;
    block-size: 20px;
    padding: 18px 15px;
    outline: none;
    text-align: center;
    width: 200px;
    transition: 0.5s;
}`]
})
export class UsersComponent implements OnInit {
  items = [
    // { name: 'Admins', text: 'To administer the system and creating roster & shifts.', url: 'admin' },
    { name: 'Customers', text: 'To whom we provide our services for guarding their sites.', url: 'customer' },
    { name: 'Contractors', text: 'Third party who provide Staff and their services to our company.', url: 'contractor' },
    { name: 'Other Staff', text: 'Displays data of the staff who are currently on board', url: 'staff' },
  ];

  searchText: string = '';
  @ViewChild('matDrawer', { static: true }) matDrawer: MatDrawer;
  drawerMode: 'side' | 'over';
  selectedItem: Item;
  filteredItems: any[] = [];

  /**
   * Constructor
   */
  constructor(
    private _activatedRoute: ActivatedRoute,
    private _changeDetectorRef: ChangeDetectorRef,
    private _router: Router) {
    this.searchText = '';
  }
  ngOnInit(): void {
    this.filteredItems = this.items
  }

  navigateToFileManager(userType: string): void {
    const url = `/users/file-manager/${userType}`;
    this._router.navigate([url]);
  }


  onBackdropClicked(): void {
    this._router.navigate(['./'], { relativeTo: this._activatedRoute });
    this._changeDetectorRef.markForCheck();
  }

  trackByFn(index: number, item: Item): any {
    return item.id || index;
  }

  performSearch(): void {
    if (this.searchText.trim() === '') {
      this.filteredItems = [...this.items];
    } else {
      this.filteredItems = this.items.filter((item) =>
        item.name.toLowerCase().includes(this.searchText.toLowerCase())
      );
    }
    this._changeDetectorRef.markForCheck();
  }
}

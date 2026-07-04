import { Component, OnInit, ViewChild } from '@angular/core';
import { FormControl } from '@angular/forms';
import { MatSelect } from '@angular/material/select';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { ReplaySubject, Subject, take, takeUntil } from 'rxjs';
export interface ChipModel {
  name: string;
  selected: boolean;
}
interface Customer {
  id: number;
  name: string;
}
const Customer: Customer[] = [
  {
    name: 'Usman Bhatti',
    id: 1,
  },
  {
    name: 'Naveed Qadir',
    id: 2,
  },
  {
    name: 'Rameez Arrain',
    id: 3,
  }
];
@Component({
  selector: 'app-roster-three',
  templateUrl: './roster-three.component.html',
  styleUrls: ['./roster-three.component.scss']
})
export class RosterThreeComponent implements OnInit {
  toppings = new FormControl('');
  toppingList: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia'];
  formFieldHelpers: string[] = [''];
  selected = 'site-week';

  availableChips: ChipModel[] = [
    { name: 'Guard', selected: true },
    { name: 'Site', selected: false },
  ];


  /**Select Multiple Sites */
  /** list of Customer */
  protected customers: Customer[] = Customer;
  public allBanksSize = Customer.length;
  public hotelMultiCtrl = new FormControl();
  public hotelMultiFilterCtrl = new FormControl();
  public filteredCustomersMulti: ReplaySubject<Customer[]> = new ReplaySubject<
    Customer[]
  >(1);
  public filteredSitesMulti: ReplaySubject<Customer[]> = new ReplaySubject<
    Customer[]
  >(1);
  protected filteredHotelsCache: Customer[] = [];
  isIndeterminate = false;
  isChecked = false;
  @ViewChild('multiSelect', { static: true }) multiSelect: MatSelect;
  protected _onDestroy = new Subject<void>();
  constructor(
    public globals: GlobalVariable
  ) { }



  ngOnInit(): void {


    /**this code for customer multiselect */
    this.hotelMultiCtrl.setValue([
      this.customers[5],
      this.customers[4],
      this.customers[3],
    ]);
    // load the initial bank list
    this.filteredCustomersMulti.next(this.customers.slice());
    this.filteredSitesMulti.next(this.customers.slice());
    // listen for search field value changes
    this.hotelMultiFilterCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterHotelsMulti();
        this.setToggleAllCheckboxState();
      });
    // listen for multi select field value changes
    this.hotelMultiCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.setToggleAllCheckboxState();
      });
    /**end Code ofr customer oninit */
  }

  /**Again Customer multi select */
  ngAfterViewInit() {
    this.setInitialValue();
  }
  ngOnDestroy() {
    this._onDestroy.next();
    this._onDestroy.complete();
  }
  toggleSelectAll(selectAllValue: boolean) {
    this.filteredCustomersMulti
      .pipe(take(1), takeUntil(this._onDestroy))
      .subscribe((val) => {
        if (selectAllValue) {
          this.hotelMultiCtrl.patchValue(val);
        } else {
          this.hotelMultiCtrl.patchValue([]);
        }
      });
  }
  /**
   * Sets the initial value after the filteredBanks are loaded initially
   */
  protected setInitialValue() {
    this.filteredCustomersMulti
      .pipe(take(1), takeUntil(this._onDestroy))
      .subscribe(() => {
        // setting the compareWith property to a comparison function
        // triggers initializing the selection according to the initial value of
        // the form control (i.e. _initializeSelection())
        // this needs to be done after the filteredBanks are loaded initially
        // and after the mat-option elements are available
        this.multiSelect.compareWith = (a: Customer, b: Customer) =>
          a && b && a.id === b.id;
      });
  }
  protected filterHotelsMulti() {
    if (!this.customers) {
      return;
    }
    // get the search keyword
    let search = this.hotelMultiFilterCtrl.value;
    if (!search) {
      this.filteredHotelsCache = this.customers.slice();
      this.filteredCustomersMulti.next(this.filteredHotelsCache);
      return;
    } else {
      search = search.toLowerCase();
    }
    // filter the customers
    this.filteredHotelsCache = this.customers.filter(
      (bank) => bank.name.toLowerCase().indexOf(search) > -1
    );
    this.filteredCustomersMulti.next(this.filteredHotelsCache);
  }
  protected setToggleAllCheckboxState() {
    let filteredLength = 0;
    if (this.hotelMultiCtrl && this.hotelMultiCtrl.value) {
      this.filteredHotelsCache.forEach((el) => {
        if (this.hotelMultiCtrl.value.indexOf(el) > -1) {
          filteredLength++;
        }
      });
      this.isIndeterminate =
        filteredLength > 0 && filteredLength < this.filteredHotelsCache.length;
      this.isChecked =
        filteredLength > 0 &&
        filteredLength === this.filteredHotelsCache.length;
    }
  }
  /**end again customer multiselect */


  //////////////calender Next and Previous///////////

  displayNextWeek() {
    this.getDays(1);

  }
  displayPrevWeek() {
    this.getDays(-1);

  }
  timeTracker = moment();
  getDays(e) {
    this.globals.getWeekDays = [];
    // console.log(this.days);

    if (e == 0) {
      this.timeTracker = moment();
    } else {
      this.timeTracker.add(e, 'weeks');
    }
    // Find start and end of week
    var startOfWeek = this.timeTracker.clone().startOf('isoWeek');
    var endOfWeek = this.timeTracker.clone().endOf('isoWeek');
    this.globals.selectedDate = `${startOfWeek.format('DD MMM')} - ${endOfWeek.format('DD MMM')}`;
    var day = startOfWeek;
    while (day.isSameOrBefore(endOfWeek)) {
      this.globals.getWeekDays.push(moment(day).format("ddd , DD/MM"));
      day = day.add(1, 'days');
    }
    return this.globals.getWeekDays;
  }


  onChange($event: any) {
    console.log('Selected chip: ', $event.value);
  }
}

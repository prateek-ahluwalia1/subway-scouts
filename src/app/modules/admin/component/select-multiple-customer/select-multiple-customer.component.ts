import { AfterViewInit, ChangeDetectionStrategy, ChangeDetectorRef, Component, EventEmitter, Input, OnDestroy, OnInit, Output, ViewChild } from '@angular/core';
import { FormControl } from '@angular/forms';
import { ReplaySubject, Subject } from 'rxjs';
import { take, takeUntil } from 'rxjs/operators';
import { MatSelect, MatSelectChange } from '@angular/material/select';
import { GlobalVariable } from 'app/shared/global';
@Component({
  selector: 'app-select-multiple-customer',
  templateUrl: './select-multiple-customer.component.html',
  styleUrls: ['./select-multiple-customer.component.scss'],
  // changeDetection: ChangeDetectionStrategy.OnPush
})
export class SelectMultipleCustomerComponent implements OnInit, AfterViewInit, OnDestroy {


  @Input('placeholderText') placeholderText;
  @Input('label') label;
  @Input() signleDouble;
  @Input() set dataArray(dataArray: any[]) {
    this._data = dataArray;
    // load the initial bank list
    if (dataArray) {
      this.filteredBanksMulti.next(dataArray.slice());
    }

    if (this.global.selectedCustomer) {
      const defaultOption = this.dataArray.find(option => option.id == this.global.selectedCustomer);
      if (defaultOption) {
        this.bankMultiCtrl.setValue(defaultOption);
      }
    }
    if (this.global.selectedCustomers) {
      const jobIds = this.global.selectedCustomers.map(job => job.id);
      const matchedObjects = this.dataArray.filter(obj => jobIds.includes(obj.id));
      this.bankMultiCtrl.setValue(matchedObjects);
    }

  }

  get dataArray(): any[] {
    return this._data;
  }

  private _data: any[];

  /** control for the selected customer for multi-selection */
  public bankMultiCtrl: FormControl<any> = new FormControl<any>([]);

  /** control for the MatSelect filter keyword multi-selection */
  public bankMultiFilterCtrl: FormControl<string> = new FormControl<string>('');

  /** list of customers filtered by search keyword */
  public filteredBanksMulti: ReplaySubject<any> = new ReplaySubject<any>(1);

  /** local copy of filtered customers to help set the toggle all checkbox state */
  protected filteredBanksCache: any = [];

  /** flags to set the toggle all checkbox state */
  isIndeterminate = false;
  isChecked = false;

  @ViewChild('multiSelect', { static: true }) multiSelect: MatSelect;

  /** Subject that emits when the component has been destroyed. */
  protected _onDestroy = new Subject<void>();
  @Output() selectedCustomer = new EventEmitter<any>();
  @Output() selectedCus = new EventEmitter<any>();

  constructor(public global: GlobalVariable, private cdr: ChangeDetectorRef) { }

  ngOnInit() {

    // listen for search field value changes
    this.bankMultiFilterCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy))
      .subscribe(() => {
        this.filterBanksMulti();
        this.setToggleAllCheckboxState();
      });

    // listen for multi select field value changes
    this.bankMultiCtrl.valueChanges
      .pipe(takeUntil(this._onDestroy)).subscribe(() => {
        this.selectedCustomer.emit(this.bankMultiCtrl);
        this.selectedCus.emit(this.bankMultiCtrl);
        this.setToggleAllCheckboxState();
      });
  }

  ngAfterViewInit() {
    this.setInitialValue();
  }

  ngOnDestroy() {
    this._onDestroy.next();
    this._onDestroy.complete();
  }

  toggleSelectAll(selectAllValue: boolean) {
    this.filteredBanksMulti.pipe(take(1), takeUntil(this._onDestroy))
      .subscribe(val => {
        if (selectAllValue) {
          this.bankMultiCtrl.patchValue(val);
          this.selectedCustomer.emit(this.bankMultiCtrl);

        } else {
          this.bankMultiCtrl.patchValue([]);
          this.selectedCustomer.emit([]);
        }
      });
  }

  /**
   * Sets the initial value after the filteredBanks are loaded initially
   */


  protected setInitialValue() {
    if (this.multiSelect) { // Add a null check here
      this.filteredBanksMulti
        .pipe(take(1), takeUntil(this._onDestroy))
        .subscribe(() => {
          // setting the compareWith property to a comparison function
          // triggers initializing the selection according to the initial value of
          // the form control (i.e. _initializeSelection())
          // this needs to be done after the filteredBanks are loaded initially
          // and after the mat-option elements are available
          this.multiSelect.compareWith = (a: any, b: any) => a && b && a.id === b.id;
        });
    }
  }

  protected filterBanksMulti() {
    if (this.signleDouble == 'no') {
      if (!this.dataArray) {
        return;
      }
      let search = this.bankMultiFilterCtrl.value;
      if (!search) {
        this.filteredBanksMulti.next(this.dataArray.slice());
        return;
      } else {
        search = search.toLowerCase();
      }
      this.filteredBanksMulti.next(
        this.dataArray.filter(bank => bank.name.toLowerCase().indexOf(search) > -1)
      );
    }
    else {
      if (!this.dataArray) {
        return;
      }
      let search = this.bankMultiFilterCtrl.value;
      if (!search) {
        this.filteredBanksCache = this.dataArray.slice();
        this.filteredBanksMulti.next(this.filteredBanksCache);
        return;
      } else {
        search = search.toLowerCase();
      }
      this.filteredBanksCache = this.dataArray.filter(bank => bank.name.toLowerCase().indexOf(search) > -1);
      this.filteredBanksMulti.next(this.filteredBanksCache);
    }

  }

  protected setToggleAllCheckboxState() {
    let filteredLength = 0;
    if (this.bankMultiCtrl && this.bankMultiCtrl.value) {
      this.filteredBanksCache.forEach(el => {
        if (this.bankMultiCtrl.value.indexOf(el) > -1) {
          filteredLength++;
        }
      });
      this.isIndeterminate = filteredLength > 0 && filteredLength < this.filteredBanksCache.length;
      this.isChecked = filteredLength > 0 && filteredLength === this.filteredBanksCache.length;
    }
  }


  handleSelectionChange(event: MatSelectChange) {
    const selectedBanks = event.value;
    this.selectedCus.emit(this.bankMultiCtrl);
    this.selectedCustomer.emit(selectedBanks);
  }

  trackById(index: number, item: any) {
    return item.id;
  }
}

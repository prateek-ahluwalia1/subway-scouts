import { Component, Input, OnInit, Output, EventEmitter, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { FormControl } from '@angular/forms';
import { ReplaySubject } from 'rxjs';
import { take } from 'rxjs/operators';
import { MatSelect, MatSelectChange } from '@angular/material/select';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-select-multiple-guards',
  templateUrl: './select-multiple-guards.component.html',
  styleUrls: ['./select-multiple-guards.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,

})
export class SelectMultipleGuardsComponent implements OnInit {
  @Input('placeholderText') placeholderText;
  @Input() signleDouble;

  private _data: any[];
  public bankMultiCtrl: FormControl = new FormControl([]);
  public bankMultiFilterCtrl: FormControl = new FormControl('');

  public filteredBanksMulti: ReplaySubject<any> = new ReplaySubject<any>(1);

  isIndeterminate = false;
  isChecked = false;

  @Output() selectedUsers = new EventEmitter<any>();
  @Output() selectedCus = new EventEmitter<any>();

  constructor(public global: GlobalVariable, private changeDetect: ChangeDetectorRef) { }

  ngOnInit() {

    this.bankMultiFilterCtrl.valueChanges.subscribe(() => {
      this.filterBanksMulti();
      this.setToggleAllCheckboxState();
    });

    this.bankMultiCtrl.valueChanges.subscribe(() => {
      this.selectedUsers.emit(this.bankMultiCtrl);
      this.selectedCus.emit(this.bankMultiCtrl);
      this.setToggleAllCheckboxState();
    });
  }

  @Input() set dataArray(dataArray: any[]) {
    console.log(dataArray);
    
    if (dataArray) {
      this.global.selectedOption$.subscribe((selectedOption) => {
        if (selectedOption && dataArray) {
          const defaultOption = dataArray.find((option) => option.id == selectedOption);
          if (defaultOption) {
            this.bankMultiCtrl.setValue(defaultOption);
            this.changeDetect.markForCheck();
          }
        }
      });
    }
    this._data = dataArray;
    if (dataArray) {
      this.filteredBanksMulti.next(dataArray.slice());
    }
  }

  get dataArray(): any[] {
    return this._data;
  }

  toggleSelectAll(selectAllValue: boolean) {
    this.filteredBanksMulti.pipe(take(1)).subscribe((val) => {
      if (selectAllValue) {
        this.bankMultiCtrl.patchValue(val);
        this.selectedUsers.emit(this.bankMultiCtrl);
      } else {
        this.bankMultiCtrl.patchValue([]);
        this.selectedUsers.emit([]);
      }
    });
  }

  protected filterBanksMulti() {
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
      this.dataArray.filter((bank) => bank.name.toLowerCase().indexOf(search) > -1)
    );
  }

  protected setToggleAllCheckboxState() {
    if (this.bankMultiCtrl && this.bankMultiCtrl.value) {
      const filteredLength = this.bankMultiCtrl.value.length;
      this.isIndeterminate = filteredLength > 0 && filteredLength < this.dataArray.length;
      this.isChecked = filteredLength > 0 && filteredLength === this.dataArray.length;
    }
  }

  handleSelectionChange(event: MatSelectChange) {
    const selectedBanks = event.value;
    this.selectedCus.emit(this.bankMultiCtrl);
    this.selectedUsers.emit(selectedBanks);
  }


  trackById(index: number, item: any) {
    return item.id;
  }

  resetSelection() {
    this.bankMultiCtrl.setValue([]); // Reset the selected values
    this.selectedUsers.emit([]); // Emit an empty selection
    this.isIndeterminate = false;
    this.isChecked = false;
    this.changeDetect.markForCheck();
  }

}

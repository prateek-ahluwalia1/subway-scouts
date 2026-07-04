import { Component, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
import { FormControl } from '@angular/forms';
import { MatSelect } from '@angular/material/select';
import { GlobalVariable } from 'app/shared/global';
import { ReplaySubject, Subject, take, takeUntil } from 'rxjs';

@Component({
  selector: 'app-selects-sites-multiple-job-rosters',
  templateUrl: './selects-sites-multiple-job-rosters.component.html',
  styleUrls: ['./selects-sites-multiple-job-rosters.component.scss']
})
export class SelectsSitesMultipleJobRostersComponent implements OnInit {

 
  @Input('placeholderText') placeholderText;
  @Input('type') type;



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
 @Output() selectedSite = new EventEmitter<any>();

 constructor(public global: GlobalVariable) { }

 ngOnInit() {
  
    if (this.global.sitesList) {
      console.log(this.global.sitesList);
      
       // load the initial bank list
      this.filteredBanksMulti.next(this.global.sitesList.slice());

    // if (this.global.selectedSite) {
    //   const matchedObjects = this.dataArray.filter(obj => this.global.selectedSite.includes(obj.id));
    //   this.bankMultiCtrl.setValue(matchedObjects);
    // }

    // console.log("this.global.selectedSite  ",this.global.selectedSite);
    
    }
   // this.bankMultiCtrl.setValue(this.dataArray[1]);
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
     ////call api there
     this.selectedSite.emit(this.bankMultiCtrl);
     // console.log(this.bankMultiCtrl);
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
         ///call api there
     this.selectedSite.emit(this.bankMultiCtrl);
         // console.log(this.bankMultiCtrl.value);

       } else {
         this.bankMultiCtrl.patchValue([]);
       }
     });
 }

 /**
  * Sets the initial value after the filteredBanks are loaded initially
  */
 protected setInitialValue() {
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

 protected filterBanksMulti() {
   if (!this.global.sitesList) {
     return;
   }
   // get the search keyword
   let search = this.bankMultiFilterCtrl.value;
   if (!search) {
     this.filteredBanksCache = this.global.sitesList.slice();
     this.filteredBanksMulti.next(this.filteredBanksCache);
     return;
   } else {
     search = search.toLowerCase();
   }
   // filter the banks
   this.filteredBanksCache = this.global.sitesList.filter(bank => bank.site_name.toLowerCase().indexOf(search) > -1);
   this.filteredBanksMulti.next(this.filteredBanksCache);
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



}

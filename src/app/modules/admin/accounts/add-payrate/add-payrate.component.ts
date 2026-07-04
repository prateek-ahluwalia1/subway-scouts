import { Component, HostListener, Input, OnInit } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { MatDatepickerInputEvent } from '@angular/material/datepicker';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ChargeRateService } from 'app/services/charge-rate.service';
import { SiteService } from 'app/services/site.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import moment from 'moment';

interface ChargeListItem {
  metro: number;
  date: string;
  regional: number;
  metroName: string;
  regionalName: string;
}

@Component({
  selector: 'app-add-payrate',
  templateUrl: './add-payrate.component.html',
  styleUrls: ['./add-payrate.component.scss'],
})

export class AddPayrateComponent implements OnInit {

  @Input() public data;
  @HostListener('window:scroll')
  submitted: boolean = false
  addChargeForm: FormGroup;
  customerList: any = [];
  state: any;
  customer: any;
  effective_date: any;
  position: any;
  level: any;
  // payrateDate: any;
  // hours:any;
  // mon_sat: boolean = false;
  // weekend: any;
  title: any;
  ot_base_rate: any;
  chargeListMetroRegional: ChargeListItem[] = [];
  charge_List_Metro_Regional_EBA: ChargeListItem[] = [];
  charge_List_Metro_Regional_Award: ChargeListItem[] = [];

  levels = [
    { value: 1, name: 'Level 1' },
    { value: 2, name: 'Level 2' },
    { value: 3, name: 'Level 3' },
    { value: 4, name: 'Level 4' },
    { value: 5, name: 'Level 5' }
  ];

  constructor(
    private ngbActiveModal: NgbActiveModal,
    public siteService: SiteService,
    public chargeRateService: ChargeRateService,
    private toastService: ToastServiceService,
    public dateAdapter: DateAdapter<Date>,
  ) { 
    this.dateAdapter.setLocale('en-AU');
  }

  ngOnInit(): void {
    console.log("Data", this.data.rates)
    // this.initializeChargeLists(this.data.rates);
    // this.initializeRates(this.data.rates);

    this.initializeChargeLists(this.data.rates);
    this.siteService.getCustomer().subscribe(res => {
      this.customerList = res.data;
    });
    this.initializeRates(this.data.rates);
  }

  // private initializeChargeLists(rates: any): void {

  //   this.chargeListMetroRegional = [
  //     { metro: rates.pf_day ?? 0, date: 'Day', regional: rates.casual_day ?? 0, metroName: 'pf_day', regionalName: 'casual_day' },
  //     { metro: rates.pf_night ?? 0, date: 'Night', regional: rates.casual_night ?? 0, metroName: 'pf_night', regionalName: 'casual_night' },
  //     { metro: rates.pf_sat ?? 0, date: 'Saturday', regional: rates.casual_sat ?? 0, metroName: 'pf_sat', regionalName: 'casual_sat' },
  //     { metro: rates.pf_sun ?? 0, date: 'Sunday', regional: rates.casual_sun ?? 0, metroName: 'pf_sun', regionalName: 'casual_sun' },
  //     { metro: rates.pf_ph ?? 0, date: 'Public Holiday', regional: rates.casual_ph ?? 0, metroName: 'pf_ph', regionalName: 'casual_ph' },
  //   ];
    
  //   this.charge_List_Metro_Regional_EBA = [
  //     { metro: rates.eba_metro_mon_to_fri_day_rate, date: 'Mon-Fri (Day 06:00 - 18:00)', regional: rates.eba_reg_mon_to_fri_day_rate, metroName: 'eba_metro_mon_to_fri_day_rate', regionalName: 'eba_reg_mon_to_fri_day_rate' },
  //     { metro: rates.eba_metro_mon_to_fri_night_rate, date: 'Mon-Fri (Night 18:00 - 06:00)', regional: rates.eba_reg_mon_to_fri_night_rate, metroName: 'eba_metro_mon_to_fri_night_rate', regionalName: 'eba_reg_mon_to_fri_night_rate' },
  //     { metro: rates.eba_metro_sat_day_rate, date: 'Saturday ', regional: rates.eba_reg_sat_day_rate, metroName: 'eba_metro_sat_day_rate', regionalName: 'eba_reg_sat_day_rate' },
  //     { metro: rates.eba_metro_sun_day_rate, date: 'Sunday ', regional: rates.eba_reg_sun_day_rate, metroName: 'eba_metro_sun_day_rate', regionalName: 'eba_reg_sun_day_rate' },
  //     { metro: rates.eba_metro_pub_holi_day_rate, date: 'Public Holiday ', regional: rates.eba_reg_pub_holi_day_rate, metroName: 'eba_metro_pub_holi_day_rate', regionalName: 'eba_reg_pub_holi_day_rate' },
  //   ];

  //   this.charge_List_Metro_Regional_Award = [
  //     { metro: rates.award_metro_mon_to_fri_day_rate, date: 'Mon-Fri (Day 06:00 - 18:00)', regional: rates.award_reg_mon_to_fri_day_rate, metroName: 'award_metro_mon_to_fri_day_rate', regionalName: 'award_reg_mon_to_fri_day_rate' },
  //     { metro: rates.award_metro_mon_to_fri_night_rate, date: 'Mon-Fri (Night 18:00 - 06:00)', regional: rates.award_reg_mon_to_fri_night_rate, metroName: 'award_metro_mon_to_fri_night_rate', regionalName: 'award_reg_mon_to_fri_night_rate' },
  //     { metro: rates.award_metro_sat_day_rate, date: 'Saturday ', regional: rates.award_reg_sat_day_rate, metroName: 'award_metro_sat_day_rate', regionalName: 'award_reg_sat_day_rate' },
  //     { metro: rates.award_metro_sun_day_rate, date: 'Sunday ', regional: rates.award_reg_sun_day_rate, metroName: 'award_metro_sun_day_rate', regionalName: 'award_reg_sun_day_rate' },
  //     { metro: rates.award_metro_pub_holi_day_rate, date: 'Public Holiday ', regional: rates.award_reg_pub_holi_day_rate, metroName: 'award_metro_pub_holi_day_rate', regionalName: 'award_reg_pub_holi_day_rate' },
  //   ];
  // }

  private initializeChargeLists(rates: any): void {
    this.chargeListMetroRegional = [
      { metro: rates.def_metro_mon_to_fri_day_rate, date: 'Mon-Fri (Day 06:00 - 18:00)', regional: rates.def_reg_mon_to_fri_day_rate, metroName: 'def_metro_mon_to_fri_day_rate', regionalName: 'def_reg_mon_to_fri_day_rate' },
      { metro: rates.def_metro_mon_to_fri_night_rate, date: 'Mon-Fri (Night 18:00 - 06:00)', regional: rates.def_reg_mon_to_fri_night_rate, metroName: 'def_metro_mon_to_fri_night_rate', regionalName: 'def_reg_mon_to_fri_night_rate' },
      { metro: rates.def_metro_sat_day_rate, date: 'Saturday ', regional: rates.def_reg_sat_day_rate, metroName: 'def_metro_sat_day_rate', regionalName: 'def_reg_sat_day_rate' },
      { metro: rates.def_metro_sun_day_rate, date: 'Sunday ', regional: rates.def_reg_sun_day_rate, metroName: 'def_metro_sun_day_rate', regionalName: 'def_reg_sun_day_rate' },
      { metro: rates.def_metro_pub_holi_day_rate, date: 'Public Holiday ', regional: rates.def_reg_pub_holi_day_rate, metroName: 'def_metro_pub_holi_day_rate', regionalName: 'def_reg_pub_holi_day_rate' },
    ];

    this.charge_List_Metro_Regional_EBA = [
      { metro: rates.eba_metro_mon_to_fri_day_rate, date: 'Mon-Fri (Day 06:00 - 18:00)', regional: rates.eba_reg_mon_to_fri_day_rate, metroName: 'eba_metro_mon_to_fri_day_rate', regionalName: 'eba_reg_mon_to_fri_day_rate' },
      { metro: rates.eba_metro_mon_to_fri_night_rate, date: 'Mon-Fri (Night 18:00 - 06:00)', regional: rates.eba_reg_mon_to_fri_night_rate, metroName: 'eba_metro_mon_to_fri_night_rate', regionalName: 'eba_reg_mon_to_fri_night_rate' },
      { metro: rates.eba_metro_sat_day_rate, date: 'Saturday ', regional: rates.eba_reg_sat_day_rate, metroName: 'eba_metro_sat_day_rate', regionalName: 'eba_reg_sat_day_rate' },
      { metro: rates.eba_metro_sun_day_rate, date: 'Sunday ', regional: rates.eba_reg_sun_day_rate, metroName: 'eba_metro_sun_day_rate', regionalName: 'eba_reg_sun_day_rate' },
      { metro: rates.eba_metro_pub_holi_day_rate, date: 'Public Holiday ', regional: rates.eba_reg_pub_holi_day_rate, metroName: 'eba_metro_pub_holi_day_rate', regionalName: 'eba_reg_pub_holi_day_rate' },
    ]
    this.charge_List_Metro_Regional_Award = [
      { metro: rates.award_metro_mon_to_fri_day_rate, date: 'Mon-Fri (Day 06:00 - 18:00)', regional: rates.award_reg_mon_to_fri_day_rate, metroName: 'award_metro_mon_to_fri_day_rate', regionalName: 'award_reg_mon_to_fri_day_rate' },
      { metro: rates.award_metro_mon_to_fri_night_rate, date: 'Mon-Fri (Night 18:00 - 06:00)', regional: rates.award_reg_mon_to_fri_night_rate, metroName: 'award_metro_mon_to_fri_night_rate', regionalName: 'award_reg_mon_to_fri_night_rate' },
      { metro: rates.award_metro_sat_day_rate, date: 'Saturday ', regional: rates.award_reg_sat_day_rate, metroName: 'award_metro_sat_day_rate', regionalName: 'award_reg_sat_day_rate' },
      { metro: rates.award_metro_sun_day_rate, date: 'Sunday ', regional: rates.award_reg_sun_day_rate, metroName: 'award_metro_sun_day_rate', regionalName: 'award_reg_sun_day_rate' },
      { metro: rates.award_metro_pub_holi_day_rate, date: 'Public Holiday ', regional: rates.award_reg_pub_holi_day_rate, metroName: 'award_metro_pub_holi_day_rate', regionalName: 'award_reg_pub_holi_day_rate' },
      // { metro: 0, date: 'Saturday (Night 18:00 - 06:00)', regional: 0, metroName: 'award_metro_sat_night_rate', regionalName: 'award_reg_sat_night_rate' },
      // { metro: 0, date: 'Sunday (Night 18:00 - 06:00)', regional: 0, metroName: 'award_metro_sun_night_rate', regionalName: 'award_reg_sun_night_rate' },
      // { metro: 0, date: 'Public Holiday (Night 18:00 - 06:00)', regional: 0, metroName: 'award_metro_pub_holi_night_rate', regionalName: 'award_reg_pub_holi_night_rate' },
    ]
  }

  // private initializeRates(rates: any): void {
  //   this.mon_sat = rates.weekend === 1;
  //   this.hours = rates.hours;
  //   this.level = rates.level;
  //   this.title = rates.title;
  //   this.effective_date = rates.effective_date;
  // }

  private initializeRates(rates: any): void {
    this.customer = rates.customer_id;
    this.state = rates.state;
    this.position = rates.position;
    this.level = rates.level;
    this.title = rates.title;
    this.ot_base_rate = rates.ot_base_rate;
  }

  close(data): void {
    this.ngbActiveModal.close(data);
  }

  // submitPayRateForm(value, type, el?: HTMLElement): void {
  //   this.submitted = true;
  //   if (!this.title || !this.effective_date || !this.level || !this.hours) {
  //     el.scrollIntoView();
  //     return;
  //   } 
  //   else {
  //     let status = 'Pay Rate Operation';
  //     if (type == 'submit') {
  //       this.weekend = this.mon_sat ? 1 : 0;
  //       const formattedDate = this.effective_date ? moment(this.effective_date).format('YYYY/MM/DD') : null;
  //       const payload = {
  //         ...value, 
  //         effective_date: formattedDate,
  //         weekend: this.weekend,
  //       };
  //       Object.keys(payload).forEach((key) => {
  //         if (key === "null") {
  //           delete payload[key];
  //         }
  //       });
  //       this.chargeRateService.sendPayRates(payload).subscribe(res => {
  //         if (res.success) {
  //           this.toastService.toastNotification(res.message, status);
  //         } else {
  //           this.toastService.toastNotification1(res.message, status);
  //         }
  //       });
  //       this.close('add');
  //     } 
  //     else {
  //       value.id = this.data?.rates?.id;
  //       this.weekend = this.mon_sat ? 1 : 0;
  //       const formattedDate = this.effective_date ? moment(this.effective_date).format('YYYY/MM/DD') : null;
  //       const payload = {
  //         ...value, 
  //         effective_date: formattedDate, 
  //         weekend: this.weekend,
  //       };
  //       Object.keys(payload).forEach((key) => {
  //         if (key === "null") {
  //           delete payload[key];
  //         }
  //       });
  //       this.chargeRateService.updatePayRates(payload).subscribe(res => {
  //         if (res.success) {
  //           this.toastService.toastNotification(res.message, status);
  //         }
  //         else {
  //           this.toastService.toastNotification1(res.message, status);
  //         }
  //       });
  //       this.close('update');
  //     }
  //   }
  // }

  submitPayRateForm(value, type, el?: HTMLElement): void {
    this.submitted = true;
    if (!this.title || !this.customer || !this.level || !this.state || !this.position) {
      el.scrollIntoView();
      return;
    } else {
      let status = 'Pay Rate Operation';
      if (type == 'submit') {
        this.chargeRateService.sendPayRates(value).subscribe(res => {
          if (res.success) {
            this.toastService.toastNotification(res.message, status);
          } else {
            this.toastService.toastNotification1(res.message, status);
          }
        });
        this.close('add');
      } else {
        value.id = this.data?.rates?.id;
        this.chargeRateService.updatePayRates(value).subscribe(res => {
          if (res.success) {
            this.toastService.toastNotification(res.message, status);
          }
          else {
            this.toastService.toastNotification1(res.message, status);
          }
        });
        this.close('update');
      }
    }
  }
  
}

import { ToastServiceService } from '../../../../services/toast-service.service';
import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ChargeRateService } from 'app/services/charge-rate.service';
import { SiteService } from 'app/services/site.service';
import { ChargeRatesComponent } from '../charge-rates/charge-rates.component';
import { DateAdapter } from '@angular/material/core';
import moment from 'moment';

interface ChargeRate {
  metro: number;
  date: string;
  regional: number;
  metroName: string;
  regionalName: string;
}

interface RateData {
  customer_id: number;
  state: string;
  position: any;
  level: any;
  title: any;
  ot_base_rate: any;
  effective_date: any;
}

@Component({
  selector: 'app-add-charge-rate',
  templateUrl: './add-charge-rate.component.html',
  styleUrls: ['./add-charge-rate.component.scss']
})

export class AddChargeRateComponent implements OnInit {

  @ViewChild(ChargeRatesComponent) chargeRateComponent: ChargeRatesComponent;

  @Input() public data;
  addChargeForm: FormGroup;
  customerList: any = [];
  state: string;
  customer: number;
  position: any;
  level: any;
  title: any;
  ot_base_rate: any;
  effective_date: any;
  submitted: boolean = false

  chargeListMetroRegional: ChargeRate[] = [
    { metro: 0, date: 'Mon-Fri (Day 06:00 - 18:00)', regional: 0, metroName: 'flat_metro_week_day_day', regionalName: 'flat_regional_week_day_day' },
    { metro: 0, date: 'Mon-Fri (Night 18:00 - 06:00)', regional: 0, metroName: 'flat_metro_week_day_night', regionalName: 'flat_regional_week_day_night' },
    { metro: 0, date: 'Saturday (Day 06:00 - 18:00)', regional: 0, metroName: 'flat_metro_saturday', regionalName: 'flat_regional_saturday' },
    { metro: 0, date: 'Saturday (Night 18:00 - 06:00)', regional: 0, metroName: 'flat_metro_saturday_night', regionalName: 'flat_regional_saturday_night' },
    { metro: 0, date: 'Sunday (Day 06:00 - 18:00)', regional: 0, metroName: 'flat_metro_sunday', regionalName: 'flat_regional_sunday' },
    { metro: 0, date: 'Sunday (Night 18:00 - 06:00)', regional: 0, metroName: 'flat_metro_sunday_night', regionalName: 'flat_regional_sunday_night' },
    { metro: 0, date: 'Public Holiday (Day 06:00 - 18:00)', regional: 0, metroName: 'flat_metro_public_holiday', regionalName: 'flat_regional_public_holiday' },
    { metro: 0, date: 'Public Holiday (Night 18:00 - 06:00)', regional: 0, metroName: 'flat_metro_public_holiday_night', regionalName: 'flat_regional_public_holiday_night' },
  ];

  charge_List_Metro_Regional_EBA = [
    { metro: 0, date: 'Mon-Fri (Day 06:00 - 18:00)', regional: 0, metroName: 'eba_metro_weekday_day', regionalName: 'eba_regional_weekday_day' },
    { metro: 0, date: 'Mon-Fri (Night 18:00 - 06:00)', regional: 0, metroName: 'eba_metro_weekday_night', regionalName: 'eba_regional_weekday_night' },
    { metro: 0, date: 'Saturday (Day 06:00 - 18:00)', regional: 0, metroName: 'eba_metro_saturday_day', regionalName: 'eba_regional_saturday_day' },
    { metro: 0, date: 'Saturday (Night 18:00 - 06:00)', regional: 0, metroName: 'eba_metro_saturday_night', regionalName: 'eba_regional_saturday_night' },
    { metro: 0, date: 'Sunday (Day 06:00 - 18:00)', regional: 0, metroName: 'eba_metro_sunday_day', regionalName: 'eba_regional_sunday_day' },
    { metro: 0, date: 'Sunday (Night 18:00 - 06:00)', regional: 0, metroName: 'eba_metro_sunday_night', regionalName: 'eba_regional_sunday_night' },
    { metro: 0, date: 'Public Holiday (Day 06:00 - 18:00)', regional: 0, metroName: 'eba_metro_public_holiday', regionalName: 'eba_regional_public_holiday' },
    { metro: 0, date: 'Public Holiday (Night 18:00 - 06:00)', regional: 0, metroName: 'eba_metro_public_holiday_night', regionalName: 'eba_regional_public_holiday_night' },
  ]

  // charge_List_Metro_Regional_Award = [
  //   { metro: 0, date: 'Mon-Fri (Day 06:00 - 18:00)', regional: 0, metroName: 'award_metro_mon_to_fri_day_rate', regionalName: 'award_reg_mon_to_fri_day_rate' },
  //   { metro: 0, date: 'Mon-Fri (Night 18:00 - 06:00)', regional: 0, metroName: 'award_metro_mon_to_fri_night_rate', regionalName: 'award_reg_mon_to_fri_night_rate' },
  //   { metro: 0, date: 'Saturday ', regional: 0, metroName: 'award_metro_sat_day_rate', regionalName: 'award_reg_sat_day_rate' },
  //   { metro: 0, date: 'Sunday ', regional: 0, metroName: 'award_metro_sun_day_rate', regionalName: 'award_reg_sun_day_rate' },
  //   { metro: 0, date: 'Public Holiday ', regional: 0, metroName: 'award_metro_pub_holi_day_rate', regionalName: 'award_reg_pub_holi_day_rate' },
  // ]

  levels = [
    { value: 1, label: 'Level 1' },
    { value: 2, label: 'Level 2' },
    { value: 3, label: 'Level 3' },
    { value: 4, label: 'Level 4' },
    { value: 5, label: 'Level 5' }
  ];

  constructor(private ngbActiveModal: NgbActiveModal, public siteService: SiteService, 
    public chargeRateService: ChargeRateService, 
    private toastService: ToastServiceService,
    public dateAdapter: DateAdapter<Date>,
  ) {
    this.dateAdapter.setLocale('en-AU');
  }

  ngOnInit(): void {
    console.log(this.data);

    this.siteService.getCustomer().subscribe(res => {
      this.customerList = res.data;
    })

    this.customer = Number(this.data.rates.customer_id);
    this.state = this.data.rates.state;
    this.position = this.data.rates.position;
    // this.level = this.data.rates.level;
    this.title = this.data.rates.title;
    this.ot_base_rate = this.data.rates.ot_base_rate;
    this.level = Number(this.data.rates.level);
    this.effective_date = this.data.rates.effective_date 
      ? new Date(this.data.rates.effective_date.replace(/\//g, "-")) 
      : null;

    // def metro 

    this.chargeListMetroRegional[0].metro = this.data.rates.flat_metro_week_day_day;
    this.chargeListMetroRegional[1].metro = this.data.rates.flat_metro_week_day_night;
    this.chargeListMetroRegional[2].metro = this.data.rates.flat_metro_saturday;
    this.chargeListMetroRegional[3].metro = this.data.rates.flat_metro_saturday_night;
    this.chargeListMetroRegional[4].metro = this.data.rates.flat_metro_sunday;
    this.chargeListMetroRegional[5].metro = this.data.rates.flat_metro_sunday_night;
    this.chargeListMetroRegional[6].metro = this.data.rates.flat_metro_public_holiday;
    this.chargeListMetroRegional[7].metro = this.data.rates.flat_metro_public_holiday_night;

    // def regional

    this.chargeListMetroRegional[0].regional = this.data.rates.flat_regional_week_day_day;
    this.chargeListMetroRegional[1].regional = this.data.rates.flat_regional_week_day_night;
    this.chargeListMetroRegional[2].regional = this.data.rates.flat_regional_saturday;
    this.chargeListMetroRegional[3].regional=this.data.rates.flat_regional_saturday_night;
    this.chargeListMetroRegional[4].regional = this.data.rates.flat_regional_sunday;
    this.chargeListMetroRegional[5].regional=this.data.rates.flat_regional_sunday_night;
    this.chargeListMetroRegional[6].regional = this.data.rates.flat_regional_public_holiday;
    this.chargeListMetroRegional[7].regional=this.data.rates.flat_regional_public_holiday_night;

    // Eba Metro

    this.charge_List_Metro_Regional_EBA[0].metro = this.data.rates.eba_metro_weekday_day;
    this.charge_List_Metro_Regional_EBA[1].metro = this.data.rates.eba_metro_weekday_night;
    this.charge_List_Metro_Regional_EBA[2].metro = this.data.rates.eba_metro_saturday_day;
    this.charge_List_Metro_Regional_EBA[3].metro=this.data.rates.eba_metro_saturday_night;
    this.charge_List_Metro_Regional_EBA[4].metro = this.data.rates.eba_metro_sunday_day;
    this.charge_List_Metro_Regional_EBA[5].metro=this.data.rates.eba_metro_sunday_night;
    this.charge_List_Metro_Regional_EBA[6].metro = this.data.rates.eba_metro_public_holiday;
    this.charge_List_Metro_Regional_EBA[7].metro=this.data.rates.eba_metro_public_holiday_night;

    // Eba regional

    this.charge_List_Metro_Regional_EBA[0].regional = this.data.rates.eba_regional_weekday_day;
    this.charge_List_Metro_Regional_EBA[1].regional = this.data.rates.eba_regional_weekday_night;
    this.charge_List_Metro_Regional_EBA[2].regional = this.data.rates.eba_regional_saturday_day;
    this.charge_List_Metro_Regional_EBA[3].regional=this.data.rates.eba_regional_saturday_night;
    this.charge_List_Metro_Regional_EBA[4].regional = this.data.rates.eba_regional_sunday_day;
    this.charge_List_Metro_Regional_EBA[5].regional=this.data.rates.eba_regional_sunday_night;
    this.charge_List_Metro_Regional_EBA[6].regional = this.data.rates.eba_regional_public_holiday;
    this.charge_List_Metro_Regional_EBA[7].regional=this.data.rates.eba_regional_public_holiday_night;


    // Award Metro

    // this.charge_List_Metro_Regional_Award[0].metro = this.data.rates.award_metro_mon_to_fri_day_rate;
    // this.charge_List_Metro_Regional_Award[1].metro = this.data.rates.award_metro_mon_to_fri_night_rate;
    // this.charge_List_Metro_Regional_Award[2].metro = this.data.rates.award_metro_sat_day_rate;
    // // this.charge_List_Metro_Regional_Award[3].metro=this.data.rates.award_metro_sat_night_rate;
    // this.charge_List_Metro_Regional_Award[3].metro = this.data.rates.award_metro_sun_day_rate;
    // // this.charge_List_Metro_Regional_Award[5].metro=this.data.rates.award_metro_sun_night_rate;
    // this.charge_List_Metro_Regional_Award[4].metro = this.data.rates.award_metro_pub_holi_day_rate;
    // // this.charge_List_Metro_Regional_Award[7].metro=this.data.rates.award_metro_pub_holi_night_rate;

    // Award regional

    // this.charge_List_Metro_Regional_Award[0].regional = this.data.rates.award_reg_mon_to_fri_day_rate;
    // this.charge_List_Metro_Regional_Award[1].regional = this.data.rates.award_reg_mon_to_fri_night_rate;
    // this.charge_List_Metro_Regional_Award[2].regional = this.data.rates.award_reg_sat_day_rate;
    // // this.charge_List_Metro_Regional_Award[3].regional=this.data.rates.award_reg_sat_night_rate;
    // this.charge_List_Metro_Regional_Award[3].regional = this.data.rates.award_reg_sun_day_rate;
    // // this.charge_List_Metro_Regional_Award[5].regional=this.data.rates.award_reg_sun_night_rate;
    // this.charge_List_Metro_Regional_Award[4].regional = this.data.rates.award_reg_pub_holi_day_rate;
    // // this.charge_List_Metro_Regional_Award[7].regional=this.data.rates.award_reg_pub_holi_night_rate;
  }

  close(dat: any) {
    console.log("dddddd ", dat);
    this.ngbActiveModal.close(dat);
  }

  // submit and update  charge RATE
  submitChargeRateForm(value, type, el?: HTMLElement) {
    this.submitted = true
    if (!this.title || !this.customer || !this.level || !this.state || !this.position || !this.effective_date) {
      el.scrollIntoView();
      return
    }
    else if (this.title && this.customer && this.level && this.state && this.position && this.effective_date) {
      if (type == 'submit') {
        let status = 'ChargeRate Operation';
        const formattedDate = this.effective_date ? moment(this.effective_date).format('YYYY/MM/DD') : null;
        const payload = {
          ...value, 
          effective_date: formattedDate,
          
        };
        // console.log("Submit charge rate", payload)
        this.chargeRateService.sendChargeRates(payload).subscribe(res => {
          if (res.success) {
            this.toastService.toastNotification(res.message, status)
          }
          else {
            this.toastService.toastNotification1(res.message, status)
          }
        }, (error => {
          this.toastService.toastNotification1('Something went wrong. Please try again later', 'Charge Rate!')
        }))
        this.close('add');
      }

      else {
        value.id = this.data?.rates?.id
        let status = 'ChargeRate Operation';
        const formattedDate = this.effective_date ? moment(this.effective_date).format('YYYY/MM/DD') : null;
        const payload = {
          ...value,
          effective_date: formattedDate,
        };
        // console.log("update data", payload)
        this.chargeRateService.updateChargeRates(payload).subscribe(res => {
          if (res.success == true) {
            this.toastService.toastNotification(res.message, status)
          }
          else {
            this.toastService.toastNotification1(res.message, status)
          }
        }, (error => {
          this.toastService.toastNotification1('Something went wrong. Please try again later', 'Charge Rate!')
        }))
        this.close('update');
      }
    }
  }

  getSpecificChargeRateWithLevel() {
    let data = {
      title: this.data?.rates.title,
      level: this.level
    }
    this.chargeRateService.getSpecificChargeRateWithLevel(data).subscribe(({ success, data }) => {
      if (success) {
        this.chargeListMetroRegional[0].metro = data?.def_metro_mon_to_fri_day_rate;
        this.chargeListMetroRegional[1].metro = data?.def_metro_mon_to_fri_night_rate;
        this.chargeListMetroRegional[2].metro = data?.def_metro_sat_day_rate;
        this.chargeListMetroRegional[3].metro = data?.def_metro_sun_day_rate;
        this.chargeListMetroRegional[4].metro = data?.def_metro_pub_holi_day_rate;
        // def regional
        this.chargeListMetroRegional[0].regional = data?.def_reg_mon_to_fri_day_rate;
        this.chargeListMetroRegional[1].regional = data?.def_reg_mon_to_fri_night_rate;
        this.chargeListMetroRegional[2].regional = data?.def_reg_sat_day_rate;
        this.chargeListMetroRegional[3].regional = data?.def_reg_sun_day_rate;
        this.chargeListMetroRegional[4].regional = data?.def_reg_pub_holi_day_rate;
        // Eba Metro
        this.charge_List_Metro_Regional_EBA[0].metro = data?.eba_metro_mon_to_fri_day_rate;
        this.charge_List_Metro_Regional_EBA[1].metro = data?.eba_metro_mon_to_fri_night_rate;
        this.charge_List_Metro_Regional_EBA[2].metro = data?.eba_metro_sat_day_rate;
        this.charge_List_Metro_Regional_EBA[3].metro = data?.eba_metro_sun_day_rate;
        this.charge_List_Metro_Regional_EBA[4].metro = data?.eba_metro_pub_holi_day_rate;
        // Eba regional
        this.charge_List_Metro_Regional_EBA[0].regional = data?.eba_reg_mon_to_fri_day_rate;
        this.charge_List_Metro_Regional_EBA[1].regional = data?.eba_reg_mon_to_fri_night_rate;
        this.charge_List_Metro_Regional_EBA[2].regional = data?.eba_reg_sat_day_rate;
        this.charge_List_Metro_Regional_EBA[3].regional = data?.eba_reg_sun_day_rate;
        this.charge_List_Metro_Regional_EBA[4].regional = data?.eba_reg_pub_holi_day_rate;
        // // Award Metro
        // this.charge_List_Metro_Regional_Award[0].metro = data?.award_metro_mon_to_fri_day_rate;
        // this.charge_List_Metro_Regional_Award[1].metro = data?.award_metro_mon_to_fri_night_rate;
        // this.charge_List_Metro_Regional_Award[2].metro = data?.award_metro_sat_day_rate;
        // this.charge_List_Metro_Regional_Award[3].metro = data?.award_metro_sun_day_rate;
        // this.charge_List_Metro_Regional_Award[4].metro = data?.award_metro_pub_holi_day_rate;
        // // Award regional
        // this.charge_List_Metro_Regional_Award[0].regional = data?.award_reg_mon_to_fri_day_rate;
        // this.charge_List_Metro_Regional_Award[1].regional = data?.award_reg_mon_to_fri_night_rate;
        // this.charge_List_Metro_Regional_Award[2].regional = data?.award_reg_sat_day_rate;
        // this.charge_List_Metro_Regional_Award[3].regional = data?.award_reg_sun_day_rate;
        // this.charge_List_Metro_Regional_Award[4].regional = data?.award_reg_pub_holi_day_rate;
      }
      else {
        this.toastService.toastNotification1('Charge Rate Not Found', 'Charge Rate!')
      }
    }, (error => {
      this.toastService.toastNotification1('Something went wrong. Please try again later', 'Charge Rate!')
    }))
  }
}

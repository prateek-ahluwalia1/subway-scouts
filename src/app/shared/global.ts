import { ElementRef, Injectable, NgModule, ViewChild } from "@angular/core";
import { ToastServiceService } from "app/services/toast-service.service";
import moment from "moment";
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from "@costlydeveloper/ngx-awesome-popup";
import { BehaviorSubject, Observable } from "rxjs";

@NgModule({
  declarations: [],
  imports: []
})

@Injectable()

export class GlobalVariable {

  @ViewChild('beepAudio') beepAudioElement: ElementRef;
  portalSetting
  baseUrl = 'https://apis.subway.staffoo.com.au/api/';
  baseUrlCrm = 'https://apis.subway.staffoo.com.au/crm/';
  rosterFour_SelectedWeek = [];
  rosterFourSelectedDate
  // for checking which page link is activated
  activeNavigationPage
  selected_week = 'site-week';
  sitesList
  weekType
  twoWeekEnd
  entoSelectedWeek = [];
  selectedSitesIds: number[] = [];
  selectedCardIds: number[] = [];
  selectedGuardIds: number[] = [];
  shift_paste: boolean = false;
  isContextMenuDisabled: boolean = false;
  entoSelectedDate;
  guard_id
  roster_id
  start
  end
  getWeekDays: any = [];
  getTwo_WeeksDays = []
  selectedDate;
  check_url = ''
  documentsList: any = [];
  currentWeekend: string;
  roster_three_sidebar = true
  roster_three_shift_component = false
  latsDayInTwoWeek: any = [];
  firstDayInTwoWeek: any = [];
  messageTemplates = []
  selectedCustomer: any
  selectedStaff: any
  selectedCustomers: any
  selectedSite: any
  documentViewEnable: boolean = false
  admin
  // this.addShiftBasicForm.value.admin_id = admin_id.admin_id
  selectedGuard: any
  selectedGuards: any
  //timestamp access to pooling
  timestamp = Date.now();
  routeId
  ActivateUrl
  //signInOut data
  signInOut = [];
  queryParamsOfForm: { title: string; body: string; }
  showCrmTab: boolean = false
  permissions: any = []
  unreadNotes = 0
  apiError = 'Something went wrong. Please contact with support team.'
  siteSelectedForRoster: any
  user_type :any
  states = [
    { name: 'Victoria', value: 'vic' },
    { name: 'New South Wales', value: 'nsw' },
    { name: 'Tasmania', value: 'tas' },
    { name: 'Queensland', value: 'qld' },
    { name: 'Western Australia', value: 'wa' },
    { name: 'South Australia', value: 'sa' },
    { name: 'ACT', value: 'act' }
  ];
  private selectedOptionSubject = new BehaviorSubject<string>('');
  private selectedCustomerOptionSubject = new BehaviorSubject<string>('');
  selectedOption$: Observable<string> = this.selectedOptionSubject.asObservable();
  selectedCustomerOption$: Observable<string> = this.selectedCustomerOptionSubject.asObservable();
  selectTabCrm:string  = 'dashboard'

  constructor(private toast: ToastServiceService) {
    const data =JSON.parse(localStorage.getItem('admin'))
    this.user_type = data?.admin_user_type
  }

  reset(): void {
    this.portalSetting = null;
    this.baseUrl = 'https://apis.thescouts.com.au/api/';
    this.baseUrlCrm = 'https://apis.thescouts.com.au/crm/';
    this.rosterFour_SelectedWeek = [];
    this.rosterFourSelectedDate = null;
    this.activeNavigationPage = '';
    this.selected_week = 'site-week';
    this.sitesList = null;
    this.weekType = null;
    this.twoWeekEnd = null;
    this.entoSelectedWeek = [];
    this.selectedSitesIds = [];
    this.selectedGuardIds = [];
    this.entoSelectedDate = null;
    this.guard_id = null;
    this.roster_id = null;
    this.start = null;
    this.end = null;
    this.getWeekDays = [];
    this.getTwo_WeeksDays = [];
    this.selectedDate = null;
    this.check_url = '';
    this.documentsList = [];
    this.currentWeekend = '';
    this.roster_three_sidebar = true;
    this.roster_three_shift_component = false;
    this.latsDayInTwoWeek = [];
    this.firstDayInTwoWeek = [];
    this.messageTemplates = [];
    this.selectedCustomer = null;
    this.selectedCustomers = null;
    this.selectedSite = null;
    this.documentViewEnable = false;
    this.admin = null;
    this.selectedGuard = null;
    this.selectedGuards = null;
    this.timestamp = Date.now();
    this.routeId = null;
    this.ActivateUrl = null;
    this.signInOut = [];
    this.queryParamsOfForm = { title: '', body: '' };
    this.showCrmTab = false;
    this.permissions = [];
    this.unreadNotes = 0;
  }

  // Updated AddTimeDate function
  AddTimeDate(starts, ends, day) {
    const newStart = moment(starts, "HH:mm");
    const newEnd = moment(ends, "HH:mm");
    if (newStart.isSame(newEnd)) {
      const status = 'null';
      const msg = 'Start and End time cannot be equal';
      return { status, msg };
    } else {
      const start = day + " " + starts;
      if (newEnd < newStart) {
        newEnd.startOf('day');
        const endTime = moment(day).add(1, 'day');
        const end = endTime.format("MM-DD-YYYY") + " " + ends;
        return { start, end };
      } else {
        const end = day + " " + ends;
        return { start, end };
      }
    }
  }

  // common in one and two week
  manualChargeRate = [
    { date: 'Mon-Fri (Day 06:00 - 18:00)', regional: 0, chargerateName: 'chargerate_mon_to_fri_day_rate' },
    { date: 'Mon-Fri (Night 18:00 - 06:00)', regional: 0, chargerateName: 'chargerate_mon_to_fri_night_rate' },
    { date: 'Saturday ', regional: 0, chargerateName: 'chargerate_sat_day_rate' },
    { date: 'Sunday ', regional: 0, chargerateName: 'chargerate_sun_day_rate' },
    { date: 'Public Holiday ', regional: 0, chargerateName: 'chargerate_pub_holi_day_rate' },
  ]
  manualPayRate = [
    { date: 'Mon-Fri (Day 06:00 - 18:00)', regional: 0, payrateName: 'payrate_mon_to_fri_day_rate' },
    { date: 'Mon-Fri (Night 18:00 - 06:00)', regional: 0, payrateName: 'payrate_mon_to_fri_night_rate' },
    { date: 'Saturday ', regional: 0, payrateName: 'payrate_sat_day_rate' },
    { date: 'Sunday ', regional: 0, payrateName: 'payrate_sun_day_rate' },
    { date: 'Public Holiday ', regional: 0, payrateName: 'payrate_pub_holi_day_rate' },
  ]

  playBeepSound() {
    let audio = new Audio();
    audio.src = "../../assets/images/notification.wav";
    audio.load();
    // Add an error event listener
    audio.addEventListener('error', (event) => {
      console.error('Audio playback error:', event);
      // Retry playing the audio after a delay (e.g., 1 second)
      setTimeout(() => {
        audio.load();
        audio.play();
      }, 1000); // Adjust the delay as needed
    });
    // Attempt to play the audio
    audio.play();
  }

  confirmationBox(title, message, yes, no): Observable<any> {
    const newConfirmBox = new ConfirmBoxInitializer();
    let layoutType = DialogLayoutDisplay.SUCCESS;
    let buttons: ButtonMaker[] = [];
    newConfirmBox.setTitle(title);
    newConfirmBox.setMessage(message);
    buttons = [
      new ButtonMaker(no, no, ButtonLayoutDisplay.DANGER),
      new ButtonMaker(yes, yes, ButtonLayoutDisplay.SUCCESS)
    ];
    newConfirmBox.setConfig({
      layoutType,
      animationIn: AppearanceAnimation.ZOOM_IN_ROTATE,
      animationOut: DisappearanceAnimation.ZOOM_OUT_WIND,
      allowHtmlMessage: true,
      buttonPosition: 'center',
    });
    newConfirmBox.setButtons(buttons);
    // Return the observable from openConfirmBox$
    return newConfirmBox.openConfirmBox$();
  }

  updateSelectedOption(option: string) {
    this.selectedOptionSubject.next(option);
  }

  updateCustomerSingleOption(option: string) {
    this.selectedOptionSubject.next(option);
  }

}
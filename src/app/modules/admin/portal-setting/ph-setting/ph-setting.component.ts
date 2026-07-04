import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CalendarOptions } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import { FullCalendarComponent } from '@fullcalendar/angular'; // Import FullCalendarComponent
import interactionPlugin from '@fullcalendar/interaction';
import { ButtonLayoutDisplay, ButtonMaker, DialogInitializer, DialogLayoutDisplay } from '@costlydeveloper/ngx-awesome-popup';
import { GlobalVariable } from 'app/shared/global';
import { PortalSettingsService } from '../portal-settings.service';
import { DemoComponent } from './demo/demo.component';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

@Component({
  selector: 'app-ph-setting',
  templateUrl: './ph-setting.component.html',
  styleUrls: ['./ph-setting.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})

export class PhSettingComponent implements OnInit, OnDestroy {

  @ViewChild('fullcalendar') fullcalendar!: FullCalendarComponent;

  calendarOptions: CalendarOptions = {
    initialView: 'dayGridMonth',
    plugins: [dayGridPlugin, interactionPlugin],
    events: [],
    eventClick: this.handleEventClick.bind(this),
    dateClick: this.handleDateClick.bind(this)
  };

  publicHolidays: any[] = [];
  selectedState = 'vic'
  constructor(private http: HttpClient, private service: PortalSettingsService, public global: GlobalVariable,
    private cdr: ChangeDetectorRef, private trackAdmin: TrackAdminActivityService,) {
    this.getPh()
    // this.fetchPublicHolidays();
  }
  
  ngOnInit(): void {
    if (!localStorage.getItem('routerId')) {
      this.activity()
    }
  }

  handleEventClick(info: any) {
    console.log('Event clicked:', info.event);
    console.log('Event clicked:', info.event.start);
    const dialogPopup = new DialogInitializer(DemoComponent);
    dialogPopup.setConfig({
      layoutType: DialogLayoutDisplay.INFO // SUCCESS | INFO | NONE | DANGER | WARNING

    });
    dialogPopup.setCustomData({ data: info.event, type: 'update' });
    dialogPopup.setButtons([
      new ButtonMaker('Close', 'close', ButtonLayoutDisplay.DARK),
      new ButtonMaker('Update', 'update', ButtonLayoutDisplay.SUCCESS),
      new ButtonMaker('Delete', 'delete', ButtonLayoutDisplay.DANGER),
    ]);
    dialogPopup.openDialog$().subscribe(resp => {
      if (resp.clickedButtonID == "update" || resp.clickedButtonID == "delete") {
        this.getPh()
      }
    });
  }

  handleDateClick(arg: any) {
    const dialogPopup = new DialogInitializer(DemoComponent);
    dialogPopup.setConfig({
      layoutType: DialogLayoutDisplay.INFO // SUCCESS | INFO | NONE | DANGER | WARNING

    });
    dialogPopup.setCustomData({ date: arg.dateStr });
    dialogPopup.setButtons([
      new ButtonMaker('Close', 'close', ButtonLayoutDisplay.DARK),
      new ButtonMaker('Save', 'save', ButtonLayoutDisplay.SUCCESS),
    ]);
    dialogPopup.openDialog$().subscribe(resp => {
      if (resp.clickedButtonID == 'save') {
        this.getPh()
      }
    });
  }

  ngAfterViewInit() {

  }

  getPh() {
    this.service.getPH(this.selectedState).subscribe(
      (res) => {
        console.log(res);
        this.calendarOptions.events = res?.data.map((event) => ({
          id: event.id,
          title: event.holiday_name,
          info: event.information,
          start: event.date,
          state: event.state,
          date: event.date,
        }));
        this.fullcalendar.getApi().render();
        this.cdr.markForCheck()
      },
      (error) => {
        console.error('Error fetching events:', error);
      }
    );
  }

  filterState(value) {
    console.log(value);
    this.selectedState = value
    this.getPh()
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity('Public Holidays', 'Exite Public Holidays Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Public Holidays', `Enter in Public Holidays`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }
}

import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GuardLicenseService } from 'app/services/guard-license.service';
declare var google

@Component({
  selector: 'app-time-clock',
  templateUrl: './time-clock.component.html',
  styleUrls: ['./time-clock.component.scss']
})

export class TimeClockComponent implements OnInit {

  @ViewChild('map') mapElement: ElementRef;
  latitude = -37.8136;
  longitude = 144.9631;
  map: any;
  searchTerm: string
  guards = [
    // {state_name:'Victoria',staff_name:'Usman Bhatti',start_date:'10-10-2023', start_time:'03:30',finish_time:'05:40',green_call_1:'22',green_call_2:'345', clock_in_time:'45:40',start_break_time:'4',end_break_time:'44',clock_time_out:'44',total_hours:'40',  latitude:31.634664, longitude:74.351998},
    // {state_name:'Victoria',staff_name:'Naveed',start_date:'10-10-2023', start_time:'03:30',finish_time:'05:40',green_call_1:'22',green_call_2:'345', clock_in_time:'45:40',start_break_time:'4',end_break_time:'44',clock_time_out:'44',total_hours:'40', longitude: 74.362571, latitude: 31.638849},
  ]

  first_green_call: any;
  second_green_call: any;
  id;
  type;
  data;
  routeId
  isMapVisible: boolean = false;

  constructor(private guardLicense: GuardLicenseService, private globals: GlobalVariable, private router: Router,
    private trackAdmin: TrackAdminActivityService) {

    const activatedUrl = this.router.url;
    this.globals.ActivateUrl = activatedUrl
    console.log(activatedUrl);
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
    this.guardLicense.getTimeClock().subscribe(({ success, data }) => {
      if (success) {
        this.guards = data;
        // setTimeout(() => {
        //   this.loadMap()
        // }, 500)
      }
    });
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');
    this.trackAdmin.storeActivity('Exit Time Clock Page', 'Exit Time Clock Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Time Clock Page', 'Enter in Time Clock Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  loadMap() {
    console.log(this.mapElement);
    let mapOptions = {
      center: {
        lat: this.latitude,
        lng: this.longitude,
      },
      zoom: 10,
      draggable: true,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
    };

    this.map = new google.maps.Map(this.mapElement.nativeElement, mapOptions);
    for (let i = 0; i < this.guards.length; i++) {
      let guard = this.guards[i];
      // console.log("coordinates", guard.lat, guard.lng);
      let content = `<div><img style='width:50px;height:50px;margin:auto;' src='${guard.image}'> <br> ${guard.staff_name}</div>`;
      setTimeout(() => {
        this.addMarker(guard.lat, guard.lng, content);
      }, 1000);
    }
  }

  addMarker(lat: number, lng: number, content: string) {
    // console.log('Marker Here', lat, lng);
    let latLng = new google.maps.LatLng(lat, lng);
    let marker = new google.maps.Marker({
      map: this.map,
      animation: google.maps.Animation.DROP,
      position: latLng,
      draggable: false,
      icon: 'assets/icons/map.png'
    });

    // let content = `<div><img style='width:50px;height:50px;margin:auto;' src='${'https://mdbootstrap.com/img/new/avatars/8.jpg'}'> <br> Usman Bhatti</div>`;
    //add username here

    this.addInfoWindow(marker, content);
  }

  addInfoWindow(marker, content) {
    let infoWindow = new google.maps.InfoWindow({
      content: content,
    });

    google.maps.event.addListener(marker, 'mouseover', () => {
      infoWindow.open(this.map, marker);
    });

    google.maps.event.addListener(marker, 'mouseout', () => {
      infoWindow.close();
    });
  }

  toggleMap() {
    this.isMapVisible = !this.isMapVisible;
    if (this.isMapVisible) {
      setTimeout(() => {
        this.loadMap();
      }, 500);
    }
  }

}

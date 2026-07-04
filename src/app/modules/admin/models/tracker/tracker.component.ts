import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";
import { GlobalVariable } from "app/shared/global";
import { JobRoster1Service } from "app/services/job-roster1.service";
declare var google;
@Component({
  selector: 'app-tracker',
  templateUrl: './tracker.component.html',
  styleUrls: ['./tracker.component.scss']
})
export class TrackerComponent implements OnInit {
  @ViewChild("map4") mapElement4: ElementRef;

  constructor(public modalService: NgbActiveModal, private globals: GlobalVariable,
    private rosterService: JobRoster1Service) { }
  trackers: any = [];

  map4: any;

  ngOnInit(): void {
    let data = {
      guard_id: this.globals.guard_id,
      roster_id: this.globals.roster_id
    }

    this.rosterService.trackerCall(data).subscribe(({ success, data }) => {
      if (success) {
        this.trackers = data;
        setTimeout(() => {
          this.initMap();
        }, 100);
      }
    });
  }


  close() {
    this.modalService.dismiss();
  }



  initMap() {
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
      console.error('Google Maps API is not loaded.');
      return;
    }
  
    const center = { lat: -37.8136, lng: 144.9631 };
    
    const mapOptions = {
      center: center,
      zoom: 15,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
    };
    if(this.mapElement4){
      this.map4 = new google.maps.Map(this.mapElement4.nativeElement, mapOptions);
  
    this.trackers.forEach(element => {
      const locationString = element?.coordinates;
      const [lat, lng] = locationString?.split(",") || [];
      const latValue = parseFloat(lat);
      const lngValue = parseFloat(lng);
  
      if (!isNaN(latValue) && !isNaN(lngValue)) {
        const marker = new google.maps.Marker({
          position: { lat: latValue, lng: lngValue },
          map: this.map4,
          title: element.event_time,
        });
      }
    });
    }
  }
  


}

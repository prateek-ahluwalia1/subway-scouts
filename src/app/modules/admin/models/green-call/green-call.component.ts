import { Component, OnInit, ViewChild, ElementRef, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { GlobalVariable } from 'app/shared/global';
declare var google;

@Component({
  selector: 'app-green-call',
  templateUrl: './green-call.component.html',
  styleUrls: ['./green-call.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})

export class GreenCallComponent implements OnInit {

  @ViewChild("map3") mapElement3: ElementRef;
  geo: any;
  map3: any;
  isMapVisible: boolean = false;
  greenCalls: any = [];

  constructor(public modalService: NgbActiveModal, private jobRoster: JobRoster1Service, private globals: GlobalVariable,
    private changeDetect: ChangeDetectorRef) { }
  
  ngOnInit(): void {
    let data = {
      guard_id: this.globals.guard_id,
      roster_id: this.globals.roster_id
    }
    this.jobRoster.greenCall(data).subscribe(({ success, data }) => {
      if (success) {
        this.greenCalls = data;
        // setTimeout(() => {
        //   this.initWelfareMap();
        // }, 100);
      }
      this.changeDetect.markForCheck()
    })
  }

  initWelfareMap() {
    this.greenCalls?.forEach((element, index) => {
      const locationString = element?.coordinates;
      if (locationString) {
        const [lat, lng] = locationString?.split(",");
        const mapOptions = {
          center: new google.maps.LatLng(parseFloat(lat), parseFloat(lng)), // Set a default center if needed
          zoom: 15,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
        };
        this.map3 = new google.maps.Map(this.mapElement3.nativeElement, mapOptions);
        const marker = new google.maps.Marker({
          position: { lat: parseFloat(lat), lng: parseFloat(lng) },
          map: this.map3,
          title: "Staff Response on Green call",
          icon: {
            url: "assets/icons/jobshiftActivity/map-icon.png",
            size: new google.maps.Size(38, 38),
            labelOrigin: new google.maps.Point(13, 26),
          },
          label: {
            text: String(index + 1),
            color: "white",
            fontWeight: "bold",
            fontSize: "12px",
          },
        });
      }
    });
    this.changeDetect.markForCheck()
  }

  close() {
    this.modalService.dismiss();
  }

  toggleMap() {
    this.isMapVisible = !this.isMapVisible;
    if (this.isMapVisible) {
      setTimeout(() => {
        this.initWelfareMap();
      }, 500);
    }
  }

}

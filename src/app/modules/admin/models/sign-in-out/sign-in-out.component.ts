import { Component, ElementRef, OnInit, ViewChild } from "@angular/core";
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";
import { GlobalVariable } from "app/shared/global";
import { JobRoster1Service } from 'app/services/job-roster1.service';
declare var google;

@Component({
  selector: "app-sign-in-out",
  templateUrl: "./sign-in-out.component.html",
  styleUrls: ["./sign-in-out.component.scss"],
})

export class SignInOutComponent implements OnInit {

  @ViewChild("map") mapElement: ElementRef;
  @ViewChild("map2") mapElement2: ElementRef;
  geo: any;
  map: any;
  map2: any;
  datas: any = []
  showPlaceholderImage = false;
  showSignInMap = false;
  showSignOutMap = false;

  constructor(public modalService: NgbActiveModal, public globals: GlobalVariable, private jobRoster: JobRoster1Service) { }

  ngOnInit(): void {
    let data = {
      guard_id: this.globals.guard_id,
      job_id: this.globals.roster_id
    }
    this.jobRoster.jobSignInOut(data).subscribe(({ success, data }) => {
      if (success) {
        this.datas = data
        // this.initMap();
        // this.initMap2();
      }
    })
  }

  initMap() {
    const locationString = this.datas?.signin_location;
    if (locationString) {
      const [lat, lng] = locationString.split(",");
      const coords = new google.maps.LatLng(lat, lng);

      const mapOptions = {
        center: coords,
        zoom: 15,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
      };
      this.map = new google.maps.Map(this.mapElement.nativeElement, mapOptions);

      const marker = new google.maps.Marker({
        map: this.map,
        position: coords,
        draggable: true,
        title: "Staff Sign-In Location",
      });

    } else {
      console.log("Invalid location string");
    }
  }

  initMap2() {
    const locationString = this.datas?.signout_location;
    if (locationString) {
      const [lat, lng] = locationString.split(",");
      const coords = new google.maps.LatLng(lat, lng);

      const mapOptions = {
        center: coords,
        zoom: 15,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
      };
      const map2 = new google.maps.Map(this.mapElement2.nativeElement, mapOptions);

      const marker = new google.maps.Marker({
        map: map2,
        position: coords,
        draggable: true,
        title: "Staff Sign-Out Location",
      });
    } else {
      console.log("Invalid location string");
    }
  }

  close() {
    this.modalService.dismiss();
  }

  handleImageError(event: any) {
    const imgElement = event.target as HTMLImageElement;
    imgElement.src = 'assets/images/no_logout_image_emoji.png';
    event.target.style.objectFit = 'cover';
  }

  getDisplayValue(value: any): string {
    return value === null || value === undefined || value === '' || value === 'undefined' ? 'N/A' : value;
  }

  loadSignInMap() {
    this.showSignInMap = !this.showSignInMap;
    if (this.showSignInMap) {
      setTimeout(() => this.initMap(), 200);
    }
  }

  loadSignOutMap() {
    this.showSignOutMap = !this.showSignOutMap;
    if (this.showSignOutMap) {
      setTimeout(() => this.initMap2(), 200);
    }
  }

}

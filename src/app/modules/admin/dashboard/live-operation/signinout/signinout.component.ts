import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';

declare var google: any;

@Component({
  selector: 'app-signinout',
  templateUrl: './signinout.component.html',
  styleUrls: ['./signinout.component.scss']
})

export class SigninoutComponent implements OnInit {
  
  @ViewChild("map") mapElement: ElementRef;
  @ViewChild("map2") mapElement2: ElementRef;
  datas: any = [];
  showPlaceholderImage = false;
  @Input() data;
  showSignInMap = false;
  showSignOutMap = false;

  constructor(
    public modalService: NgbActiveModal,
    public globals: GlobalVariable,
    private jobRoster: JobRoster1Service,
    private toast: ToastServiceService
  ) { }

  ngOnInit(): void {
    let data = {
      guard_id: this.data.guard_id,
      job_id: this.data.roster_id
    };

    if (data) {
      this.jobRoster.jobSignInOut(data).subscribe(({ success, data }) => {
        if (success) {
          this.datas = data;
          // this.initMap();
          // this.initMap2();
        }
      });
    } else {
      this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Request Incomplete!');
    }
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
      const map = new google.maps.Map(this.mapElement.nativeElement, mapOptions);

      const marker = new google.maps.Marker({
        map: map,
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
    console.log(locationString);

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

  loadSignInMap() {
    this.showSignInMap = !this.showSignInMap;
    if (this.showSignInMap) {
        setTimeout(() => this.initMap(), 500);
    }
  }

  loadSignOutMap() {
    this.showSignOutMap = !this.showSignOutMap;
    if (this.showSignOutMap) {
        setTimeout(() => this.initMap2(), 500);
    }
  }
}

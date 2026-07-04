import { Component, OnInit, ViewChild, ElementRef, ChangeDetectionStrategy, ChangeDetectorRef } from "@angular/core";
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";
import { GlobalVariable } from "app/shared/global";
import { JobRoster1Service } from "app/services/job-roster1.service";
declare var google;

@Component({
  selector: "app-welfare-call",
  templateUrl: "./welfare-call.component.html",
  styleUrls: ["./welfare-call.component.scss"],
  changeDetection: ChangeDetectionStrategy.OnPush,
})

export class WelfareCallComponent implements OnInit {

  @ViewChild("map3") mapElement3: ElementRef;
  isMapVisible: boolean = false;
  geo: any;
  map3: any;
  welfares: any = [];
  coords;

  constructor(
    public modalService: NgbActiveModal,
    private globals: GlobalVariable,
    private rosterService: JobRoster1Service,
    private changeDetect: ChangeDetectorRef
  ) { }

  ngOnInit(): void {
    this.showdata()
  }

  showdata() {
    let data = {
      guard_id: this.globals.guard_id,
      roster_id: this.globals.roster_id
    }
    this.rosterService.welfareCall(data).subscribe((res) => {
      if (res.success) {
        this.welfares = res.data;
        // setTimeout(() => {
        //   this.initWelfareMap();
        // }, 100);
        this.changeDetect.markForCheck()
      }
    })
  }

  initWelfareMap() {
    this.welfares.forEach((element, index) => {
      const locationString = element?.coordinates;
      if (locationString) {
        const [lat, lng] = locationString?.split(",");
        const mapOptions = {
          center: new google.maps.LatLng(parseFloat(lat), parseFloat(lng)),
          zoom: 15,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
        };
        this.map3 = new google.maps.Map(this.mapElement3.nativeElement, mapOptions);
        const marker = new google.maps.Marker({
          position: { lat: parseFloat(lat), lng: parseFloat(lng) },
          map: this.map3,
          title: "Staff Response on welfare call",
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
      }, 100);
    }
  }

}

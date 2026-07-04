import { ChangeDetectionStrategy, Component, Input, OnInit } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import moment from 'moment';
import { NgxSpinnerService } from 'ngx-spinner';
export class Customers {
  id: number;
  name: string;
}
export class Locations {
  id: number;
  site_name: string;
}
declare const google: any;

@Component({
  selector: 'app-broadcast-job',
  templateUrl: './broadcast-job.component.html',
  styleUrls: ['./broadcast-job.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BroadcastJobComponent implements OnInit {

  @Input() customers;
  isShowMap: boolean = false
  guardInRadius: any[] = []
  radius: number;
  ad_hoc_formData = { state: '', job_instrcutions: '', address: '', coordinates: '', customer_id: '', site_id: '', start_date: '', start_time: '', end_date: '', end_time: '' };
  locations: Locations[] = [];
  map;
  marker;
  circle
  constructor(private modalService: NgbModal, public userService: StaffService, private rosterService: JobRoster1Service,
    private toast: ToastServiceService, private spinner: NgxSpinnerService) {
    this.radius = 10;
  }

  ngOnInit(): void {
  }

  close(data) {
    this.modalService.dismissAll(data)
  }

  receiveDataFromChildCustomer(data: any) {
    let id = data.value.id
    this.ad_hoc_formData.customer_id = id
    this.userService.getCusSite([id]).subscribe(res => {
      console.log(res);

      if (res.success) {
        this.locations = res.data
      }
    })
  }

  receiveDataFromChildSiteAdhos(event) {
    console.log(event);

    this.isShowMap = true;
    this.ad_hoc_formData.site_id = event.id
    this.ad_hoc_formData.address = event.address
    this.ad_hoc_formData.coordinates = event.coordinates
    this.ad_hoc_formData.state = event.state
    let params = {
      coordinates: event.coordinates,
      radius: this.radius
    }
    if (event.coordinates) {
      this.rosterService.getAdhocGuard(params).subscribe(({ success, data }) => {
        if (success) {
          this.guardInRadius = data
          const mapEl = document.getElementById('map') as HTMLElement;
          if (mapEl) {
            this.initMap(mapEl);
          } else {
            console.error('Map element not found!');
          }
        }
        else if (!success) {
          this.guardInRadius = []
        }

      })
    }
  }

  submitForm() {
    this.spinner.show()
    if (this.ad_hoc_formData.customer_id == '' || this.ad_hoc_formData.site_id == '') {
      this.toast.toastNotification1('Please select Customer and Site', 'Invalid Form')
      this.spinner.hide()
      return;
    }
    const startDate = moment(this.ad_hoc_formData.start_date);
    const newStartDate = startDate.format('MM-DD-YYYY');
    const newStartTime = this.ad_hoc_formData.start_time;
    const newStart = moment(`${newStartDate} ${newStartTime}`, 'MM-DD-YYYY HH:mm');

    const endDate = moment(this.ad_hoc_formData.end_date);
    const newEndDate = endDate.format('MM-DD-YYYY');
    const newEndTime = this.ad_hoc_formData.end_time;
    const newEnd = moment(`${newEndDate} ${newEndTime}`, 'MM-DD-YYYY HH:mm');

    const maxDuration = moment.duration(12, 'hours');
    const duration = moment.duration(newEnd.diff(newStart));

    if (newEnd.isAfter(newStart) && duration.asMilliseconds() <= maxDuration.asMilliseconds()) {
      const modifiedData = {
        ...this.ad_hoc_formData,
        new_start: newStart.format('MM-DD-YYYY HH:mm'),
        new_end: newEnd.format('MM-DD-YYYY HH:mm'),
        radius: this.radius,
        roster_id: parseInt(localStorage.getItem('rosterId')),
        guards: this.guardInRadius
      };

      this.rosterService.adHocShift(modifiedData).subscribe(({ success, message }) => {
        if (success) {
          this.toast.toastNotification(message, 'ASAP Job!')
          this.close('add')
        }
        this.spinner.hide()
      })
    } else {
      this.toast.toastNotification1('Error: End date and time must be within 12 hours of start date and time.', 'Time Conflict')
      this.spinner.hide()
      return;
    }
  }


  // ad-hoc shift code end
  initMap(mapElement: HTMLElement): void {

    const kilometersToMiles = (kilometers: number): number => {
      return kilometers * 0.621371; // 1 kilometer = 0.621371 miles
    };

    // Convert radius to miles
    const milesRadius = kilometersToMiles(this.radius);

    const coordinates = this.ad_hoc_formData.coordinates;
    const [latitude, longitude] = coordinates.split(",");

    const parsedLatitude = parseFloat(latitude);
    const parsedLongitude = parseFloat(longitude);

    if (!isNaN(parsedLatitude) && !isNaN(parsedLongitude)) {
      const mapOptions = {
        center: { lat: parsedLatitude, lng: parsedLongitude },
        zoom: 13
      };

      this.map = new google.maps.Map(mapElement, mapOptions);

      this.marker = new google.maps.Marker({
        position: { lat: parsedLatitude, lng: parsedLongitude },
        map: this.map,
        title: 'Site Location'
      });

      const metersRadius = milesRadius * 1609.34; // Convert miles to meters

      this.circle = new google.maps.Circle({
        map: this.map,
        radius: metersRadius,
        center: this.marker.getPosition(),
        fillColor: '#007bff',
        fillOpacity: 0.2,
        strokeColor: '#007bff',
        strokeOpacity: 0.5,
      });

      // Update circle radius when the map zoom changes
      this.map.addListener('zoom_changed', () => {
      });
      this.showUsersInRadius(this.marker);
      google.maps.event.addListenerOnce(this.map, 'idle', () => {
        // this.showUsersInRadius();
      });
    } else {
      // Handle invalid coordinates
      console.error('Invalid coordinates:', coordinates);
    }
  }


  // ad-hoc shift code end
  updateCircleRadius(radius): void {
    var sign_radius = Number(radius.target.value)
    this.radius = sign_radius;
    if (this.ad_hoc_formData.site_id != '') {
      let params = {
        coordinates: this.ad_hoc_formData.coordinates,
        radius: this.radius
      }
      if (this.ad_hoc_formData.coordinates) {
        this.rosterService.getAdhocGuard(params).subscribe(({ success, data }) => {
          if (success) {
            this.guardInRadius = data
            const mapEl = document.getElementById('map') as HTMLElement;
            this.initMap(mapEl)
          }
          else if (!success) {
            this.guardInRadius = []
          }
        })
      }

    }
  }

  showUsersInRadius(marker): void {
    const usersInRadius = [];

    for (const user of this.guardInRadius) {
      const [lat, lng] = user.coordinates.split(",");

      const userPosition = new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
      const distanceInMeters = google.maps.geometry?.spherical?.computeDistanceBetween(marker?.getPosition(), userPosition);
      const distanceInMiles = distanceInMeters * 0.000621371; // Convert distance from meters to miles
      if (distanceInMiles <= this.radius) {
        usersInRadius.push(user);
        let markerIcon;
        let title;
        if (user.profile_image && user.profile_image !== "") {
          markerIcon = {
            url: user.profile_image,
            scaledSize: new google.maps.Size(40, 40),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(20, 20),
          };
          title = user.first_name + ' ' + user.last_name;
        } else {
          const canvas = document.createElement('canvas');
          canvas.width = 40;
          canvas.height = 40;
          const context = canvas.getContext('2d');
          context.fillStyle = '#007bff';
          context.beginPath();
          context.arc(20, 20, 20, 0, 2 * Math.PI);
          context.fill();
          context.fillStyle = '#ffffff';
          context.font = '16px Arial';
          context.textAlign = 'center';
          context.textBaseline = 'middle';
          context.fillText(user.first_name.charAt(0).toUpperCase() + user.last_name.charAt(0).toUpperCase(), 20, 20);
          const roundedAvatarUrl = canvas.toDataURL();
          markerIcon = {
            url: roundedAvatarUrl,
            scaledSize: new google.maps.Size(40, 40),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(20, 20),
          };
          title = user.first_name + ' ' + user.last_name;
        }
        const userMarker = new google.maps.Marker({
          position: userPosition,
          map: this.map,
          icon: markerIcon,
          title: title,
        });

        // Rest of your code...
      }
    }

    console.log('Users in radius:', usersInRadius);
  }

}

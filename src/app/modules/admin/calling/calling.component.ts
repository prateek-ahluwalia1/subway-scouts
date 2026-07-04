import { NgbModal } from "@ng-bootstrap/ng-bootstrap";
import { AircalService } from "./../../../services/aircal.service";
import { AfterViewInit, Component, OnInit, ViewChild } from "@angular/core";
import AircallPhone from "aircall-everywhere";
import { DomSanitizer } from "@angular/platform-browser";
import { ThemePalette } from "@angular/material/core";
import * as moment from "moment-timezone";
import { FormControl, FormGroup } from "@angular/forms";
import { MatTabChangeEvent } from "@angular/material/tabs";
import { GlobalVariable } from "app/shared/global";
import { TrackAdminActivityService } from "app/services/track-admin-activity.service";
import { DateAdapter } from "@angular/material/core";

@Component({
  selector: "app-calling",
  templateUrl: "./calling.component.html",
  styleUrls: ["./calling.component.scss"],
})

export class CallingComponent implements OnInit {

  searchCalls;
  range = new FormGroup({
    start: new FormControl<Date | null>(null),
    end: new FormControl<Date | null>(null),
  });
  selectedCall = "All";
  calls: string[] = ["All", "Inbound", "Outbound", "Missed calls"];

  guards = [
    // { name: 'Naveed Khan', email: 'mail@mail.com', phone: '032432434', requests: 4, wh: '0.00', aal: '0.00', asl: '0.00', ual: '0.00', },
    // { name: 'Usman Bhatti', email: 'mail@mail.com', phone: '032432434', requests: 0, wh: '0.00', aal: '0.00', asl: '0.00', ual: '0.00', },
    // { name: 'Wajhat Naqvi', email: 'mail@mail.com', phone: '032432434', requests: 2, wh: '0.00', aal: '0.00', asl: '0.00', ual: '0.00', },
    // { name: 'Rameez', email: 'mail@mail.com', phone: '032432434', requests: 1, wh: '0.00', aal: '0.00', asl: '0.00', ual: '0.00', },
  ];
  links = ["All"];
  activeLink = this.links[0];
  background: ThemePalette = "warn";
  phoneWindow: Window | null = null;
  integrationSettings: any = {};
  path: string | null = null;
  userSettings: any = {};
  eventsRegistered: any = {};
  phoneLoginState = false;
  phoneUrl = "https://phone.aircall.io";
  domToLoadPhone: string | undefined;
  integrationToLoad: string | undefined;
  debug = true;
  size = "big";

  private aircallPhone: AircallPhone;

  id;
  type;
  data;
  routeId;

  constructor(
    public aircalService: AircalService,
    private modalService: NgbModal,
    private sanitizer: DomSanitizer,
    public dateAdapter: DateAdapter<Date>,
    private global: GlobalVariable,
    private trackAdmin: TrackAdminActivityService
  ) {
    this.dateAdapter.setLocale("en-AU");

    let routerId = localStorage.getItem("routerId");
    if (!routerId) {
      this.activity();
    }
  }

  avalabilties = [
    { id: 1, name: "Available", value: "available" },
    { id: 2, name: "Custom", value: "custom" },
    { id: 3, name: "Unavailable", value: "unavailable" },
  ];
  numbers;
  filteredItems = [];
  searchTerm: string;
  contacts = [];

  ngOnInit(): void {
    this.getCalls();

    this.range.get("end").valueChanges.subscribe((value) => {
      // run your API call here using the selected value
      const from = Math.floor(this.range.value.start.getTime() / 1000);
      const to = Math.floor(value.getTime() / 1000);
      this.getCalls(from, to);
    });

    this.getUsers();
  }

  ngOnDestroy() {
    localStorage.removeItem("routerId");

    this.trackAdmin
      .storeActivity(
        "Exit Calling History Page",
        "Exit Calling History Page",
        this.routeId
      )
      .subscribe((res) => {});
  }

  activity() {
    this.trackAdmin
      .storeActivity("Calling History Page", "Enter in Calling History Page")
      .subscribe(({ success, id }) => {
        if (success) {
          localStorage.setItem("routerId", id);
          this.routeId = id;
        }
      });
  }

  search() {
    if (!this.searchTerm || this.searchTerm.length === 0) {
      this.filteredItems = this.contacts;
      return;
    }
    const results = this.contacts.filter((item) => {
      return (
        item.name.toLowerCase().indexOf(this.searchTerm.toLowerCase()) > -1
      );
    });
    this.filteredItems = results.length === 1 ? results : [];
  }

  myHtml;
  showDialer(contact) {
    console.log(contact.raw_digits);

    this.aircallPhone = new AircallPhone({
      domToLoadPhone: "#phone",
      onLogin: (settings) => {
        console.log("User is logged in");
        this.aircallPhone.isLoggedIn((response) => {
          if (response) {
            console.log("User is logged in");
            this.aircallPhone.send(
              "dial_number",
              { phone_number: contact.raw_digits },
              (success, data) => {
                console.log(success, data);
              }
            );
          } else {
            console.log("User is not logged in");
          }
        });
      },
      onLogout: () => {
        console.log("User is logged out");
      },
    });
    this.myHtml = "";
    let sizeStyle = "";
    switch (this.size) {
      case "big":
        sizeStyle = "height:666px; width:376px;";
        break;
      case "small":
        sizeStyle = "height:600px; width:376px;";
        break;
      case "auto":
        sizeStyle = "height:100%; width:100%;";
        break;
    }
    this.myHtml = this.sanitizer.bypassSecurityTrustHtml(
      `<iframe allow="microphone; autoplay; clipboard-read; clipboard-write; hid" src="${this.getUrlToLoad()}" style="${sizeStyle}"></iframe>`
    );
  }

  getUrlToLoad(): string {
    return `${this.phoneUrl}${this.path ? "/" + this.path : ""}`;
  }

  getUsers() {
    this.aircalService.getUsers().subscribe(({ users, meta }) => {
      users.forEach((element) => {
        this.links.push(element.name);
      });
    });
  }

  filterValue;
  filterCalls(value) {
    let filteredGuards = [...this.guards]; // make a copy of the original array
    this.filterValue = value;
    this.getCalls(this.range.value.start, this.range.value.end);

    this.guards = filteredGuards; // update the original array with the filtered array
  }

  // getStatics(value) {
  //   this.aircalService.statics().subscribe(({ calls, meta }) => {
  //     calls.forEach(element => {
  //       if (element.started_at) {
  //         element.started_at_new = moment.utc(element.started_at * 1000).tz('Australia/Melbourne').format('DD-MM-YYYY HH:mm');
  //       }
  //       if (element.answered_at) {
  //         element.answered_at_new = moment.utc(element.answered_at * 1000).tz('Australia/Melbourne').format('DD-MM-YYYY HH:mm');
  //       }
  //       if (element.ended_at) {
  //         element.ended_at_new = moment.utc(element.ended_at * 1000).tz('Australia/Melbourne').format('DD-MM-YYYY HH:mm');
  //       }
  //       const minutes = Math.floor(element.duration / 60);
  //       const seconds = element.duration % 60;
  //       const formattedTime = `${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
  //       element.call_time = formattedTime

  //     });
  //     if (value == 'Inbound') {
  //       this.guards = calls.filter(obj => obj.direction == "inbound");
  //     } else if (value == 'Outbound') {
  //       this.guards = calls.filter(obj => obj.direction == "outbound");
  //     } else if (value == 'Missed calls') {
  //       this.guards = calls.filter(obj => !obj.answered_at);
  //     }
  //     else {
  //       this.guards = calls
  //     }

  //   }
  //   )
  // }

  // order
  // orderBy(value){
  //   this.order = value
  //   this.getCalls(this.range.value.start, this.range.value.end, value)

  // }
  getCalls(from?, to?, order?) {
    this.aircalService.statics(from, to, order).subscribe(({ calls, meta }) => {
      calls.forEach((element) => {
        if (element.started_at) {
          element.started_at_new = moment
            .utc(element.started_at * 1000)
            .tz("Australia/Melbourne")
            .format("DD-MM-YYYY HH:mm");
        }
        if (element.answered_at) {
          element.answered_at_new = moment
            .utc(element.answered_at * 1000)
            .tz("Australia/Melbourne")
            .format("DD-MM-YYYY HH:mm");
        }
        if (element.ended_at) {
          element.ended_at_new = moment
            .utc(element.ended_at * 1000)
            .tz("Australia/Melbourne")
            .format("DD-MM-YYYY HH:mm");
        }
        const minutes = Math.floor(element.duration / 60);
        const seconds = element.duration % 60;
        const formattedTime = `${minutes < 10 ? "0" : ""}${minutes}:${
          seconds < 10 ? "0" : ""
        }${seconds}`;
        element.call_time = formattedTime;
      });
      if (this.filterValue == "Inbound") {
        this.guards = calls.filter((obj) => obj.direction == "inbound");
      } else if (this.filterValue == "Outbound") {
        this.guards = calls.filter((obj) => obj.direction == "outbound");
      } else if (this.filterValue == "Missed calls") {
        this.guards = calls.filter((obj) => !obj.answered_at);
      } else {
        this.guards = calls;
      }
      console.log(this.guards);

      // this.records = calls;
    });
  }

  selectedTabIndex: number;
  onTabSelectionChanged(event: MatTabChangeEvent) {
    console.log(this.links);
    // this.activeLink = link;
    // console.log('Selected link:', link);
    // this.selectedTabIndex = event.index;
    const user = this.links[2];
    // this.getCalls(this.range.value.start, this.range.value.end, this.selectedCall, user)
  }
}

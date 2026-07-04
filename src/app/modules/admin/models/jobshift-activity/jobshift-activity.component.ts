import { Component, ElementRef, Input, OnInit, ViewChild,QueryList, ViewChildren, ChangeDetectorRef  } from "@angular/core";
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";
import { faRightFromBracket } from "@fortawesome/free-solid-svg-icons";
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { GlobalVariable } from 'app/shared/global';
import { ToastServiceService } from "app/services/toast-service.service";
import { Subject } from "rxjs";
import { ReportsService } from "app/services/reports.service";
import { NgxSpinnerService } from 'ngx-spinner';
import * as html2pdf from 'html2pdf.js';
import moment from "moment";
import { FormBuilder, FormControl, FormGroup, Validators } from "@angular/forms";
declare var google;
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { MatAccordion } from "@angular/material/expansion";

@Component({
  selector: "app-jobshift-activity",
  templateUrl: "./jobshift-activity.component.html",
  styleUrls: ["./jobshift-activity.component.scss"],
})

export class JobshiftActivityComponent implements OnInit {
  // @ViewChild("map3") mapElement3: ElementRef;
  @ViewChild("content") content: ElementRef;
  @ViewChildren('map') mapElements: QueryList<ElementRef>;
  @ViewChildren('scanMap') scanMap: QueryList<ElementRef>;
  maps: any[] = [];
  scan_map:any[]=[];
  @Input() fromRoster;
  @Input() jobStatus: string;
  break: any = []
  selectedCardIndex: number = 0;
  operationNotes: string = '';
  submittedNotes: any;
  operationNoteId: any;
  selectedCardIndex$: Subject<number> = new Subject<number>();
  selectedTable7: { img: string };
  portal_setting
  footPatrol
  rating: number;
  description:string;
  map:any;
  isEditing: boolean = false

  constructor(public modalService: NgbActiveModal, private jobRoster: JobRoster1Service, private globals: GlobalVariable,
    private toast: ToastServiceService, private resportService: ReportsService,private sanitizer: DomSanitizer,private cdr: ChangeDetectorRef,
    private spinnerService: NgxSpinnerService, private fb: FormBuilder) {
    this.rating = 0;

    // this.initMap();
  }
  detail: boolean = true;
  /**font awesome icon */
  faPencil = faRightFromBracket;
  tasks = [];
  stars: number[] = [1, 2, 3, 4, 5];
  currentRating: number = 0;

  sideCards = [
    {
      text: "Sign In/Out Details",
      logo: "../../../../../assets/icons/jobshiftActivity/Group 465.png",
      background: "#BFFFFB",
      color: " #027069",
    },
    // {
    //   text: "Break Details",
    //   logo: "../../../../../assets/icons/jobshiftActivity/Group.png",
    //   background: "#FFFCCA",
    //   color: " #9C8928",
    // },
    // {
    //   text: "Green Call",
    //   logo: "../../../../../assets/icons/jobshiftActivity/Vector-2.png",
    //   background: "#BBF5B1",
    //   color: " #105652",
    // },
    // {
    //   text: "Welfare Call",
    //   logo: "../../../../../assets/icons/jobshiftActivity/Vector.png",
    //   background: "#BFD1F3",
    //   color: " #4B689F",
    // },
    // {
    //   text: "Tracker",
    //   logo: "../../../../../assets/icons/jobshiftActivity/group-646.png",
    //   background: "#D6D6D6",
    //   color: " #767676",
    // },
    // {
    //   text: "Incident Report",
    //   logo: "../../../../../assets/icons/jobshiftActivity/Vector-1.png",
    //   background: "#F2CCC0",
    //   color: " #C20606",
    // },
    // {
    //   text: "Operation Notes",
    //   logo: "../../../../../assets/icons/jobshiftActivity/Group 463.png",
    //   background: "#FFFCCA",
    //   color: " #9C8928",
    // },
    {
      text: "Shift Activity",
      logo: "../../../../../assets/icons/jobshiftActivity/Group 466.png",
      background: "#BBF5B1",
      color: " #105652",
    },
    // {
    //   text: "Shift Task",
    //   logo: "../../../../../assets/icons/createSite/Group.png",
    //   background: "#BFFFFB",
    //   color: " #027069",
    // },
    // {
    //   text: "Daily Shift Report",
    //   logo: "../../../../../assets/icons/jobshiftActivity/Vector-1.png",
    //   background: "#F2CCC0",
    //   color: "#C20606",
    // },
    // {
    //   text: "Patrolling Report ",
    //   logo: "../../../../../assets/icons/jobshiftActivity/Group 463.png",
    //   background: "#D6D6D6",
    //   color: "#767676",
    // },
    
    // {
    //   text: "Patrolling Report ",
    //   logo: "../../../../../assets/icons/jobshiftActivity/Group 463.png",
    //   background: "#D6D6D6",
    //   color: "#767676",
    // },
    // {
    //   text: "Rating",
    //   logo: "../../../../../assets/icons/star.png",
    //   background: "#FFFCCA",
    //   color: "#9C8928",
    // },
  ];
  borderColors: string[] = [
    "3px solid #027069",
    "3px solid #9C8928",
    "3px solid #105652",
    "3px solid #4B689F",
    // "3px solid #767676",
    "3px solid #C20606",
    "3px solid #9C8928",
    "3px solid #105652",
    "3px solid #027069",
    "3px solid #C20606",
    "3px solid #767676",
    "3px solid #9C8928",

  ];
  breakDetails: any = [];

  notes;
  AddInurydetail: string = '';
  filteredSideCards = [];
  datas: any=[]
  patrolling_data:any=[]

  ngOnInit(): void {
    console.log('Job Status:', this.jobStatus);
    this.filterSideCards();
  }

  incidentReport: any = [];

  // initMap() {
  //   console.log('mapElement:', this.mapElement);

  //   const locationString = this.datas?.signin_location;
  //   if (locationString) {
  //     const [lat, lng] = locationString.split(",");
  //     const coords = new google.maps.LatLng(lat, lng);

  //     const mapOptions = {
  //       center: coords,
  //       zoom: 15,
  //       mapTypeId: google.maps.MapTypeId.ROADMAP,
  //     };
  //     this.map = new google.maps.Map(this.mapElement.nativeElement, mapOptions);

  //     const marker = new google.maps.Marker({
  //       map: this.map,
  //       position: coords,
  //       draggable: true,
  //       title: "Staff Sign-In Location",
  //     });

  //   } else {
  //     console.log("Invalid location string");
  //   }
  // }
  initMaps() {
    this.patrolling_data.forEach((patrol, index) => {
      const mapElementId = `map_${index}`;
      const mapElement = document.getElementById(mapElementId);
      if (mapElement && patrol.coordinates) {
        const [lat, lng] = patrol.coordinates.split(",");
        const coords = new google.maps.LatLng(lat, lng);

        const mapOptions = {
          center: coords,
          zoom: 15,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
        };

        const map = new google.maps.Map(mapElement, mapOptions);
        const marker = new google.maps.Marker({
          map: map,
          position: coords,
          draggable: true,
          title: `Patrol ID: ${patrol.id}`,
        });

        this.maps.push(map);
      } else {
        console.log("Invalid location string or map element not found");
      }
    });
  }

  
  selectCard(index: number) {
    this.selectedCardIndex = index;
    if (this.selectedCardIndex === 9) {
      let data = {
        guard_id: this.globals.guard_id,
        roster_id: this.globals.roster_id

      };
      this.jobRoster.patrolling_report(data).subscribe(({ success, data }) => {
        if (success) {
          this.patrolling_data = data;
          this.cdr.detectChanges(); // Detect changes to update the view
          // this.initMaps();
          // this.initScanMaps();
        }
      });
    }
    if (this.selectedCardIndex == 4) {
      this.isEditing = false
      let data = {
        guard_id: this.globals.guard_id,
        roster_id: this.globals.roster_id
      }
      this.jobRoster.incidentReport(data).subscribe((res) => {
        if (res.success) {
          this.incidentReport = res.data;
          this.customerName = res.customer;
          this.location = res.loaction;
          this.start = res.shift_start.split(" ")[1];
          this.end = res.shift_end.split(" ")[1]
          this.staffName = res.staff;
        }
      })
    }
    if (this.selectedCardIndex == 6) {
      this.getShiftActivity()
    }

    if (this.selectedCardIndex == 5) {
      this.getNotes()
    }
    if (this.selectedCardIndex == 1) {
      this.getBreakDetail()
    }

    if (this.selectedCardIndex == 7) {
      this.getShiftTask()
    }

    if (this.selectedCardIndex == 8) {
      this.isEditing = false
      this.getFootPatrol()
    }

  }
  incident
  isShown(inci) {
    this.detail = !this.detail;
    this.incident = inci;
    this.AddInurydetail = inci.injury_detail
  }

  close() {
    this.modalService.dismiss();
  }

  submitOperationNotes() {
    let data = {
      guard_id: this.globals.guard_id,
      roster_id: this.globals.roster_id,
      operation_notes: this.operationNotes
    }
    this.jobRoster.operationNotes(data).subscribe(res => {
      let status = "Operation Notes";
      if (res.success) {
        this.toast.toastNotification(res.message, status);
        this.getNotes();
      }
      else {
        this.toast.toastNotification1(res.message, status);
      }
    }, error => {
      this.toast.toastNotification1(this.globals.apiError, 'Error')
    })
    this.operationNotes = '';
  }

  makePdf(id) {
    let value = {
      incident_id: id
    }
    this.resportService.dowloadIncidentReport(value).subscribe(({ success, path, message }) => {
      if (success) {
        this.downloadPdf(path, 'incident_report.pdf')
        this.toast.toastNotification(message, 'Incident Report!')
      } else {
        this.toast.toastNotification1('Something went wrong', 'Incident Report!')
      }
    });
  }

  getBreakDetail() {
    let data = {
      guard_id: this.globals.guard_id,
      roster_id: this.globals.roster_id,
    }
    this.jobRoster.getBreakDetail(data).subscribe(({ success, data }) => {
      if (success) {
        this.break = data
      }
    })
  }

  getShiftActivity() {
    let data = {
      guard_id: this.globals.guard_id,
      roster_id: this.globals.roster_id,
    }
    this.jobRoster.getShiftActivity(data).subscribe(({ success, data, customer, loaction, shift_end, shift_start, staff }) => {
      if (success) {
        this.datas = data
        this.customerName = customer;
        this.location = loaction;
        this.start = shift_start.split(" ")[1];
        this.end = shift_end.split(" ")[1]
        this.staffName = staff;
      }
    })
  }

  getNotes() {
    let data = {
      guard_id: this.globals.guard_id,
      roster_id: this.globals.roster_id
    }
    this.jobRoster.getNotes(data).subscribe(({ success, data }) => {
      if (success) {
        this.notes = data.operation_notes;
        // this.operationNotes = this.notes;
        this.submittedNotes = this.notes
        this.operationNoteId = data.id
      }
    })
  }

  downloadPdf(url: string, fileName: string) {
    const link = document.createElement('a');
    link.setAttribute('target', '_blank');
    link.setAttribute('href', url);
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    link.remove();
  }

  customerName
  location
  start
  end
  staffName
  getShiftTask() {
    let data = {
      roster_id: this.globals.roster_id,
    }
    this.jobRoster.getshiftTask(data).subscribe(({ success, data }) => {
      if (success) {
        this.tasks = data;
        console.log("shift tasks", this.tasks);
      }
    })
  }

  formatTime(dateString: string): string {
    if (dateString) {
      return moment(dateString, 'DD-MM-YYYY HH:mm').format('HH:mm');
    }
    else {
      return null;
    }
  }

  handleImageError(event: Event) {
    const imgElement = event.target as HTMLImageElement;
    imgElement.src = 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/No_image_available.svg/2048px-No_image_available.svg.png'; // Replace with the path to your default image
  }

  getImageUrl(imageFileName: string): string {
    const baseUrl = 'https://apis.thescouts.com.au/shiftTask/';
    return baseUrl + imageFileName;
  }

  // shift activity and shift task pdf

  savePDF(): void {
    let value = {
      guard_id: this.globals.guard_id,
      roster_id: this.globals.roster_id
    }
    this.jobRoster.pdfshiftActivity(value).subscribe(({ success, path, message }) => {
      if (success) {
        this.downloadPdf(path, 'shift_activity_report.pdf')
        this.toast.toastNotification(message, 'Shift Activity Report!')
      } else {
        this.toast.toastNotification1('Something went wrong', 'Shift Activity Report!')
      }
    });
  }


  // shift task pdf

  shiftTaskPdf() {
    let value = {
      roster_id: this.globals.roster_id,
    }
    this.jobRoster.pdfshiftTask(value).subscribe(({ success, path, message }) => {
      if (success) {
        this.downloadPdf(path, 'shift_tasks_report.pdf')
        this.toast.toastNotification(message, 'Shift task Report!')
      } else {
        this.toast.toastNotification1('Something went wrong', 'Shift task Report!')
      }
    });
  }

  getFootPatrol() {
    let data = {
      guard_id: this.globals.guard_id,
      roster_id: this.globals.roster_id
    }
    this.jobRoster.footPatrolReport(data).subscribe(({ success, data, customer, loaction, shift_start, shift_end, staff }) => {
      if (success) {
        this.footPatrol = data;
        this.customerName = customer;
        this.location = loaction;
        this.start = shift_start.split(" ")[1];
        this.end = shift_end.split(" ")[1]
        this.staffName = staff;
        console.log("Foot Patrol Report", this.footPatrol);
      }
    })
  }

  generateFootPatrolPdf(Id) {
    let value = {
      id: Id
    }
    this.resportService.dowloadFootPatrolReport(value).subscribe(({ success, path, message }) => {
      if (success) {
        this.downloadPdf(path, 'Daily_Shift_Report.pdf')
        this.toast.toastNotification(message, 'Daily Shift Report!')
      } else {
        this.toast.toastNotification1(message, 'Daily Shift Report!')
      }
    });
  }

  saveTask(taskId: number, field: any, event: any, type) {
    const editedContent = event.target.textContent;
    const data: any = {
      id: taskId,
      actual_start_time: field.actual_start_time,
      actual_end_time: field.actual_end_time,
      note: field.note,
    };

    if (type === 'start') {
      data.actual_start_time = field.actual_start_time.split(' ')[0] + ' ' + editedContent;
    } else if (type === 'end') {
      data.actual_end_time = field.actual_end_time.split(' ')[0] + ' ' + editedContent;
    } else {
      data.note = editedContent;
    }

    this.jobRoster.saveEditTask(data).subscribe(
      ({ message, success }) => {
        this.isEditing = false
        this.toast.toastNotification(message, success ? 'Task Operation!' : 'Task Operation!');
      },
      (error) => {
        this.toast.toastNotification1('Something went wrong, Please contact with the support team.', 'Request Incomplete!');
      }
    );
  }

  savePetroleDetail(taskId: number) {
    const patroldataToUpdate = this.footPatrol.find(p => p.id === taskId);
    console.log(patroldataToUpdate);
    
    const data: any = {
      id: taskId,
      patrolling_detail: patroldataToUpdate.patrolling_details,
    };
    this.jobRoster.savePetroleDetail(data).subscribe(
      ({ message, success }) => {
        this.isEditing = false
        this.toast.toastNotification(message, success ? 'Foot Patrol Report Operation!' : 'Foot Patrol Report Operation!');
      },
      (error) => {
        this.toast.toastNotification1(this.globals.apiError, 'Request Incomplete!');
      }
    );
  }

  submitDetail(id) {
    let data = {
      id: id,
      injury_detail: this.AddInurydetail
    }
    this.resportService.editInjurydetail(data).subscribe(({ success, message }) => {
      if (success) {
        this.toast.toastNotification(message, 'Incident Report!')
      }
      else {
        this.toast.toastNotification1(message, 'Incident Report!')
      }
      this.isEditing = true
    }, error => {
      this.toast.toastNotification1(this.globals.apiError, 'Incident Report!')
    })
  }

  rate() {
    

    let data = {
      guard_id: this.globals.guard_id,
      roster_id: this.globals.roster_id,
      rating:this.rating,
      rating_desc:this.description
    }
    this.jobRoster.giveRating(data).subscribe((res)=>{
      console.log(res);
      
    })
  }

  filterSideCards(): void {
    this.filteredSideCards = this.sideCards.filter(card => 
      card.text !== 'Rating' || (card.text === 'Rating' && this.jobStatus === 'completed')
    );
  }
  donwload_patrolling_report(){
    let data = {
      roster_id: this.globals.roster_id
    }
    this.jobRoster.generatePatrollingPdf(data).subscribe(({ success, pdf_url, message }) => {
      if (success) {
        this.downloadPdf(pdf_url, 'Patrolling_report.pdf')
        // this.toast.toastNotification(message, '')
      } else {
        this.toast.toastNotification1(message, 'Patrolling Report!')
      }
    });
  }


  initScanMaps() {
    this.patrolling_data?.forEach((report, reportIndex) => {
      report.scanners?.forEach((scanner, scannerIndex) => {
        const mapElementId = `smap_${reportIndex}_${scannerIndex}`;
        const mapElement = document.getElementById(mapElementId);
        
        if (mapElement && scanner.coordinates) {
          const [lat, lng] = scanner.coordinates.split(",");
          const coords = new google.maps.LatLng(lat, lng);
          const geocoder = new google.maps.Geocoder();
          geocoder.geocode({ location: coords }, (results, status) => {
            if (status === google.maps.GeocoderStatus.OK) {
              if (results[0]) {
                mapElement.innerText = results[0].formatted_address;
              } else {
                mapElement.innerText = 'No results found';
              }
            } else {
              console.error('Geocoder failed due to:', status);
              mapElement.innerText = 'Location not available';
            }
          });
        } else {
          console.log("Invalid location string or map element not found");
        }
      });
    });
  }

  deleteNote() {
    let data = {
      id: this.operationNoteId
    }

    this.jobRoster.operationDelete(data).subscribe(res => {
      let status = "Operation Notes";
      if (res.success) {
        this.toast.toastNotification(res.msg, status);
        this.getNotes();
      }
      else {
        this.toast.toastNotification1(res.msg, status);
      }
    }, error => {
      this.toast.toastNotification1(this.globals.apiError, 'Error')
    })

  }
  
 
 
}

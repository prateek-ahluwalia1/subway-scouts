import { ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { ActivatedRoute, Router } from '@angular/router';
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AnnouncementInductionService } from 'app/services/announcement-induction.service';
import { CustomerService } from 'app/services/customer.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { trigger, transition, style, animate, query, stagger } from '@angular/animations';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { HttpHeaders } from '@angular/common/http';
import { PermissionsService } from 'app/services/permissions.service';
import { CommonServiceService } from '../users-reports/common-service.service';

export class Customers {
  id: number;
  name: string;
}

@Component({
  selector: 'app-announcements',
  templateUrl: './announcements.component.html',
  styleUrls: ['./announcements.component.scss'],
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('500ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('500ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ]
})

export class AnnouncementsComponent implements OnInit, OnDestroy {

  public addAnnouncement: FormGroup;
  public communiType: string;
  public allAnnouncements: any[] = [];
  public adminPermissions: any;
  public isShare = false;
  public selectedState = 'Victoria';
  public customers: Customers[] = [];
  public fileUploaded = false;
  public uploadFileUrl: string;
  public file_name: string;
  public annId: string;
  public historyData: any = {};
  public historyDataLoaded = false;
  filteredHistoryData: any[] = [];
  searchQuery: string = '';
  private routerId: string;
  guards: any;
  customersIds
  sitesIds: any;
  sitesList: any;
  guardsIds: any;
  noHistoryMsg: string = '';

  constructor(
    private modalService: NgbModal,
    private fb: FormBuilder,
    private global: GlobalVariable,
    private announcementService: AnnouncementInductionService,
    public toast: ToastServiceService,
    private route: ActivatedRoute,
    private sanitizer: DomSanitizer,
    private customerService: CustomerService,
    private userService: StaffService,
    private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService,
    private cdr: ChangeDetectorRef,
    public router: Router,
    private _commomService: CommonServiceService,
  ) { }

  ngOnInit(): void {
    this.routerId = localStorage.getItem('routerId');
    if (!this.routerId) {
      this.recordActivity('Enter in Announcement Page');
    }
    this.communiType = this.route.snapshot.paramMap.get('communiType') || '';
    this.initializeForm();
    this.loadPermissions();
    this.getAllAnnouncements();
  }

  ngOnDestroy() {
    this.recordActivity('Exit Announcement Page', true);
  }

  private initializeForm(): void {
    this.addAnnouncement = this.fb.group({
      title: ['', Validators.required],
      announcement: [],
      induction: [],
      file: []
    });
  }

  private loadPermissions(): void {
    const permissions = this.permissionService.getPermissionsByTitle('Communications');
    if (this.communiType === 'induction') {
      this.adminPermissions = permissions?.childPage?.find(item => item.title === 'Induction');
    } else {
      this.adminPermissions = permissions?.childPage?.find(item => item.title === 'Announcement');
    }
    this.cdr.detectChanges();
  }

  private getAllAnnouncements(): void {
    const serviceMethod = this.communiType === 'induction' ? this.announcementService.getAllInduction() : this.announcementService.getAllAnnouncement(this.communiType);
    serviceMethod.subscribe(({ success, data }) => {
      if (success) {
        this.allAnnouncements = data?.map(ann => ({ ...ann, isExpanded: false })) || [];
      }
    });
  }

  private recordActivity(activity: string, isExit = false): void {
    this.trackAdmin.storeActivity('Announcement', activity, this.routerId).subscribe(({ success, id }) => {
      if (success) {
        if (isExit) {
          localStorage.removeItem('routerId');
        } else {
          this.routerId = id;
          localStorage.setItem('routerId', id);
        }
      }
    });
  }

  get isInduction(): boolean {
    return this.communiType === 'induction';
  }

  truncate(text: string, maxLength: number): string {
    return text.length > maxLength ? `${text.substr(0, maxLength - 3)}...` : text;
  }

  bypassSecurityTrustHtml(html: string, maxLength: number): SafeHtml {
    const truncatedHtml = this.truncate(html, maxLength);
    return this.sanitizer.bypassSecurityTrustHtml(truncatedHtml);
  }

  toggleExpanded(announcement: any): void {
    announcement.isExpanded = !announcement.isExpanded;
  }

  newAnnouncement(formTemplate: any): void {
    this.annId = '';
    this.isShare = false;
    if (this.isInduction) {
      this.router.navigate(['/induction', '']);
    } else {
      this.modalService.open(formTemplate, { size: 'lg' });
    }
  }

  save(): void {
    const formData = { ...this.addAnnouncement.value, admin_id: this.global.admin.admin_id, type: this.communiType };
    if (this.isInduction) {
      formData.induction_id = this.annId;
    } else {
      formData.announce_id = this.annId;
    }
    this.announcementService.addAnnouncement(formData).subscribe(({ success, status }) => {
      if (success) {
        const operationType = this.isInduction ? 'Induction Operation!' : 'Announcement Operation!';
        this.toast.toastNotification(status, operationType);
        this.closeModal();
        this.getAllAnnouncements();
      }
    });
  }

  editAnnouncement(id: string, announcement: any, formTemplate: any): void {
    this.isShare = false;
    this.annId = id;
    if (this.isInduction) {
      this.router.navigate(['/induction', id]);
    } else {
      this.modalService.open(formTemplate, { size: 'lg' });
      this.addAnnouncement.patchValue({
        title: announcement.title,
        announcement: announcement.html_body
      });
      if (announcement.file) {
        this.fileUploaded = true;
        this.file_name = 'View Uploaded File';
        this.uploadFileUrl = announcement.file;
      }
    }
  }

  deleteAnnouncement(id: string): void {
    const confirmBox = new ConfirmBoxInitializer();
    confirmBox.setTitle('Communication Operation!');
    confirmBox.setMessage('Are you sure to delete this?');
    confirmBox.setConfig({
      layoutType: DialogLayoutDisplay.DANGER,
      animationIn: AppearanceAnimation.ZOOM_IN,
      animationOut: DisappearanceAnimation.ZOOM_OUT,
      allowHtmlMessage: true,
      buttonPosition: 'right',
    });
    confirmBox.setButtonLabels('Confirm', 'Decline');
    confirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.success) {
        const serviceMethod = this.isInduction ? this.announcementService.delInduction(id) : this.announcementService.dellAnnouncement(id, this.communiType);
        serviceMethod.subscribe(({ success, message }) => {
          let operationType
          if (success) {
            operationType = this.isInduction ? 'Induction Operation!' : 'Announcement Operation!';
            this.toast.toastNotification(message, operationType);
            this.getAllAnnouncements();
          } else {
            this.toast.toastNotification1(message, operationType);
          }
        }, () => {
          this.toast.toastNotification1('Something went wrong. Please contact support.', 'Operation Error');
        });
      }
    });
  }

  shareTemp(id: string, formTemplate: any): void {
    this.customersIds = [];
    this.isShare = true;
    this.annId = id;
    this.modalService.open(formTemplate, { size: 'lg' });
    this.customerService.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data;
      }
    });
  }

  closeModal(): void {
    this.addAnnouncement.reset();
    this.modalService.dismissAll();
  }

  uploadFiles(event: any): void {
    const file = event.target.files[0];
    const formData = new FormData();
    formData.append('file', file, file.name);
    formData.append('folder', 'Announcement');
    const headers = new HttpHeaders({
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    });
    this.customerService.uploadImgPdf(formData, { headers }).subscribe(response => {
      if (response.success) {
        this.addAnnouncement.patchValue({ file: response.url });
      } else {
        this.toast.toastNotification1('Something went wrong. Please check your file size or connection.', 'File Upload!');
      }
    }, (error) => {
      console.error(error);
    });
  }

  viewFile(): void {
    window.open(this.uploadFileUrl, '_blank');
  }

  resetFileInput(): void {
    this.fileUploaded = false;
  }

  onStateSelect(selectedState: string): void {
    this.selectedState = selectedState;
    this.getGuards();
  }

  receiveDataFromChild(data: any): void {
    this.customersIds = data.value.map(item => item.id);
    if (this.customersIds.length) {
      this.userService.getCusSite(this.customersIds).subscribe(({ success, data }) => {
        if (success) {
          this.sitesList = data;
          this.getGuards();
        }
      });
    }
  }

  receiveDataFromChildSite(data: any): void {
    this.sitesIds = data.map(item => item.id);
    this.getGuards();
  }

  receiveDataFromChildGuards(data: any): void {
    this.guardsIds = data.value.map(item => item.id);
  }

  getGuards(): void {
    const params = {
      customer_ids: this.customersIds,
      site_ids: this.sitesIds,
      state: this.selectedState
    };
    this.announcementService.getGuardsFilter(params).subscribe(({ success, data }) => {
      if (success) {
        this.guards = data.map(element => ({
          ...element,
          name: `${element.first_name} ${element.middle_name || ''} ${element.last_name || ''}`.trim()
        }));
      }
    });
  }

  inductionId;
  showHistory(id: string, formHistory: any): void {
    this.inductionId = id;
    this.historyData = [];
    this.filteredHistoryData = [];
    this.historyDataLoaded = false;
    this.modalService.open(formHistory, { size: 'lg' });
    this.announcementService.getHistory({ id, type: this.communiType }).subscribe(({ success, data, msg }) => {
      if (success) {
        this.historyData = data;
        this.filteredHistoryData = [...this.historyData];
      }
      else {
        this.historyData = [];
        this.filteredHistoryData = [];
        this.noHistoryMsg = msg;
      }
      this.historyDataLoaded = true;
    });
  }

  send(): void {
    if (this.guardsIds && this.guardsIds.length > 0) {
      const data = this.isInduction ? {
        guards: this.guardsIds,
        questionnair_id: this.annId
      } : {
        type: this.communiType,
        guardIds: this.guardsIds,
        id: this.annId,
        customer_ids: this.customersIds,
        site_ids: this.sitesIds,
        state: this.selectedState
      };

      const serviceMethod = this.isInduction ? this.announcementService.shareQuestionnaire : this.announcementService.shareTemplates;
      serviceMethod.call(this.announcementService, data).subscribe(({ success, message }) => {
        if (success) {
          this.toast.toastNotification(message, `${this.isInduction ? 'Induction' : 'Announcement'} Operation`);
          this.closeModal();
        } else {
          this.toast.toastNotification1(message, 'Operation Error');
        }
      }, () => {
        this.toast.toastNotification1('Something went wrong. Please contact support.', 'Operation Error');
      });
    } else {
      this.toast.toastNotification1('Please select at least one staff.', 'Selection Error');
    }
  }

  downloadCertificate(pdfUrl: string) {
    if (pdfUrl) {
        window.open(pdfUrl, '_blank');
    } else {
        console.error("No PDF URL available");
    }
  }

  filterHistoryData() {
    if (!this.searchQuery) {
      this.filteredHistoryData = [...this.historyData]; // Reset to original data if search is empty
    } else {
      this.filteredHistoryData = this.historyData.filter(item =>
        item.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    }
  }

  exportData(){
      let data = {
        id: this.inductionId,
        type: 'excel'
      }
      this.announcementService.downloadInductionExcel(data).subscribe(({ success, path }) => {
        if (success) {
          this._commomService.downloadExcelFile(path, `Induction.xlxs`);
          this.trackAdmin.storeActivity('Induction', `Download induction file`, localStorage.getItem('routerId')).subscribe();
        }
      });
    }
}

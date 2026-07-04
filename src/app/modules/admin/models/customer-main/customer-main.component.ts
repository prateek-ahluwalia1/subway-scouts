import { Component, Input, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { ModalDismissReasons, NgbActiveModal, NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { CustomerService } from 'app/services/customer.service';
import { CreateCustomerComponent } from '../../component/create-customer/create-customer.component';
import { ToastServiceService } from 'app/services/toast-service.service';
import { SiteService } from 'app/services/site.service';
import { DateAdapter } from '@angular/material/core';
import { ContractorService } from 'app/services/contractor.service';
import { HttpHeaders } from '@angular/common/http';
import { ConfirmBoxInitializer, DialogLayoutDisplay, AppearanceAnimation, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { PermissionsService } from 'app/services/permissions.service';
import { NgxSpinnerService } from 'ngx-spinner';


@Component({
  selector: 'app-customer-main',
  templateUrl: './customer-main.component.html',
  styleUrls: ['./customer-main.component.scss']
})
export class CustomerMainComponent implements OnInit {

  selectedtypeId: any = 1;
  selectedtypeName: any = "Personal";
  showrates: boolean = false;
  documentsList: any = [];
  closeAdminCreateModal: NgbModalRef;
  action: any;
  modelData: string;
  uploadedImage: boolean = false;
  end_time
  createDetailModel: NgbModalRef;
  createPersonalModal: NgbModalRef;
  createEmploymentModal: NgbModalRef;
  todayDate: string;
  array: any = [];
  base64String: string;
  business_file: string;
  security_file: string;
  public_health_file: string;
  labour_hire_file: string;
  tabType: string = 'Documents';
  updateCutomer: FormGroup;
  @Input() fromParent;
  @Input() type;
  name
  email
  phone
  state
  bs_img
  sec_img
  pub_img
  li_img
  tot_shifts
  tot_sites
  documents = []
  chargeRate: any = []
  uploadFile: any;
  bussinessuploaded = false
  secuploaded = false
  lhuploaded = false
  pluploaded = false
  Permissions;

  profileTrackers: any[] = []
  states: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia'];
  constructor(private fb: FormBuilder, public model: NgbModal, private toast: ToastServiceService, public dateAdapter: DateAdapter<Date>,
    public ngbActiveModal: NgbActiveModal, private cutomerService: CustomerService, private siteService: SiteService, private contr: ContractorService,
    private permissionService: PermissionsService, private spinner: NgxSpinnerService) {
    this.dateAdapter.setLocale('en-AU');
  }

  ngOnInit(): void {
    console.log(this.type);

    const per = this.permissionService.getPermissionsByTitle('Onboarding');
    if (this.type == 'contractor') {
      this.Permissions = per?.childPage?.find(item => item.title === 'Contractors');
    }
    else {
      this.Permissions = per?.childPage?.find(item => item.title === 'Customers');
      this.getCustomerPrileTracker()
    }

    if (this.fromParent) {
      if (this.fromParent && this.fromParent.data.job_level) {
        this.getChargeRate(this.fromParent.data.job_level)
      }
      this.updateCutomer = this.fb.group({
        job_level: [this.fromParent.data.job_level],
        state: [this.fromParent.data.state],
        chargerate: [this.fromParent.data.charge_rate],
        charge_rate_apply_date: [this.fromParent.data.apply_date],
        more_contacts: this.fb.array([
        ]),
        business: this.fb.group({
          document_name: new FormControl('business'),
          document: new FormControl(''),
          document_no: new FormControl(''),
          document_expire: new FormControl(''),
        }),
        security: this.fb.group({
          document_name: new FormControl('security'),
          document: new FormControl(''),
          document_no: new FormControl(''),
          document_expire: new FormControl(''),
        }),
        public_liablity: this.fb.group({
          document_name: new FormControl('public_liablity'),
          document: new FormControl(''),
          document_no: new FormControl(''),
          document_expire: new FormControl(''),
        }),
        labour_hire: this.fb.group({
          document_name: new FormControl('labour_hire'),
          document: new FormControl(''),
          document_no: new FormControl(''),
          document_expire: new FormControl(''),
        }),
      });
    }
    else {
      this.updateCutomer = this.fb.group({
        job_level: [''],
        state: [''],
        chargerate: [''],
        charge_rate_apply_date: [''],
        more_contacts: this.fb.array([
        ]),
        business: this.fb.group({
          document_name: new FormControl('business'),
          document: new FormControl(''),
          document_no: new FormControl(''),
          document_expire: new FormControl(''),
        }),
        security: this.fb.group({
          document_name: new FormControl('security'),
          document: new FormControl(''),
          document_no: new FormControl(''),
          document_expire: new FormControl(''),
        }),
        public_liablity: this.fb.group({
          document_name: new FormControl('public_liablity'),
          document: new FormControl(''),
          document_no: new FormControl(''),
          document_expire: new FormControl(''),
        }),
        labour_hire: this.fb.group({
          document_name: new FormControl('labour_hire'),
          document: new FormControl(''),
          document_no: new FormControl(''),
          document_expire: new FormControl(''),
        }),
      });
    }

    if (this.fromParent.data.otherDocuments != null) {
      this.fromParent.data.otherDocuments.forEach(element => {
        if (element.type == "business") {
          if (element?.document) {
            const extension = element?.document.split(".").pop().toLowerCase();
            this.business_file = element.document;
            console.log(this.business_file);
            if (extension === "pdf") {
              this.bussinessuploaded = true
              this.businessFileName = 'Business File'
            } else {
              // this.tfn_file = this.fromParentEmployeeDetail?.tfn_file;
            }
          }
          this.updateCutomer.get(['business', 'document_no']).setValue(element.document_no)
          this.updateCutomer.get(['business', 'document_expire']).setValue(element.document_expire)
          this.updateCutomer.get(['business', 'document']).setValue(element.document)
          this.updateCutomer.get(['business', 'document_name']).setValue(element.type)
          this.business_file = element.document
        }
        if (element.type == "labour_hire") {
          if (element?.document) {
            const extension = element?.document.split(".").pop().toLowerCase();
            this.labour_hire_file = element.document;
            if (extension === "pdf") {
              this.lhuploaded = true
              this.lhFileName = 'Business File'
            } else {
              // this.tfn_file = this.fromParentEmployeeDetail?.tfn_file;
            }
          }
          this.updateCutomer.get(['labour_hire', 'document_no']).setValue(element.document_no)
          this.updateCutomer.get(['labour_hire', 'document_expire']).setValue(element.document_expire)
          this.updateCutomer.get(['labour_hire', 'document']).setValue(element.document)
          this.updateCutomer.get(['labour_hire', 'document_name']).setValue(element.type)
          this.labour_hire_file = element.document
        }
        if (element.type == "public_liablity") {
          if (element?.document) {
            const extension = element?.document.split(".").pop().toLowerCase();
            this.public_health_file = element.document;
            if (extension === "pdf") {
              this.pluploaded = true
              this.plFileName = 'Public Health File'
            } else {
              // this.tfn_file = this.fromParentEmployeeDetail?.tfn_file;
            }
          }

          this.updateCutomer.get(['public_liablity', 'document_no']).setValue(element.document_no)
          this.updateCutomer.get(['public_liablity', 'document_expire']).setValue(element.document_expire)
          this.updateCutomer.get(['public_liablity', 'document']).setValue(element.document)
          this.updateCutomer.get(['public_liablity', 'document_name']).setValue(element.type)
          this.public_health_file = element.document
        }
        if (element.type == "security") {
          if (element?.document) {
            const extension = element?.document.split(".").pop().toLowerCase();
            this.security_file = element.document;
            if (extension === "pdf") {
              this.secuploaded = true
              this.scFileName = 'Business File'
            } else {
              // this.tfn_file = this.fromParentEmployeeDetail?.tfn_file;
            }
          }
          this.updateCutomer.get(['security', 'document_no']).setValue(element.document_no)
          this.updateCutomer.get(['security', 'document_expire']).setValue(element.document_expire)
          this.updateCutomer.get(['security', 'document']).setValue(element.document)
          this.updateCutomer.get(['security', 'document_name']).setValue(element.type)
          this.security_file = element.document
        }

      });
    }
    if (this.fromParent.data.moreContacts) {
      this.fromParent.data.moreContacts.forEach(element => {
        this.more_contacts.push(this.fb.group({
          more_contact_id: [element.more_contact_id],
          more_email: [element.more_email],
          more_phone: [element.more_phone],
          more_notes: [element.more_notes],
        }));
      });
    }
    if (this.fromParent.data.name || this.fromParent.data.email || this.fromParent.data.phone || this.fromParent.data.state) {
      this.name = this.fromParent.data.name
      this.tot_shifts = this.fromParent.shift_count
      this.tot_sites = this.fromParent.sites_count
      this.email = this.fromParent.data.email
      this.phone = this.fromParent.data.phone
      this.state = this.fromParent.data.state
    }

    this.updateCutomer.get('job_level').valueChanges.subscribe(value => {
      this.siteService.getChargeRate(value).subscribe(({ success, data }) => {
        if (success) {
          this.chargeRate = data
        }
      }, error => {
        console.error(error);
      });
    });

  }

  getChargeRate(id) {
    this.siteService.getChargeRate(id).subscribe(({ success, data }) => {
      if (success) {
        this.chargeRate = data
      }
    });
  }
  get more_contacts(): FormArray {
    return this.updateCutomer.get('more_contacts') as FormArray;
  }
  dismiss(e) {
    this.ngbActiveModal.dismiss(e);
  }

  getTabType(selected: string, el: HTMLElement) {
    this.tabType = selected;
    el.scrollIntoView();
  }


  // add new Texte Area
  addTextarea() {
    this.more_contacts.push(this.fb.group({
      more_contact_id: [''],
      more_email: [''],
      more_phone: [''],
      more_notes: [''],
    }));
  }

  // remove Text Area
  removeTextArea(value, index) {
    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure you want to confirm this action?');
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.SUCCESS, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'right', // optional 
    });
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.success) {
        if (this.type == 'customer') {
          this.cutomerService.delCustomerContacts(value.more_contact_id).subscribe(({ success, message }) => {
            if (success) {
              (this.updateCutomer.get('more_contacts') as FormArray).removeAt(index);
              let status = 'customer Contacts!'
              this.toast.toastNotification1(message, status)
            }

          });
        } else {
          this.contr.delContractorContacts(value.more_contact_id).subscribe(({ success, message }) => {
            if (success) {
              (this.updateCutomer.get('more_contacts') as FormArray).removeAt(index);
              let status = 'Contractor Contacts!'
              this.toast.toastNotification1(message, status)
            }

          });
        }
      }
    });
  }



  businessFileName
  scFileName
  plFileName
  lhFileName
  folder
  // Upload Customer Documents
  uploadImage(type) {
    this.base64String = '';
    let input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*,application/pdf";
    input.onchange = (_) => {
      let files = Array.from(input.files);
      if (!files || files.length === 0) return;
      this.uploadFile = files[0];
      const myFormData = new FormData();
      const headers = new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      })
      myFormData.append('file', this.uploadFile, this.uploadFile.name);
      if (this.type == 'customer') {
        this.folder = 'customer_documents'
      }
      else {
        this.folder = 'contractor_documents'
      }
      myFormData.append('folder', this.folder)
      this.cutomerService.uploadImgPdf(myFormData, {
        headers: headers
      }).subscribe(
        response => {
          if (response.success) {
            const extension = response.url.split(".").pop().toLowerCase();
            if (type == 'business') {
              if (extension === "pdf") {
                this.bussinessuploaded = true
                this.businessFileName = 'Business Uploaded'
              } else {
                this.bs_img = response.path
                this.bussinessuploaded = false
              }
              this.business_file = response.url;
              this.bs_img = response.path;
            }
            else if (type == 'security') {
              if (extension === "pdf") {
                this.secuploaded = true
                this.scFileName = 'Security File Uploaded'
              } else {
                this.secuploaded = false
              }
              this.security_file = response.url;
              this.sec_img = response.path;
            }

            else if (type == 'Public Liablity') {
              if (extension === "pdf") {
                this.pluploaded = true
                this.plFileName = 'Public Liablity File Uploaded'
              } else {
                this.pluploaded = false
              }
              this.public_health_file = response.url;
              this.pub_img = response.path;
            }
            else {
              if (extension === "pdf") {
                this.lhuploaded = true
                this.lhFileName = 'Public Liability File Uploaded'
              } else {
                this.lhuploaded = false
              }
              this.labour_hire_file = response.url;
              this.li_img = response.path;
            }
          }
        },
        (error) => {
          console.error(error);
        }
      );

    };

    input.click();
  }

  onFormSubmit(value) {
    this.spinner.show()
    if (this.sec_img) {
      value.security.document = this.sec_img
    }
    if (this.bs_img) {
      value.business.document = this.bs_img
    }
    if (this.pub_img) {
      value.public_liablity.document = this.pub_img
    }
    if (this.li_img) {
      value.labour_hire.document = this.li_img
    }
    if (value.business.document_no || value.business.document_expire || value.business.type || value.business.document) {
      this.documents.push(value.business)
    }
    if (value.security.document_no || value.security.document_expire || value.security.type || value.security.document) {
      this.documents.push(value.security)
    }
    if (value.public_liablity.document_no || value.public_liablity.document_expire || value.public_liablity.type || value.public_liablity.document) {
      this.documents.push(value.public_liablity)
    }
    if (value.labour_hire.document_no || value.labour_hire.document_expire || value.labour_hire.type || value.labour_hire.document) {
      this.documents.push(value.labour_hire)
    }
    value.documents = this.documents
    if (this.fromParent.data) {
      value.id = this.fromParent.data.id
    }

    value.name = this.name
    value.shift_count = this.tot_shifts
    value.sites_count = this.tot_sites
    value.email = this.email
    value.phone = this.phone

    if (this.type == 'contractor') {
      this.contr.updatesContractor(value).subscribe(res => {
        if (res.success == true) {
          this.dismiss('true')
        }
        this.spinner.hide()
      }, (error) => {
        this.spinner.hide()
        console.log(error);
      });
    }
    else {
      this.cutomerService.updateCustomer(value).subscribe(res => {
        if (res.success == true) {
          this.dismiss('true')
        }
        this.spinner.hide()
      }, (error) => {
        this.spinner.hide()
        console.log(error);
      });
    }
  }

  isEdit: boolean = false;
  updateCutomers(id?) {
    this.isEdit = true;
    if (this.type == 'customer') {
      if (this.fromParent.data.id) {
        this.cutomerService.getCusPersonal(this.fromParent.data.id).subscribe(res => {
          if (res.success == true) {
            const modalRef = this.model.open(CreateCustomerComponent, {
              scrollable: true, windowClass: "updateCustomer"
              , size: 'lg'
            })
            modalRef.componentInstance.fromParent = res;
            modalRef.componentInstance.fromParentedit = this.isEdit;
            modalRef.componentInstance.updateType = 'updateCustomer';
            modalRef.result.then((result) => {
              console.log("Modal Result", `Closed with: ${result}`)
              console.log(result);
            }, (reason) => {
              if (reason == 'update') {
                this.toast.toastNotification('Customer Update Successfully', 'Customer Operation!')
                this.dismiss('update')
              }
              console.log(reason);
              console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)

            });
          }
        }, (error) => {
          console.log(error);
        });
      }
    }
    else {
      if (this.fromParent.data.id) {
        this.contr.getSpecContractor(this.fromParent.data.id).subscribe(res => {
          if (res.success == true) {
            const modalRef = this.model.open(CreateCustomerComponent, {
              scrollable: true, windowClass: "updateContractor"
              , size: 'lg'
            })
            modalRef.componentInstance.fromParent = res;
            modalRef.componentInstance.fromParentedit = this.isEdit;
            modalRef.componentInstance.updateType = 'updateContractor';
            modalRef.result.then((result) => {
              console.log("Modal Result", `Closed with: ${result}`)
              console.log(result);
            }, (reason) => {
              if (reason == 'update') {
                this.toast.toastNotification('Contractor Update Successfully', 'Contractor Operation!')
                this.dismiss('update')
              }
              console.log(reason);
              console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)

            });
          }
        }, (error) => {
          console.log(error);
        });
      }
    }
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else if (reason === 'true') {
      this.dismiss('true')
      // this.getAdmin_data();
    }
  }


  removeImg(name, i?) {
    if (name == 'business') {
      this.business_file = ''
    }
    else if (name == 'security') {
      this.security_file = ''
    }
    else if (name == 'Public Liablity') {
      this.public_health_file = ''
    }
    else if (name == 'Public Liablity') {
      this.labour_hire_file = ''
    }
    else { }
  }


  viewFiles(file) {
    window.open(file, '_blank')
  }


  getCustomerPrileTracker() {
    this.cutomerService.profileTracker(this.fromParent?.data).subscribe(({ success, data }) => {
      if (success) {
        this.profileTrackers = data
      }
    })
  }

  filteredData(data: any): any {
    if (!data) {
      return {};
    }

    const excludedProperties = ['customer_id', 'contractor_id', 'updated_at'];

    return Object.keys(data)
      .filter(key => !excludedProperties.includes(key))
      .reduce((obj, key) => {
        obj[key] = data[key];
        return obj;
      }, {});
  }
}
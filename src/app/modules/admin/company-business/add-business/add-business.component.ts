import { HttpHeaders } from '@angular/common/http';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { CustomerService } from 'app/services/customer.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { BusinessServiceService } from '../business-service.service';
import { ActivatedRoute, Router } from '@angular/router';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-add-business',
  templateUrl: './add-business.component.html',
  styleUrls: ['./add-business.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddBusinessComponent implements OnInit {

  createbusiness: FormGroup;
  submitted: boolean = false;
  logoimg: any;
  aboutfile: any;
  cards: any[] = [];
  activeCardIndex: number; // Initialize as -1, meaning no card is active initially
  business_id: number;

  setActiveCard(card) {
    this.activeCardIndex = card.id;
    this.createbusiness.get('packege').setValue(card.id)
    this.changeDetect.markForCheck()
  }
  constructor(private toast: ToastServiceService, private fb: FormBuilder, private route: ActivatedRoute,
    private cusService: CustomerService, private businessService: BusinessServiceService,
    private router: Router, private global: GlobalVariable, private changeDetect: ChangeDetectorRef) { }

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      this.business_id = params['id'];
      if (this.business_id) {
        this.editBusiness(this.business_id)
      }
    });
    this.initializeForm()
    this.getAllPackeges()
  }

  initializeForm() {
    this.createbusiness = this.fb.group({
      name: ['', [Validators.required]],
      status: ['', [Validators.required]],
      db_name: ['', [Validators.required]],
      address: ['', [Validators.required]],
      domain: ['', [Validators.required]],
      app_id: ['', [Validators.required]],
      email: ['', [Validators.required]],
      busitype: ['', [Validators.required]],
      server_key: ['', [Validators.required]],
      about_company: [''],
      logo: [''],
      about_file: [''],
      packege: [''],
    });
  }
  get f() {
    return this.createbusiness.controls;
  }

  //////upload logo profile image/////
  uploadFiles(event, type) {
    let input = document.createElement("input");
    input.type = "file";
    input.accept = "image/jpeg, image/png, image/jpg";
    input.onchange = (_) => {
      let files = Array.from(input.files);
      if (!files || files.length === 0) return;
      this.logoimg = files[0];
      const myFormData = new FormData();
      const headers = new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      })
      myFormData.append('file', this.logoimg, this.logoimg.name);
      myFormData.append('folder', 'business_setting');
      this.cusService.uploadImgPdf(myFormData, {
        headers: headers
      }).subscribe(
        response => {
          if (response.success) {
            this.toast.toastNotification('File Upload Successfully', 'File Upload!')
            this.createbusiness.get('logo').setValue(response.url);
          }
          else {
            this.toast.toastNotification1('Something went wrong please check your file size or connection', 'File Upload!')
          }
          this.changeDetect.markForCheck()
        },
        (error) => {
          this.toast.toastNotification1('Something went wrong please check your file size or connection', 'File Upload!')
          console.error(error);
        }
      );

    };
    input.click();

  }

  uploadAboutFile(event: any): void {
    event.preventDefault(); // Prevent the default behavior of the file input

    const fileInput = event.target;
    const file = fileInput.files && fileInput.files.length > 0 ? fileInput.files[0] : null;

    if (!file) {
      return;
    }

    const reader = new FileReader();

    reader.onload = (_) => {
      const myFormData = new FormData();
      const headers = new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      });

      myFormData.append('file', file, file.name);
      myFormData.append('folder', 'business_setting');

      this.cusService.uploadImgPdf(myFormData, {
        headers: headers
      }).subscribe(
        (response) => {
          if (response.success) {
            this.toast.toastNotification('File Upload Successfully', 'File Upload!');
            this.createbusiness.get('about_file').setValue(response.url);
          } else {
            this.toast.toastNotification1('Something went wrong. Please check your file size or connection', 'File Upload!');
          }
          this.changeDetect.markForCheck();
        },
        (error) => {
          this.toast.toastNotification1('Something went wrong. Please check your file size or connection', 'File Upload!');
          console.error(error);
        }
      );
    };

    reader.readAsDataURL(file); // Read the file as a data URL
  }


  onSubmit() {
    this.submitted = true;

    if (this.createbusiness.invalid) {
      return;
    }

    this.createbusiness.disable();
    const username = JSON.parse(localStorage.getItem("admin"))
    let params = {
      business_name: this.createbusiness.value.name,
      business_status: this.createbusiness.value.status,
      database_name: this.createbusiness.value.db_name,
      address: this.createbusiness.value.address,
      email: this.createbusiness.value.email,
      domain: this.createbusiness.value.domain,
      business_type: this.createbusiness.value.busitype,
      app_id: this.createbusiness.value.app_id,
      server_key: this.createbusiness.value.server_key,
      about_company: this.createbusiness.value.about_company,
      temp_logo: this.createbusiness.value.logo,
      temp_about_file: this.createbusiness.value.about_file,
      admin_id: username.admin_id,
      packege: this.createbusiness.value.packege,
      hide: 0,
    } as {
      business_name: string, business_status: string, database_name: string, address: string, email: string,
      domain: string, business_type: string, app_id: string, server_key: string,
      about_company: string, temp_logo: string, temp_about_file: string, id?: number, packege: string
    };
    if (this.business_id) {
      params.id = this.business_id;
    }

    if (!this.business_id) {
      this.businessService.saveform(params).subscribe(({ success, message }) => {
        if (success) {
          this.router.navigate(['company-business']);
          this.toast.toastNotification(message, 'Business Operation!');
        }
        else {
          this.createbusiness.enable()
          this.toast.toastNotification(message, 'Business Operation!');
        }
      }, (error => {
        this.toast.toastNotification(this.global.apiError, 'Business Operation!');
      }));
    }

    else {
      this.businessService.updateBusi(params).subscribe(({ success, message }) => {
        if (success) {
          this.router.navigate(['company-business']);
          this.toast.toastNotification(message, 'Business Operation!');
        }
        else {
          this.createbusiness.enable()
          this.toast.toastNotification(message, 'Business Operation!');
        }
      }, (error => {
        this.toast.toastNotification(this.global.apiError, 'Business Operation!');
      }));
    }

  }

  editBusiness(id) {
    this.businessService.getSinglebusi(id).subscribe(res => {
      if (res.success) {
        this.createbusiness.patchValue({
          name: res.data.business_name,
          status: res.data.business_status,
          db_name: res.data.database_name,
          address: res.data.address,
          email: res.data.email,
          domain: res.data.domain,
          busitype: res.data.business_type,
          app_id: res.data.app_id,
          server_key: res.data.server_key,
          about_company: res.data.about_company,
          logo: res.data.temp_logo,
          about_file: res.data.about_file,
          packege: res.data.packege,
        });
        this.activeCardIndex = res.data.packege
        // const businessIndex = this.BusinessList.findIndex(business => business.id === temp.id);
        // if (businessIndex !== -1) {
        //   this.BusinessList[businessIndex].temp_logo = res.data.temp_logo;
        // }
      }
    })
    // this.currentModal = this.modalService.open(content, { size: 'xl' });
  }

  getAllPackeges() {
    this.businessService.getAllPackeges().subscribe(({ success, data }) => {
      if (success) {
        this.cards = data
      }
      this.changeDetect.markForCheck()
    })
  }
}

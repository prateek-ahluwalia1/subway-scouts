import { Component, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { EmailSignatureService } from 'app/services/email-signature.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { ViewTemplateComponent } from '../models/view-template/view-template.component';
import { GlobalVariable } from 'app/shared/global';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
// import * as Editor from 'app/shared/build/ckeditor';
@Component({
  selector: 'app-email-signature',
  templateUrl: './email-signature.component.html',
  styleUrls: ['./email-signature.component.scss']
})
export class EmailSignatureComponent implements OnInit {

  searchEmail
  newdata: any;
  deletedata: any;
  onesign: any;
  signTitle: any;
  signBody: any;
  userName: any;
  id
  admin_id
  public addEmailTem;

  adminid;
  type;
  data;
  routeId
  signPermissions

  constructor(private modalService: NgbModal,
    private emailSignature: EmailSignatureService,
    private toast: ToastServiceService, private fb: FormBuilder,
    private global: GlobalVariable, private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService) {
    const per = this.permissionService.getPermissionsByTitle('Communications');
    this.signPermissions = per?.childPage?.find(item => item.title === 'Email Signature');
    console.log(this.signPermissions);
    this.getAllSignature();

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  getAllSignature() {
    let admin = JSON.parse(localStorage.getItem('admin'))
    this.emailSignature.getSignature(admin.admin_id).subscribe(res => {
      this.newdata = res.data;
    })
  }
  ngOnInit(): void {



    this.addEmailTem = this.fb.group({
      title: ['', [Validators.required]],
      body: [],

    })
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Email Signature Page', 'Exit Email Signature Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Email Signature Page', 'Enter in Email Signature Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  openLg(content) {
    this.modalService.open(content, { size: 'lg' });
  }
  openemaildesign(emailcontent) {
    this.modalService.open(emailcontent, { size: 'lg' });
  }
  closeModal() {
    this.modalService.dismissAll();
  }
  save() {
    let admin = JSON.parse(localStorage.getItem('admin'))
    this.admin_id = admin.admin_id
    this.addEmailTem.value.admin_id = this.admin_id
    if (!this.id) {
      this.emailSignature.saveSignature(this.addEmailTem.value).subscribe((result) => {
        if (result.success) {
          let status = 'Email Signature'
          this.toast.toastNotification(result.message, status)
          this.getAllSignature();
          this.closeModal();
        }
      })
    }
    else {
      this.addEmailTem.value.id = this.id
      this.emailSignature.updateSignature(this.addEmailTem.value).subscribe((result) => {
        if (result.success) {
          this.getAllSignature();
          this.closeModal()
          let status = 'Update Email Signature'
          this.toast.toastNotification(result.message, status)
        }
      });
    }
  }

  edit(content, user, id) {
    this.addEmailTem.get('title').setValue(user.title)
    this.addEmailTem.get('body').setValue(user.body)
    this.admin_id = user.admin_id
    this.id = user.id
    this.modalService.open(content, { size: 'lg' });
  }

  //View Design
  viewTemp(design) {
    const modalRef = this.modalService.open(ViewTemplateComponent, { size: 'lg' });
    modalRef.componentInstance.fromParent = design;
    modalRef.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        let status = this.getDismissReason(reason);
        console.log(status);

      }
    );
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return "by pressing ESC";
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return "by clicking on a backdrop";
    } else {
      return `${reason}`;
    }
  }

  deleteItem(id) {
    this.openConfirmBox(id)
  }

  // standard typescript method.
  openConfirmBox(id) {
    const newConfirmBox = new ConfirmBoxInitializer();

    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure you want to confirm this action?');

    // Choose layout color type
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.SUCCESS, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'right', // optional 
    });

    newConfirmBox.setButtonLabels('Confirm', 'Decline');

    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.success) {
        this.emailSignature.deleteSignature(id).subscribe((result) => {
          if (result.success) {
            let status = 'Email Signature Deleted!'
            this.toast.toastNotification(result.message, status)
            this.getAllSignature();
          }
        });
      }
    });
  }
}


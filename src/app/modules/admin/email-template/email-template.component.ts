import { Component, OnInit, ViewChild } from '@angular/core';
import { ConfirmBoxInitializer, DialogLayoutDisplay, AppearanceAnimation, DisappearanceAnimation, ButtonMaker, ButtonLayoutDisplay } from '@costlydeveloper/ngx-awesome-popup';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { EmailEditorComponent } from 'angular-email-editor';
import { EmailSignatureService } from 'app/services/email-signature.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { ViewTemplateComponent } from '../models/view-template/view-template.component';
import { GlobalVariable } from 'app/shared/global';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';

@Component({
  selector: 'app-email-template',
  templateUrl: './email-template.component.html',
  styleUrls: ['./email-template.component.scss']
})

export class EmailTemplateComponent implements OnInit {

  searchTem
  emailTempList: any
  @ViewChild(EmailEditorComponent)
  private emailEditor: EmailEditorComponent;
  submitted: boolean = false
  id;
  type;
  data;
  routeId
  tempPermissions

  constructor(private modalService: NgbModal,
    private emailService: EmailSignatureService,
    private toast: ToastServiceService,
    private global: GlobalVariable, private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService
  ) {
    const per = this.permissionService.getPermissionsByTitle('Communications');
    this.tempPermissions= per?.childPage?.find(item => item.title === 'Email Template');
    console.log(this.tempPermissions);
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }
  ngOnInit(): void {
    this.getAllTemp()
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Email Templates Page', 'Exit Email Templates Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Email Templates Page', 'Enter in Email Templates Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  @ViewChild('editor')
  // @ViewChild('content')
  modal: any;
  name: any;
  design
  isEditorShow: boolean = false
  editTemID

  openXl() {
    this.isEditorShow = !this.isEditorShow
  }
  closeModal() {
    this.modalService.dismissAll();
  }

  save() {
    if (!this.name) {
      this.toast.toastNotification1('Template name is required', 'Invalid Form!')
      return
    }
    this.submitted = true;
    if (!this.editTemID) {
      this.emailEditor.exportHtml(data => {
        this.emailService.saveEmail(this.name, data).subscribe(res => {
          if (res.success) {
            this.getAllTemp()
            let status = 'Email Template Operation!'
            this.toast.toastNotification(res.message, status)
            this.isEditorShow = false
            this.name = ''
          }
        })
      });
    }
    else {
      this.emailEditor.exportHtml(data => {
        this.emailService.updateDesign(this.name, data, this.editTemID).subscribe(res => {
          if (res.success) {
            this.getAllTemp()
            let status = 'Email Template Operation!'
            this.toast.toastNotification(res.message, status)
            this.isEditorShow = false
            this.name = ''
          }
        })
      });
    }

  }

  // called when the editor is created
  editorLoaded(event) {
    console.log('editorLoaded');
    // load the design json here
    this.emailEditor.editor.loadDesign(this.design);
  }

  // called when the editor has finished loading
  editorReady() {
    console.log('editorReady');
  }


  getAllTemp() {
    this.emailService.getAllTemp().subscribe(res => {
      if (res.success) {
        this.emailTempList = res.templates
      }
      else {
        this.emailTempList = []
      }
    })
  }


  //load design for edit
  editTemp(design) {
    this.emailService.getSingle(design.id).subscribe(res => {
      if (res.success) {
        console.log(res);
        this.design = this.emailEditor.editor.loadDesign(res.template.body.design);
        this.name = res.template.title
        this.editTemID = res.template.id
      }
    })
    this.isEditorShow = true
  }

  //delete design for edit
  delDesign(id) {
    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure to perform this action?');
    // Choose layout color type
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.DANGER, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'center', // optional 
    });
    newConfirmBox.setButtons([
      new ButtonMaker('Confirm', 'Confirm', ButtonLayoutDisplay.SUCCESS),
      new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER),
    ]);

    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.clickedButtonID == 'Confirm') {
        this.emailService.delDesign(id).subscribe(res => {
          if (res.success) {
            this.getAllTemp()
            let status = 'Email Template Operation!'
            this.toast.toastNotification1(res.message, status)
          }
        })
      }
    });

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
}

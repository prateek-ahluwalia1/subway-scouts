import { Component, OnInit } from '@angular/core';
import { FormBuildService } from '../form-builder.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { ActivatedRoute, Router } from '@angular/router';
import { FuseConfirmationService } from '@fuse/services/confirmation';
import { trigger, transition, style, animate, query, stagger } from '@angular/animations';
import { NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { ShreFormComponent } from '../shre-form/shre-form.component';

@Component({
  selector: 'app-form-list',
  templateUrl: './form-list.component.html',
  styleUrls: ['./form-list.component.scss'],
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('300ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('300ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ]
})

export class FormListComponent implements OnInit {
  FormList: any[] = [];
  currentUrl: string;
  isTemplates: boolean = false;

  constructor(
    private formService: FormBuildService,
    private toast: ToastServiceService,
    private router: Router,
    private modalService: NgbModal,
    private route: ActivatedRoute,
    private _fuseConfirmationService: FuseConfirmationService,
  ) { }

  ngOnInit(): void {
    this.route.url.subscribe(urlSegments => {
      this.currentUrl = urlSegments.join('/');
      this.isTemplates = this.currentUrl === 'templates';
      if (this.isTemplates) {
        this.FormList = [];
      } else {
        this.getAllForms();
      }
    });
  }

  getAllForms(): void {
    this.formService.getAllForms({ type: 'publish' }).subscribe(
      ({ success, data }) => {
        if (success) {
          this.FormList = data;
        }
      },
      (error) => {
        console.error('Error fetching forms:', error);
      }
    );
  }

  deleteForm(id: number): void {
    const confirmation = this._fuseConfirmationService.open({
      title: 'Delete Form',
      message: 'Are you sure you want to delete this form? This action cannot be undone!',
      actions: {
        confirm: {
          label: 'Delete'
        }
      }
    });

    confirmation.afterClosed().subscribe((result) => {
      if (result === 'confirmed') {
        this.formService.delDesign(id).subscribe(
          (res) => {
            if (res.success) {
              this.getAllForms();
              this.toast.toastNotification(res.message, 'Form Template Operation!');
            } else {
              this.toast.toastNotification1(res.message, 'Form Template Operation!');
            }
          },
          (error) => {
            console.error('Error deleting form:', error);
          }
        );
      }
    });
  }

  editForm(form: any): void {
    this.router.navigate(['/myforms/form-builder', form.id]);
  }

  submissionDetail(form: any): void {
    if (form.submitted_count_count === 0) {
      this.toast.toastNotification('There is no submission on this form', 'No Data Found!');
      return;
    }
    this.router.navigate(['/myforms/submission-detail', form.id, form.title]);
  }

  shareForm(form: any): void {
    const modalRef: NgbModalRef = this.modalService.open(ShreFormComponent, {
      size: 'lg',
      centered: true,
      backdropClass: 'modal-no-backdrop',
    });
    modalRef.componentInstance.form = form;
    modalRef.result.then(
      (result) => {
        console.log('Modal closed with result:', result);
      },
      (reason) => {
        console.log('Modal dismissed with reason:', reason);
      }
    );
  }

  trackById(index: number, item: any): number {
    return item.id;
  }
}

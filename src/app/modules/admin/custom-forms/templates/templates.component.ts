import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit, ViewEncapsulation } from '@angular/core';
import { FormBuildService } from '../form-builder.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-templates',
  templateUrl: './templates.component.html',
  styles: [
    `
        cards fuse-card {
            margin: 16px;
        }
    `
  ],
  encapsulation: ViewEncapsulation.None,
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class TemplatesComponent implements OnInit {
  templates: any[] = [];

  constructor(private formService: FormBuildService, private cdr: ChangeDetectorRef, private router: Router) { }

  ngOnInit(): void {
    this.getAllForms()
  }

  getAllForms() {
    this.formService.getAllForms({type :'template'}).subscribe(({ success, data }) => {
      if (success) {
        this.templates = data;
        this.cdr.markForCheck()
      }
    })
  }

  trackById(index: number, item: any) {
    return item.id;
  }

  editForm(form) {
    this.router.navigate(['/myforms/form-builder', form.id])
  }
}

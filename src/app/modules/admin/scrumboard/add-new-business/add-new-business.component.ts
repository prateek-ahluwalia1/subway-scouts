import { Component, ElementRef, OnInit, ViewChildren } from '@angular/core';
import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { MatChipInputEvent } from '@angular/material/chips';
import { FormBuilder, FormControlName, FormGroup, Validators } from '@angular/forms';
import { GenericValidator } from 'app/shared/generic-validator';
import { ScrumboardService } from '../scrumboard.service';
import { MatDialogRef } from '@angular/material/dialog';
import { Observable, fromEvent, merge, debounceTime } from 'rxjs';
import { ToastServiceService } from 'app/services/toast-service.service';

export interface Tag {
  tag: string;
}
@Component({
  selector: 'app-add-new-business',
  templateUrl: './add-new-business.component.html',
  styleUrls: ['./add-new-business.component.scss']
})
export class AddNewBusinessComponent implements OnInit {
  @ViewChildren(FormControlName, { read: ElementRef }) formInputElements: ElementRef[];

  fieldColspan = 3;
  projectForm: FormGroup;
  errorMessage: string;

  visible = true;
  selectable = true;
  removable = true;
  addOnBlur = true;
  tags: Tag[] = [];

  readonly separatorKeysCodes: number[] = [ENTER, COMMA];

  add(event: MatChipInputEvent): void {
    const input = event.input;
    const value = event.value;
    if ((value || '').trim()) {
      this.tags.push({ tag: value.trim() });
    }
    // Reset the input value
    if (input) {
      input.value = '';
    }
  }

  remove(tag: Tag): void {
    const index = this.tags.indexOf(tag);
    if (index >= 0) {
      this.tags.splice(index, 1);
    }
  }

  displayMessage: { [key: string]: string } = {};
  private genericValidator: GenericValidator;
  private validationMessages: { [key: string]: { [key: string]: string } | {} } = {
    title: {
      required: 'Name is required.',
      minlength: 'Name must be at least five characters.',
      maxlength: 'Name cannot exceed 100 characters.'
    },
  };
  constructor(private fb: FormBuilder, private service: ScrumboardService, public dialogRef: MatDialogRef<AddNewBusinessComponent>,
    private toast: ToastServiceService) {
    this.genericValidator = new GenericValidator(this.validationMessages);
  }

  ngOnInit(): void {

    this.projectForm = this.fb.group({
      title: ['', [Validators.required, Validators.minLength(5), Validators.maxLength(100)]],
      description: [''],
      tags: [''],
    });
  }

  saveProject(): void {
    if (this.projectForm.dirty && this.projectForm.valid) {
      const data = Object.assign({}, this.projectForm.value);
      data.tags = this.tags
      this.service.createProject(data)
        .subscribe(
          (response: any) => {
            if (response.success) {
              this.service.getBoards()
              this.dialogRef.close('save');
              this.tags = []
              this.toast.toastNotification(response.message, 'Stagging Operation')
              this.onSaveComplete();
            }
          },
          (error: any) => this.errorMessage = <any>error
        );
    } else if (!this.projectForm.dirty) {
      this.onSaveComplete();
    }
  }

  onSaveComplete(): void {
    this.projectForm.reset();
    // this.router.navigate([this.backUrl]);
  }

  ngAfterViewInit(): void {
    const controlBlurs: Observable<any>[] = this.formInputElements
      .map((formControl: ElementRef) => fromEvent(formControl.nativeElement, 'blur'));
    merge(this.projectForm.valueChanges, ...controlBlurs).pipe(
      debounceTime(500)
    ).subscribe(value => {
      this.displayMessage = this.genericValidator.processMessages(this.projectForm);
    });
  }

}

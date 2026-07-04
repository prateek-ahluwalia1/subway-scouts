import { Component, OnInit, ViewChild } from '@angular/core';
import { FormArray, FormBuilder, FormGroup, Validators,AbstractControl  } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { FormBuildService } from 'app/services/form-build.service';
import { SignaturePad } from 'angular2-signaturepad';
import { trigger, transition, style, animate } from '@angular/animations';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-form',
  templateUrl: './form.component.html',
  styleUrls: ['./form.component.scss'],
  animations: [
    trigger('zoomIn', [
      transition(':enter', [
        style({ transform: 'scale(0.5)', opacity: 0 }),
        animate('500ms ease-in', style({ transform: 'scale(1)', opacity: 1 }))
      ])
    ])
  ]
})
export class FormComponent implements OnInit {
  @ViewChild(SignaturePad) signaturePad: SignaturePad;
  dynamicForm: FormGroup;
  formSchema: any[];
  logoData:any[] =[]
  title: string;
  logo: string;
  id: string;
  signaturePadOptions: Object = { 'minWidth': 2, 'canvasWidth': 700, 'canvasHeight': 200 };
  signatureImg: string;
  background:any
  backgroundColor: string = ''; 
  backgroundImage: string | ArrayBuffer | null = null;
  backgroundOpacity: number = 1;
  textColor:string = ''
  
  constructor(
    private route: ActivatedRoute,
    private formService: FormBuildService,
    private fb: FormBuilder,
    private toast: ToastServiceService,
    private global: GlobalVariable
  ) { }

  ngOnInit(): void {
    this.route.paramMap.subscribe(params => {
      this.id = params.get('id');
      if (this.id) {
        this.formService.getSingletemp(this.id).subscribe({
          next: ({ success, data }) => {
            if (success) {
              this.title = data.title;
              this.logoData = JSON.parse(data.logo);
              this.logo = this.logoData[0].logo

              this.formSchema = JSON.parse(data.body);
              this.background  = JSON.parse(data.background)
              this.backgroundColor = this.background?.backgroundColor;
              this.backgroundImage  = this.background?.backgroundImage;
              this.backgroundOpacity = this.background?.backgroundOpacity;
              this.textColor = this.background?.textColor
              this.buildForm();

            }
          },
          error: () => console.error('Failed to fetch form data')
        });
      }
    }); 
  }

  buildForm() {
    const formFields = this.formSchema.map(field => this.createFieldGroup(field));
    console.log('fields: ', formFields);
    
    this.dynamicForm = this.fb.group({
      fields: this.fb.array(formFields)
    });
  }

  createFieldGroup(field): FormGroup {
    if (field.type === 'group') {
      const groupFields = field.fields.map(subField => this.createFieldGroup(subField));
      return this.fb.group({
        type: [field.type],
        name: [field.name],
        label: [field.label],
        fields: this.fb.array(groupFields)
      });
    } else {
      return this.fb.group({
        type: [field.type],
        name: [field.name],
        label: [field.label],
        placeholder: [field.placeholder],
        properties: [field.properties],
        value: ['', this.getValidators(field.properties)],
        options: [field.options || []]
      });
    }
  }

  getValidators(properties: any) {
    const validators = [];
    if (properties?.required) {
      validators.push(Validators.required);
    }
    return validators;
  }

  onSubmit() {
    if (this.dynamicForm.valid) {
      const formOutput = this.mapValuesToStructure(this.formSchema, this.dynamicForm.value);
      console.log(formOutput);
      let data = {
        form_id: this.id,
        form_data: formOutput,
        guard_id: ''
      };
      if (this.global.admin.admin_user_type && this.global.admin.admin_user_type === 'guard') {
        data.guard_id = this.global.admin.admin_id;
      } else {
        this.toast.toastNotification1('You have not permission to save this form', 'Permission Denied!');
        return;
      }
      this.formService.saveJsonForm(data).subscribe(({ success, message }) => {
        if (success) {
          const status = 'Form Submitted!';
          this.toast.toastNotification(message, status);
          this.dynamicForm.disable();
        } else {
          this.toast.toastNotification1(message, status);
        }
      });
      console.log('Form Data:', this.dynamicForm.value);
    } else {
      Object.keys(this.dynamicForm.controls).forEach(field => {
        const control = this.dynamicForm.get(field);
        control.markAsTouched({ onlySelf: true });
      });
      this.dynamicForm.markAllAsTouched();
      console.error('Form is not valid:', this.dynamicForm);
    }
  }

  mapValuesToStructure(fields: any, formValues: any): any[] {
    return fields.map((field, index) => {
      const formValue = formValues.fields[index];
      if (field.type === 'group' && field.fields) {
        return {
          ...field,
          fields: this.mapValuesToStructure(field.fields, formValue)
        };
      } 
      else {
        return {
          ...field,
          value: formValue.value || ''
        };
      }
    });
  }

  clearSignature() {
    this.signaturePad.clear();
  }

  savePad() {
    this.signatureImg = this.signaturePad.toDataURL();
    if (this.signatureImg) {
        (this.dynamicForm.get('fields') as FormArray).controls.forEach(field => {
            if (field.get('name').value === 'signature') {
                field.get('value').setValue(this.signatureImg);
            }
        });
        this.toast.toastNotification('Signature Saved', 'Success!');
    } else {
        this.toast.toastNotification1('Something went wrong', 'Error!');
    }
}
}

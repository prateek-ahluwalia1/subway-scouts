import { Component, ElementRef, ViewChild } from '@angular/core';
import { FormElementsService } from '../form-elements.service';
// import { PDFDocument } from 'pdf-lib';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { Location } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-import-form',
  templateUrl: './import-form.component.html',
  styleUrls: ['../form-types/form-types.component.scss'],
  styles: [
    `
.edit-btn,
.delete-btn {
    padding: 5px 10px;
    border: none;
    background-color: #007BFF;
    color: white;
    cursor: pointer;
}
.delete-btn {
    background-color: #dc3545;
}
    `
  ]
})
export class ImportFormComponent {
  @ViewChild('fileInput') fileInput: ElementRef<HTMLInputElement>;

  pdfContent: string | ArrayBuffer | null = null;
  selectedFileName: string | null = null;
  formElements: any;
  pdfSrc: SafeResourceUrl | null = null;

  formItems = [
    {
      icon: '📄',
      title: 'Import PDF Form',
      description: 'Convert your PDF form to an online form',
      route: '/myforms/pdf-import'
    }
  ];

  constructor(
    private formElementsService: FormElementsService,
    private sanitizer: DomSanitizer,
    private location: Location,
    private router: Router
  ) { }

  async onFileSelected(event: Event): Promise<void> {
    const file = (event.target as HTMLInputElement).files[0];
    if (file) {
      console.log('File selected:', file.name);
      const reader = new FileReader();
      reader.onload = async () => {
        try {
          const arrayBuffer = reader.result as ArrayBuffer;
          console.log('File read successfully');
          // const pdfDoc = await PDFDocument.load(arrayBuffer);
          // console.log('PDF loaded successfully');
          // this.formElements = await this.extractFormFields(pdfDoc);
          // console.log(this.formElements);
          // this.formElementsService.updateFormElements(this.formElements);
        } catch (error) {
          console.error('Error loading PDF:', error);
        }
      };
      reader.readAsArrayBuffer(file);
    }
  }


  // async extractFormFields(pdfDoc: PDFDocument): Promise<any[]> {
  //   const form = pdfDoc.getForm();
  //   console.log('here is form', form);

  //   const formElements = [];
  //   const fields = form.getFields();
  //   console.log(`Total fields found: ${fields.length}`);

  //   fields.forEach((field) => {
  //     console.log('single field', field)

  //     const type = field.constructor.name;
  //     console.log(`Processing field: ${field.getName()} with type: ${type}`);
  //     let element;
  //     const fieldName = field.getName();

  //     switch (type) {
  //       case 'PDFTextField':
  //         element = this.createTextField(field, fieldName);
  //         break;
  //       case 'PDFCheckBox':
  //         element = this.createCheckBoxField(field, fieldName);
  //         break;
  //       case 'PDFDropdown':
  //         element = this.createDropdownField(field, fieldName);
  //         break;
  //       case 'PDFRadioGroup':
  //         element = this.createRadioGroupField(field, fieldName);
  //         break;
  //       case 'PDFSignature':
  //         element = this.createPDFSignature(field, fieldName);
  //         break;
  //       default:
  //         console.log(`Unknown field type: ${type}`);
  //         break;
  //     }
  //     if (element) {
  //       formElements.push(element);
  //       console.log(`Element created:`, element);
  //     }
  //   });

  //   console.log(`Total form elements extracted: ${formElements.length}`);
  //   return formElements;
  // }


  createTextField(field, fieldName: string): any {
    return {
      type: 'single',
      name: fieldName,
      label: fieldName,
      value: '',
      placeholder: fieldName,
      properties: { required: false, inputType: 'text', readonly: false }
    };
  }

  createCheckBoxField(field, fieldName: string): any {
    return {
      type: 'checkboxGroup',
      name: fieldName,
      label: fieldName,
      value: '',
      placeholder: '',
      properties: { required: false, inputType: 'checkbox', readonly: false }
    };
  }

  createDropdownField(field, fieldName: string): any {
    const options = field.getOptions().map((option, index) => ({ id: index + 1, text: option }));
    return {
      type: 'dropdown',
      name: fieldName,
      label: fieldName,
      options,
      properties: { required: false, readonly: false }
    };
  }

  createRadioGroupField(field, fieldName: string): any {
    const options = field.getOptions().map((option, index) => ({
      id: index + 1,
      text: option
    }));
    return {
      type: 'PDFRadioGroup',
      name: fieldName,
      label: fieldName,
      options,
      properties: { required: false, readonly: false }
    };
  }

  createPDFSignature(field, fieldName: string): any {
    return {
      type: 'single',
      name: 'signature',
      label: fieldName,
      value: '',
      placeholder: '',
      properties: { required: false, readonly: false }
    };
  }

  deleteSelectedFile(): void {
    this.selectedFileName = null;
    this.formElements = null;
    console.log('File deleted');

    if (this.fileInput) {
      this.fileInput.nativeElement.value = '';
    }
  }

  goBack() {
    this.formElementsService.updateFormElements([]);
    this.location.back();
  }

  edit() {
    this.router.navigate(['/myforms/form-builder']);
  }
}

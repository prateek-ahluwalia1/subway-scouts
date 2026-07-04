import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class FormElementsService {
  private sharedData: any;
  private formSettingsSubject = new BehaviorSubject<any>({
    backgroundColor: '',
    backgroundImage: '',
    backgroundOpacity: '',
    textColor: ''
  });

  formSettings$ = this.formSettingsSubject.asObservable();

  logo: string | ArrayBuffer | null = null;
  logoSize: number = 50;
  logoAlignment: string = 'center';

  private elementsSubject = new BehaviorSubject<any[]>([]);
  elements$ = this.elementsSubject.asObservable();

  private formElementsSubject = new BehaviorSubject<any[]>([]);
  formElements$ = this.formElementsSubject.asObservable();

  private selectedElementSubject = new BehaviorSubject<any>(null);
  selectedElement$ = this.selectedElementSubject.asObservable();

  private initialElements: any[] = [
    { type: 'heading', name: 'Heading', label: 'Heading', icon: 'mat_outline:format_color_text', value: '', placeholder: '', properties: { required: false, inputType: '', readonly: false } },
    {
      type: 'group', name: 'FullName', label: 'Full Name', icon: 'heroicons_outline:user-group',
      fields: [
        { name: 'firstName', label: 'First Name', placeholder: 'Enter your first name', value: '', properties: { required: false, inputType: 'text', readonly: false } },
        { name: 'lastName', label: 'Last Name', placeholder: 'Enter your last name', value: '', properties: { required: false, inputType: 'text', readonly: false } }
      ]
    },
    { type: 'single', name: 'email', icon: 'mat_outline:email', label: 'Email', value: '', placeholder: 'Enter Email', properties: { required: false, inputType: 'email', readonly: false } },
    { type: 'single', name: 'phone', icon: 'mat_outline:phone', label: 'Phone', value: '', placeholder: 'Enter Phone Number', properties: { required: false, inputType: 'number', readonly: false } },
    {
      type: 'group', name: 'address', label: 'Address', icon: 'mat_outline:home',
      fields: [
        { name: 'streetAddress', label: 'Street Address', placeholder: 'Street Address', value: '', properties: { required: false, inputType: 'text', readonly: false } },
        { name: 'streetAddressLine2', label: 'Street Address Line 2', value: '', placeholder: 'Street Address Line 2', properties: { required: false, inputType: 'text', readonly: false } },
        { name: 'city', label: 'City', placeholder: 'City', value: '', properties: { required: false, inputType: 'text' } },
        { name: 'state', label: 'State / Province', value: '', placeholder: 'State / Province', properties: { required: false, inputType: 'text', readonly: false } },
        { name: 'zip', label: 'Postal / Zip Code', value: '', placeholder: 'Postal / Zip Code', properties: { required: false, inputType: 'text', readonly: false } }
      ]
    },
    { type: 'single', name: 'datepicker', icon: 'mat_outline:calendar_today', value: '', placeholder: 'Select Date', label: 'Select Date', properties: { required: false, inputType: 'date', readonly: false } },
    { type: 'single', name: 'signature', icon: 'mat_outline:mode_edit_outline', value: '', placeholder: '', label: 'Signature', properties: { required: false, inputType: 'button', readonly: false } },
    {
      type: 'dropdown',
      name: 'dropdown',
      label: 'Dropdown',
      icon: 'mat_outline:arrow_drop_down_circle', value: '', properties: { required: false, readonly: false },
      options: [{ id: 1, text: 'Option 1' }, { id: 2, text: 'Option 2' }, { id: 3, text: 'Option 3' }]
    },
    { type: 'single', name: 'textarea', icon: 'mat_outline:text_snippet', label: 'Text Area', value: '', placeholder: 'Enter Text', properties: { required: false, inputType: 'text', readonly: false } },
    {
      type: 'checkboxGroup', name: 'checkboxGroup', label: 'Checkbox Group', icon: 'mat_outline:check_box',
      options: [{ id: 1, text: 'Option 1', selected: false }, { id: 2, text: 'Option 2', selected: false }, { id: 3, text: 'Option 3', selected: false }]
    },
    {
      type: 'PDFRadioGroup',
      name: 'radiogroup',
      label: 'Radio Group',
      icon: 'mat_outline:radio_button_checked',
      options: [{ id: 1, text: 'Option 1' }, { id: 2, text: 'Option 2' }, { id: 3, text: 'Option 3' }],
      properties: { required: false, readonly: false }
    },
    { type: 'submit', name: 'submit', icon: 'mat_outline:send', label: 'Submit' },
  ];

  private pdfBytes: ArrayBuffer;

  constructor() {
    this.elementsSubject.next(this.initialElements);
  }

  updateElements(elements: any[]) {
    this.elementsSubject.next(elements);
  }

  addElement(element: any) {
    const currentElements = this.elementsSubject.value;
    this.elementsSubject.next([...currentElements, element]);
  }

  deleteElement(index: number) {
    const elements = [...this.elementsSubject.value];
    elements.splice(index, 1);
    this.elementsSubject.next(elements);
  }

  updateElement(index: number, newElementData: any) {
    const elements = [...this.elementsSubject.value];
    elements[index] = newElementData;
    this.elementsSubject.next(elements);
  }

  setSelectedElement(element: any): void {
    this.selectedElementSubject.next(element);
  }

  setData(data: any): void {
    this.sharedData = data;
  }

  getData(): any {
    return this.sharedData;
  }

  updateLogoSize(size: number): void {
    this.logoSize = size;
    this.elementsSubject.next(this.elementsSubject.value);
  }

  updateLogoAlignment(alignment: string): void {
    this.logoAlignment = alignment;
    this.elementsSubject.next(this.elementsSubject.value);
  }

  setFormSettings(settings: any): void {
    this.formSettingsSubject.next(settings);
  }

  getFormSettings(): any {
    return this.formSettingsSubject.value;
  }

  setFormElements(elements: any[]): void {
    this.formElementsSubject.next(elements);
  }

  getFormElements(): any[] {
    return this.formElementsSubject.value;
  }

  updateFormElements(elements: any[]) {
    this.formElementsSubject.next(elements);
  }


  setPdfBytes(bytes: ArrayBuffer | string | null) {
    if (typeof bytes === 'string') {
      this.pdfBytes = new TextEncoder().encode(bytes);
    } else {
      this.pdfBytes = bytes;
    }
  }

  getPdfBytes(): Promise<ArrayBuffer> {
    return Promise.resolve(this.pdfBytes);
  }
}

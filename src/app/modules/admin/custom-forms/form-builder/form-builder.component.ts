import { Component, OnInit, ChangeDetectorRef, ElementRef, TemplateRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { trigger, transition, style, animate } from '@angular/animations';
import { FormElementsService } from '../form-elements.service';
import { cloneDeep } from 'lodash';
import { NgbModal, NgbModalConfig, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute, Router } from '@angular/router';
import { MatSidenav } from '@angular/material/sidenav';
import { Location } from '@angular/common';
import { FormBuildService } from '../form-builder.service';
import { GlobalVariable } from 'app/shared/global';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
  selector: 'app-form-builder',
  templateUrl: './form-builder.component.html',
  styleUrls: ['./form-builder.component.scss'],
  providers: [NgbModalConfig, NgbModal],
  animations: [
    trigger('zoomIn', [
      transition(':enter', [
        style({ transform: 'scale(0.5)', opacity: 0 }),
        animate('500ms ease-in', style({ transform: 'scale(1)', opacity: 1 }))
      ])
    ])
  ]
})
export class FormBuilderComponent implements OnInit {
  @ViewChild('logoInput', { static: false }) logoInput: ElementRef;
  formElementSettingsForm: FormGroup;
  @ViewChild('settingsDrawer', { static: true }) settingsDrawer: MatSidenav;
  elements: any[] = [];
  formElements: any[] = [];
  logoData: any[] = [];
  selectedElement: any | null = null;
  backgroundData: any;

  signaturePadOptions: Object = {
    'minWidth': 2,
    'canvasWidth': 550,
    'canvasHeight': 80,
  };

  opened: boolean;
  selectedElementIndex: number | null = null;
  settingsDrawerOpened: boolean = false;
  isChecked: boolean = false;
  @ViewChild('titleModel', { static: false }) titleModel!: TemplateRef<any>;
  @ViewChild('background', { static: false }) background!: TemplateRef<any>;

  title: string;
  formId: any;
  backgroundColor: string = ''; // default background color
  backgroundImage: string | ArrayBuffer | null = null;
  backgroundOpacity: number = 1;
  textColor: string = '';

  backgroundColorSelected: boolean = false;
  backgroundImageSelected: boolean = false;

  isType: string;
  private modalRef: NgbModalRef;

  constructor(
    private fb: FormBuilder,
    private formService: FormBuildService,
    public global: GlobalVariable,
    private toast: ToastServiceService,
    private route: ActivatedRoute,
    private router: Router,
    config: NgbModalConfig,
    public _formService: FormElementsService,
    private cdr: ChangeDetectorRef,
    private modalService: NgbModal,
    private location: Location
  ) {
    config.backdrop = 'static';
    config.keyboard = false;
  }

  ngOnInit() {
    this._formService.elements$.subscribe(elements => {
      this.elements = elements;
    });

    this.formElementSettingsForm = this.fb.group({});
    this.initializeForm();
    this.route.params.subscribe(params => {
      this.formId = params['id'];
      if (this.formId) {
        this.formService.getFormById(this.formId).subscribe(res => {
          const storedData = res.data;
          this.isType = res.type;
          this.title = storedData.title;
          this.formElements = JSON.parse(storedData?.body);
          this.logoData = JSON.parse(storedData?.logo);
          this.backgroundData = JSON.parse(storedData?.background);
          this._formService.logo = this.logoData[0]?.logo;
          this._formService.logoSize = this.logoData[0]?.logoSize;
          this._formService.logoAlignment = this.logoData[0]?.logoAlign;
          this.backgroundColor = this.backgroundData?.backgroundColor;
          this.backgroundImage = this.backgroundData?.backgroundImage || this.backgroundImage;
          this.textColor = this.backgroundData?.textColor || this.textColor;
          this.cdr.markForCheck();
        });
      }
    });

    this._formService.formSettings$.subscribe(settings => {
      this.backgroundColor = settings.backgroundColor;
      this.backgroundImage = settings.backgroundImage;
      this.backgroundOpacity = settings.backgroundOpacity;
      this.textColor = settings.textColor;
      this.cdr.detectChanges();
    });

    this._formService.formElements$.subscribe(elements => {
      this.formElements = elements;
      console.log(this.formElements);
    });
  }

  initializeForm() {
    this.elements.forEach(field => {
      if (field.type === 'group') {
        const group = this.fb.group({});
        field.fields.forEach(subfield => {
          group.addControl(subfield.label, this.fb.control('', Validators.required));
        });
        this.formElementSettingsForm.addControl(field.name, group);
      } else {
        this.formElementSettingsForm.addControl(field.name, this.fb.control('', Validators.required));
      }
    });
  }

  drop(event: CdkDragDrop<any[]>) {
    if (event.previousContainer === event.container) {
      moveItemInArray(this.formElements, event.previousIndex, event.currentIndex);
    } else {
      this.addFieldToDropZone(event.previousContainer.data[event.previousIndex]);
    }
  }

  addFieldToDropZone(field: any) {
    const newField = cloneDeep(field);

    if (newField.type !== 'submit' || !this.formElements.some(el => el.type === 'submit')) {
      this.formElements.push(newField);
    } else if (newField.type === 'dropdown' || newField.type === 'checkboxGroup') {
      this.formElements.push({
        type: newField.type,
        name: newField.name,
        options: newField.options,
        properties: newField.properties ? newField.properties : []
      });
    }
    console.log(this.formElements);
  }

  publishForm(formType?) {
    if (formType) {
      this.isType = formType;
    }
    if (!this.title) {
      this.open();
      return;
    }

    const formJson = this.formElements.map(element => {
      return {
        type: element?.type,
        name: element?.name,
        label: element?.label,
        properties: element?.properties || '',
        value: element.value || '',
        placeholder: element.placeholder || '',
        fields: element.fields ? element.fields.map(field => ({
          label: field.label,
          value: element.value || '',
          name: field.name,
          properties: field?.properties,
          placeholder: field.placeholder
        })) : [],
        options: element.options ? element.options.map(field => ({
          id: field.id,
          text: field.text,
        })) : []
      };
    });

    let params = {
      title: this.title,
      logo: [{
        logo: this._formService.logo,
        logoSize: this._formService.logoSize,
        logoAlign: this._formService.logoAlignment
      }],
      body: formJson,
      type: this.isType,
      admin_id: this.global.admin.admin_id,
      background: JSON.stringify({
        backgroundColor: this.backgroundColor,
        backgroundImage: this.backgroundImage,
        backgroundOpacity: this.backgroundOpacity,
        textColor: this.textColor
      })
    } as {
      title: string,
      body: Object,
      type: string,
      id?: string,
      admin_id: number,
      logo: Array<{
        logo: string | ArrayBuffer;
        logoSize: number;
        logoAlign: string;
      }>,
      background: string;
    };

    if (this.formId) {
      params.id = this.formId;
      this.updateForm(params);
    } else {
      console.log(params);
      this.saveForm(params);
    }
  }

  saveForm(params: any) {
    const status = 'Form Template Operation!';
    this.formService.saveForm(params).subscribe({
      next: (response) => {
        if (response.success) {
          this.router.navigate(['/myforms']);
          this.toast.toastNotification(response.message, status);
        } else {
          this.toast.toastNotification1(response.message, status);
        }
      },
      error: (error) => {
        console.error('Failed to save form', error);
      }
    });
  }

  updateForm(params: any) {
    const status = 'Form Operation!';
    this.formService.updateForm(params).subscribe({
      next: (response) => {
        if (response.success) {
          this.router.navigate(['/myforms']);
          this.toast.toastNotification(response.message, status);
        } else {
          this.toast.toastNotification1(response.message, status);
        }
      },
      error: (error) => {
        console.error('Failed to save form', error);
      }
    });
  }

  onLogoSelected(event: Event): void {
    const file = (event.target as HTMLInputElement).files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = () => {
        this._formService.logo = reader.result;
      };
      reader.readAsDataURL(file);
    }
  }

  open() {
    this.modalService.open(this.titleModel);
  }

  selectElement(index: number): void {
    this.selectedElementIndex = index;
  }

  deleteElement(index: number): void {
    if (index === this.selectedElementIndex) {
      this.formElements.splice(index, 1);
      this.selectedElementIndex = null;
    }
  }

  addOption(options: any[]): void {
    options.push({ id: options.length + 1, text: '' });
  }

  removeOption(options: any[], index: number): void {
    options.splice(index, 1);
  }

  openSettingsPanel(element: any): void {
    this._formService.setSelectedElement(element);
    this.settingsDrawer.open();
  }

  updateElementSettings(updatedElement: any): void {
    if (this.selectedElement) {
      // Update the properties of the selected element
      Object.assign(this.selectedElement, updatedElement);
    }
  }

  onAddLogoClick(): void {
    if (this.logoInput && this.logoInput.nativeElement) {
      this.logoInput.nativeElement.click();
    } else {
      console.error('Logo input element not found.');
    }
  }

  openBackgroundSettings() {
    this.modalRef = this.modalService.open(this.background);
  }

  onBackgroundImageSelected(event: Event) {
    const file = (event.target as HTMLInputElement).files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.backgroundImage = e.target.result;
        this.updateBackgroundSelection();
        this.cdr.detectChanges();
      };
      reader.readAsDataURL(file);
    }
  }

  removeBackgroundImage() {
    this.backgroundImage = '';
    this.updateBackgroundSelection();
    this.cdr.detectChanges();
  }

  onBackgroundColorSelected(event: Event) {
    this.backgroundColor = (event.target as HTMLInputElement).value;
    this.updateBackgroundSelection();
    this.cdr.detectChanges();
  }

  saveBg() {
    console.log('Background Color:', this.backgroundColor);
    console.log('Background Image:', this.backgroundImage);
    console.log('Background Opacity:', this.backgroundOpacity);
  }

  private updateBackgroundSelection(): void {
    this.backgroundColorSelected = !!this.backgroundColor;
    this.backgroundImageSelected = !!this.backgroundImage;
  }

  removeSelectedColor() {
    this.backgroundColor = '';
    this.updateBackgroundSelection();
    this.cdr.detectChanges();
  }

  openFormSettings() {
    this._formService.setSelectedElement({
      type: 'form-settings',
      properties: {
        backgroundColor: this.backgroundColor,
        backgroundImage: this.backgroundImage,
        backgroundOpacity: this.backgroundOpacity,
        textColor: this.textColor
      }
    });
    this.settingsDrawer.open();
  }

  goBack() {
    this.location.back();
  }
}

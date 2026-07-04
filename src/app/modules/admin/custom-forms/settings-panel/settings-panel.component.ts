import { Component, OnInit } from '@angular/core';
import { FormElementsService } from '../form-elements.service';

@Component({
  selector: 'app-settings-panel',
  templateUrl: './settings-panel.component.html',
  styleUrls: ['./settings-panel.component.scss']
})
export class SettingsPanelComponent implements OnInit {
  logoSize: number;
  logoAlignment: string;
  selectedElement: any;
  activeTab: string = 'general';

  constructor(public _formService: FormElementsService) { }

  ngOnInit(): void {
    this._formService.selectedElement$.subscribe(element => {
      this.selectedElement = element;
      this.logoSize = this._formService.logoSize;
      this.logoAlignment = this._formService.logoAlignment;
    });
  }

  setActiveTab(tab: string): void {
    this.activeTab = tab;
  }

  removeLogo() {
    this._formService.logo = '';
  }

  onFileChange(event: Event, type: string): void {
    const file = (event.target as HTMLInputElement).files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = () => {
        if (type === 'logo') {
          this._formService.logo = reader.result;
        } else if (type === 'background') {
          this.updateBackgroundImage(reader.result);
        }
      };
      reader.readAsDataURL(file);
    }
  }

  updateLogoSize(size: number) {
    this._formService.updateLogoSize(size);
  }

  updateLogoAlignment(alignment: string) {
    this._formService.updateLogoAlignment(alignment);
  }

  onReadOnlyChange() {
    if (this.selectedElement.properties.readonly) {
      this.selectedElement.properties.required = false;
    }
  }

  updateBackgroundColor(color: string) {
    if (this.selectedElement) {
      this.selectedElement.properties.backgroundColor = color;
    }
    const settings = this._formService.getFormSettings();
    settings.backgroundColor = color;
    this._formService.setFormSettings(settings);
  }

  updateBackgroundImage(image: string | ArrayBuffer) {
    if (this.selectedElement) {
      this.selectedElement.properties.backgroundImage = image;
    }
    const settings = this._formService.getFormSettings();
    settings.backgroundImage = image;
    this._formService.setFormSettings(settings);
  }

  updateBackgroundOpacity(opacity: number) {
    if (this.selectedElement) {
      this.selectedElement.properties.backgroundOpacity = opacity;
    }
    const settings = this._formService.getFormSettings();
    settings.backgroundOpacity = opacity;
    this._formService.setFormSettings(settings);
  }

  updateTextColor(textColor: string) {
    if (this.selectedElement) {
      this.selectedElement.properties.textColor = textColor;
    }
    const settings = this._formService.getFormSettings();
    settings.textColor = textColor;
    this._formService.setFormSettings(settings);
  }
}

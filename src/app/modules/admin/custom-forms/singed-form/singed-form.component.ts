import { Component, ViewEncapsulation } from '@angular/core';
import { Router } from '@angular/router';
import { FormElementsService } from '../form-elements.service';

@Component({
  selector: 'app-singed-form',
  templateUrl: './singed-form.component.html',
  styleUrls: ['../form-types/form-types.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class SingedFormComponent {
  fileName: string = '';
  fileSize: number = 0;
  thumbnail: string = '';

  constructor(private router: Router, private pdfDataService: FormElementsService) { }

  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length) {
      const file = input.files[0];
      this.fileName = file.name;
      this.fileSize = Math.round(file.size / 1024); // Convert size to KB

      const reader = new FileReader();
      reader.onload = () => {
        this.pdfDataService.setPdfBytes(reader.result as ArrayBuffer);
        this.generateThumbnail(file);
      };
      reader.readAsArrayBuffer(file);
    }
  }

  generateThumbnail(file: File) {
    const reader = new FileReader();
    reader.onload = (e: any) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);
        this.thumbnail = canvas.toDataURL('image/png');
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  createSignableDocument() {
    this.router.navigate(['/myforms/signable-editor']);
  }

  goBack() {
    this.router.navigate(['/myforms']);
  }
}

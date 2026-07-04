import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class PdfGenerationService {

  constructor() { }

  private content: string = '';

  setHtmlContent(content: string) {
    this.content = content;
  }

  getHtmlContent(): string {
    return this.content;
  }
}

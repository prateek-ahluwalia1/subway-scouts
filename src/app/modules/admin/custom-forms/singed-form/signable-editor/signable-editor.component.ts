import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { Location } from '@angular/common';
import { cloneDeep } from 'lodash';
import { FormElementsService } from '../../form-elements.service';
import * as pdfjsLib from 'pdfjs-dist/build/pdf';
import 'pdfjs-dist/build/pdf.worker.entry';
import { saveAs } from 'file-saver';
import { PDFDocument } from 'pdf-lib';
import { SignaturePad } from 'angular2-signaturepad';

@Component({
  selector: 'app-signable-editor',
  templateUrl: './signable-editor.component.html',
  styleUrls: ['./signable-editor.component.scss']
})
export class SignableEditorComponent implements OnInit {

  settingsDrawerOpened: boolean = false;
  formElements: any[] = [];
  elements: any[] = [];
  opened: boolean;
  pdfBytes: ArrayBuffer = null; // Update type to ArrayBuffer
  selectedFieldIndex: number | null = null;

  @ViewChild('pdfCanvas', { static: true }) pdfCanvas: ElementRef;
  @ViewChild('signaturePad', { static: true }) signaturePad: SignaturePad;
  pdf: any;
  signaturePosition = { x: 0, y: 0 };
  signatureSize = { width: 200, height: 100 };
  signaturePadOptions: Object = {
    minWidth: 1,
    maxWidth: 3,
    penColor: "rgb(66, 133, 244)"
  };
  signatureDataUrl: string;

  constructor(
    public _formService: FormElementsService,
    private location: Location
  ) { }

  ngOnInit(): void {
    this._formService.elements$.subscribe(elements => {
      this.elements = elements;
    });

  }

  onFileSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.loadPdf(e.target.result);
      };
      reader.readAsArrayBuffer(file);
    }
  }

  loadPdf(data: ArrayBuffer) {
    const loadingTask = pdfjsLib.getDocument({ data });
    loadingTask.promise.then((pdf: any) => {
      this.pdf = pdf;
      this.renderPage(1);
    });
  }

  renderPage(pageNumber: number) {
    this.pdf.getPage(pageNumber).then((page: any) => {
      const viewport = page.getViewport({ scale: 1.5 });
      const canvas = document.createElement('canvas');
      const context = canvas.getContext('2d')!;
      canvas.height = viewport.height;
      canvas.width = viewport.width;

      const renderContext = {
        canvasContext: context,
        viewport: viewport
      };
      page.render(renderContext);
      document.getElementById('pdf-canvas-container')!.appendChild(canvas);
    });
  }

  saveSignature() {
    this.signatureDataUrl = this.signaturePad.toDataURL();
  }

  onDragEnd(event: any) {
    this.signaturePosition = {
      x: event.source.getFreeDragPosition().x,
      y: event.source.getFreeDragPosition().y
    };
  }

  onResize(event: any) {
    this.signatureSize = {
      width: event.rectangle.width,
      height: event.rectangle.height
    };
  }

  savePdf() {
    // Logic to merge signature into PDF and send to server
    console.log('Signature Data URL:', this.signatureDataUrl);
    console.log('Signature Position:', this.signaturePosition);
    console.log('Signature Size:', this.signatureSize);
  }


  goBack() {
    this.location.back();
  }
}

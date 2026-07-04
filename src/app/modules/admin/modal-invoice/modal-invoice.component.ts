import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
// import jsPDF from "jspdf";
import html2canvas from 'html2canvas';
import { MatChipInputEvent } from '@angular/material/chips';
import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { StaffService } from 'app/services/staff.service';
import { HttpHeaders } from '@angular/common/http';
import { ToastServiceService } from 'app/services/toast-service.service';
export interface newStaff {
  email: string;
}
@Component({
  selector: 'app-modal-invoice',
  templateUrl: './modal-invoice.component.html',
  styleUrls: ['./modal-invoice.component.scss']
})
export class ModalInvoiceComponent implements OnInit {
  newStaff: newStaff[] = [];
  @Input() fromParent;
  @Input() date;
  @Input() grand_total;

  readonly separatorKeysCodes: number[] = [ENTER, COMMA];

  add(event: MatChipInputEvent, type): void {
    const input = event.input;
    const value = event.value;
    if ((value || '').trim()) {
      this.newStaff.push({ email: value.trim() });
    }
    // Reset the input value
    if (input) {
      input.value = '';
    }
  }

  remove(fruit: newStaff, type): void {

    const index = this.newStaff.indexOf(fruit);
    if (index >= 0) {
      this.newStaff.splice(index, 1);
    }

  }
  constructor(private modalService: NgbActiveModal, private model: NgbModal, private service: StaffService,
    private toast: ToastServiceService, private ngmodel: NgbModal) { }
  @ViewChild('modalContent') content: ElementRef;
  ngOnInit(): void {

    console.log(this.grand_total);

  }
  close() {
    this.modalService.dismiss();
  }
  createPdf() {
    const content: HTMLElement = this.content.nativeElement;

    html2canvas(content).then((canvas) => {
      // const imgData = canvas.toDataURL('image/png');
      // console.log('image or pdf', imgData);

      // const pdf = new jsPDF({
      //   orientation: 'portrait',
      //   unit: 'mm',
      //   format: 'a4',
      //   compress: true
      // });

      // const imgProps = pdf.getImageProperties(imgData);
      // const pdfWidth = pdf.internal.pageSize.getWidth();
      // const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
      // pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
      // pdf.save('download.pdf');
    });
  }

  dismiss() {
    this.ngmodel.dismissAll();
  }
  sendInvoice(addGuard) {
    this.model.open(addGuard, {
      windowClass:
        "add-staff-document-class guard-for-site-modal",
      animation: true,
    });
  }

  send() {
    const content: HTMLElement = this.content.nativeElement;

    html2canvas(content).then((canvas) => {
      const imgData = canvas.toDataURL('image/png');
      // Assuming you have the base64 string stored in the variable 'imgData'
      // and you want to specify a file name for the resulting file

      // Split the base64 string into two parts: data type and data content
      const [dataType, base64Content] = imgData.split(',');

      // Decode the base64 content
      const decodedContent = atob(base64Content);

      // Create an ArrayBuffer from the decoded content
      const arrayBuffer = new ArrayBuffer(decodedContent.length);

      // Create a Uint8Array from the ArrayBuffer
      const uint8Array = new Uint8Array(arrayBuffer);

      // Populate the Uint8Array with the decoded content
      for (let i = 0; i < decodedContent.length; i++) {
        uint8Array[i] = decodedContent.charCodeAt(i);
      }

      // Create a Blob object from the Uint8Array
      const blob = new Blob([uint8Array], { type: dataType });

      // Create a File object from the Blob
      const file = new File([blob], 'filename.png', { type: dataType });

      // Now you have a File object representing the converted image file
      console.log('file', file);

      const myFormData = new FormData();
      const headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');
      myFormData.append('invoice', file);
      myFormData.append('email', JSON.stringify(this.newStaff))
      this.service.sendInvoiceToCustoemr(myFormData).subscribe(
        response => {
          if (response.success) {
            this.toast.toastNotification(response.message, 'Invoice Operation!')
            this.dismiss()
          }
          else {
            this.toast.toastNotification1('Something went wrong please try later', 'Invoice Operation!')
          }
        },
        (error) => {
          console.error(error);
        }
      );

    });


  }

}

import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
import { PdfServiceService } from 'app/services/pdf-service.service';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import * as FileSaver from 'file-saver';
@Component({
  selector: 'app-pdf',
  templateUrl: './pdf.component.html',
  styleUrls: ['./pdf.component.scss']
})
export class PdfComponent implements OnInit {
  @Input() res;
  @Input() staff;
  @ViewChild('pdfTable', { static: false }) pdfTable: ElementRef;

  constructor(private pdfService: PdfServiceService, private serviceModel: NgbActiveModal) { }
  ngOnInit(): void {
    console.log(this.res);
    console.log(this.staff);

  }

  close() {
    this.serviceModel.close()
  }

  downloadAsPDF() {
    // const data = this.pdfTable.nativeElement;
    // html2canvas(data).then((canvas) => {
    //   let fileWidth = 208;
    //   let fileHeight = (canvas.height * fileWidth) / canvas.width;
    //   const FILEURI = canvas.toDataURL("image/png");
    //   let PDF = new jsPDF("p", "mm", "a4");
    //   let position = 0;
    //   PDF.addImage(FILEURI, "PNG", 0, position, fileWidth, fileHeight);
    //   PDF.save("chat-summary.pdf");
    // });
  }

  downloadAsCSV() {
    const tableData = this.res?.data;
    let csvContent = 'Action,Date,Reason,Changed By\n';

    tableData.forEach((item) => {
      csvContent += `${item.action_type},${item.date},${item.reason},${item.action_by}\n`;
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8' });
    FileSaver.saveAs(blob, `${this.staff?.name}.csv`);
  }
}

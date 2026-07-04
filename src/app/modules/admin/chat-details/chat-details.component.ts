import { Component, ElementRef, Input, OnInit, ViewChild } from "@angular/core";
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";
import html2canvas from "html2canvas";
// import jsPDF from "jspdf";
import moment from "moment";

@Component({
  selector: "app-chat-details",
  templateUrl: "./chat-details.component.html",
  styleUrls: ["./chat-details.component.scss"],
})
export class ChatDetailsComponent implements OnInit {
  @Input() chats;
  @Input() dates;
  @Input() search_term
  @ViewChild("content") content: ElementRef;
  constructor(private modalService: NgbActiveModal) {}

  ngOnInit(): void {

    // const isAlphabet = /^[a-zA-Z]+$/.test(this.search_term);


    console.log("chats ", this.chats);
    if (this.dates.to && this.dates.from) {
      let start = moment(this.dates.to);
      this.dates.to = start.format("DD-MMM-YYYY");

      let end = moment(this.dates.from);
      this.dates.from = end.format("DD-MMM-YYYY");
    } else {
      const currentMonth = moment();

      const monthStartDate = currentMonth
        .startOf("month")
        .format("DD-MMM-YYYY");
      this.dates.to = monthStartDate;

      const monthEndDate = currentMonth.endOf("month").format("DD-MMM-YYYY");
      this.dates.from = monthEndDate;
    }
  }

  createPdf() {
    // const data = this.content.nativeElement;
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

  close() {
    this.modalService.dismiss();
  }

  date(time) {
    return moment(time * 1000).format("DD-MM-YYYY");
  }
  mergeName(first, last) {
    if (first != null && last != null) {
      return `${first} ${last}`;
    }
  }
}

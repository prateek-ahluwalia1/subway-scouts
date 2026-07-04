import {
  Component,
  OnInit,
  OnDestroy,
  Inject,
  ElementRef,
  ViewChild,
} from "@angular/core";
import { Subscription } from "rxjs";
import { DialogBelonging } from "@costlydeveloper/ngx-awesome-popup";
import { RosterServiceService } from "../roster-service.service";
import { ServiceService } from "app/services/service.service";
import moment from "moment";
import { RunsheetrosterService } from "app/services/runsheetroster.service";
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";

import { TDocumentDefinitions } from "pdfmake/interfaces";
pdfMake.vfs = pdfFonts.pdfMake.vfs;
@Component({
  selector: "app-view-deleted-shifts",
  templateUrl: "./view-deleted-shifts.component.html",
  styleUrls: ["./view-deleted-shifts.component.scss"],
})
export class ViewDeletedShiftsComponent implements OnInit, OnDestroy {
  startDate: string;
  endDate: string;
  adminList: any = [];
  seletedAdmins;
  shiftsData: any = [];
  private subscriptions: Subscription = new Subscription();
  @ViewChild("pdfTable", { static: false }) pdfTable: ElementRef;

  constructor(
    @Inject("dialogBelonging") public dialogBelonging: DialogBelonging,

    private service: RosterServiceService,
    private serviceservice: ServiceService,
    private roster: RunsheetrosterService
  ) { }

  ngOnInit(): void {
    this.setDefaultDateRange();
    this.getAllAdmins();
    if (this.dialogBelonging.customData.name === "run_sheet") {
      let data = {
        start: this.startDate,
        end: this.endDate,
        admin_id: this.seletedAdmins,
        run_sheet_id: this.dialogBelonging.customData.id,
      };
      if (this.dialogBelonging.customData) {
        this.roster.getDeletedShifts(data).subscribe((res) => {
          if (res.success) {
            this.shiftsData = res;
            this.dialogBelonging.eventsController.closeLoader();
          }
        });
      }
    } else {
      let data = {
        start: this.startDate,
        end: this.endDate,
        admin_id: this.seletedAdmins,
        roster_id: this.dialogBelonging.customData.id,
      };
      if (this.dialogBelonging.customData) {
        this.service.getDeletedShifts(data).subscribe((res) => {
          if (res.success) {
            this.shiftsData = res;
            this.dialogBelonging.eventsController.closeLoader();
          }
        });
      }
    }
    this.subscriptions.add(
      this.dialogBelonging.eventsController.onButtonClick$.subscribe(
        (_Button) => {
          if (_Button.ID === "submit") {
            this.generatepdf();
          } else if (_Button.ID === "close") {
            this.dialogBelonging.eventsController.close();
          }
        }
      )
    );
    setTimeout(() => {
      this.dialogBelonging.eventsController.closeLoader();
    }, 5000);
  }

  generatepdf() {
    const generateTableBody = () => {
      const headerRow = [
        { text: "Site", style: "tableHeader", bold: true },
        { text: "Assign To", style: "tableHeader", bold: true },
        { text: "Start", style: "tableHeader", bold: true },
        { text: "End", style: "tableHeader", bold: true },
        { text: "Reason", style: "tableHeader", bold: true },
        { text: "Changed By", style: "tableHeader", bold: true },
      ];

      const dataRows = this.shiftsData.deletedShifts.map((item, index) => [
        {
          text: item.site_name || item.runsheet_name || "N/A",
          fillColor: index % 2 === 0 ? "#f2f2f2" : "",
        },
        {
          text: item.staff_name || "N/A",
          fillColor: index % 2 === 0 ? "#f2f2f2" : "",
        },
        {
          text: item.start || "N/A",
          fillColor: index % 2 === 0 ? "#f2f2f2" : "",
        },
        {
          text: item.end || "N/A",
          fillColor: index % 2 === 0 ? "#f2f2f2" : "",
        },
        {
          text: item.reason || "N/A",
          fillColor: index % 2 === 0 ? "#f2f2f2" : "",
        },
        {
          text: item.deleted_by || "N/A",
          fillColor: index % 2 === 0 ? "#f2f2f2" : "",
        },
      ]);

      return [headerRow, ...dataRows];
    };

    let docDefinition: TDocumentDefinitions = {
      pageSize: "A4",
      content: [
        { text: "Deleted Shifts", style: "h1" },
        {
          columns: [
            {
              table: {
                headerRows: 1,
                widths: ["auto", "auto", "auto", "auto", "auto", "auto"],
                body: generateTableBody(),
                dontBreakRows: true,
              },
            },
          ],
        },
      ],
      styles: {
        tableHeader: {
          fillColor: "#24af8d",
          color: "#ffffff",
        },
        h1: {
          fontSize: 18,
          bold: true,
          margin: [0, 0, 0, 10],
        },
      },
    };

    pdfMake.createPdf(docDefinition).download("Deleted_Shifts.pdf");
  }

  setDefaultDateRange() {
    const currentDate = moment();
    const startOfWeek = currentDate.startOf("week").format("YYYY-MM-DD");
    const endOfWeek = currentDate.endOf("week").format("YYYY-MM-DD");

    this.startDate = startOfWeek;
    this.endDate = endOfWeek;
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  downloadAsCSV() { }

  getAllAdmins() {
    this.serviceservice.getAllAdmins().subscribe(({ success, data }) => {
      if (success) {
        this.adminList = data;
      }
    });
  }

  searchDelShifts() {
    let data = {
      start: this.startDate,
      end: this.endDate,
      admin_id: this.seletedAdmins,
      roster_id: this.dialogBelonging.customData.id,
    };
    this.service.getDeletedShifts(data).subscribe((res) => {
      console.log(res);
    });
  }

  receiveDataFromChild(data: any) {
    this.seletedAdmins = data?.value.map((item) => item.id);
  }
}

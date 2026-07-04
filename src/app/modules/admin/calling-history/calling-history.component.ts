import { Component, ElementRef, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { FormControl, FormGroup } from "@angular/forms";
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { AircalService } from './../../../services/aircal.service';
import { takeUntil } from 'rxjs/operators';
import { Subject } from 'rxjs';
import { DateAdapter } from "@angular/material/core";

@Component({
  selector: "app-calling-history",
  templateUrl: "./calling-history.component.html",
  styleUrls: ["./calling-history.component.scss"],
})
export class CallingHistoryComponent implements OnInit, OnDestroy {
  @ViewChild(MatPaginator) paginator: MatPaginator;
  @ViewChild('audioPlayer') audioPlayer: ElementRef;

  private destroy$: Subject<void> = new Subject<void>();

  searchTerm = "";
  range = new FormGroup({
    start: new FormControl<Date | null>(null),
    end: new FormControl<Date | null>(null),
  });

  records: any[] = [];
  currentPage = 0;
  pageSize = 20;
  totalRecords = 0;

  constructor(
    private aircalService: AircalService,
    public dateAdapter: DateAdapter<Date>,

  ) { 
    this.dateAdapter.setLocale("en-AU");

  }

  ngOnInit(): void {
    const today = new Date();
    const currentDay = today.getDay();
    const startDate = new Date(today);
    this.range.get('start').setValue(startDate)
    startDate.setDate(today.getDate() - currentDay);
    const endDate = new Date(today);
    endDate.setDate(today.getDate() + (6 - currentDay));
    this.range.get('end').setValue(endDate)
    this.fetchRecords();

    this.range.valueChanges.pipe(takeUntil(this.destroy$)).subscribe((value) => {
      this.currentPage = 0;
      this.fetchRecords();
    });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex;
    this.pageSize = event.pageSize;
    this.fetchRecords();
  }

  fetchRecords(): void {
    const from = this.range.value.start?.getTime() / 1000;
    const to = this.range.value.end?.getTime() / 1000;
    this.aircalService.statics(from, to, this.currentPage + 1, this.pageSize, 'asc')
      .subscribe(
        ({ calls, meta }) => {
          this.totalRecords = meta.total;
          this.records = calls.map((element) => {
            const localDate = new Date(element.started_at * 1000);
            const localDate1 = new Date(element.answered_at * 1000);
            const localDate2 = new Date(element.ended_at * 1000);
            return {
              ...element,
              started_at: localDate.toLocaleString(),
              answered_at: localDate1.toLocaleString(),
              ended_at: localDate2.toLocaleString(),
              audio: element.recording
            };
          });
        },
        (error) => {
          console.error('Error fetching records:', error);
        }
      );
  }


  seekTo(event: MouseEvent): void {
    const progressBar = event.target as HTMLElement;
    const rect = progressBar.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const width = rect.width;
    const duration = this.audioPlayer.nativeElement.duration;
    this.audioPlayer.nativeElement.currentTime = (x / width) * duration;
  }

  formatTime(time: number): string {
    const minutes = Math.floor(time / 60);
    const seconds = Math.floor(time % 60);
    return `${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
  }

  pageChanged(event: any) {
    this.currentPage = event.pageIndex;
    this.fetchRecords();
  }

}

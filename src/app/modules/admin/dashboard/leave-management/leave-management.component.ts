import { FullScreenService } from '../full-screen.service';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit, OnDestroy, ViewChild, ViewEncapsulation, Output, EventEmitter } from '@angular/core';
import { animate, query, stagger, style, transition, trigger } from '@angular/animations';
import { ServiceService } from 'app/services/service.service';
import { Subject, takeUntil } from 'rxjs';
import { filter } from 'rxjs/operators';
import { LeaveDetailsComponent } from '../../models/leave-details/leave-details.component';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-leave-management',
  templateUrl: './leave-management.component.html',
  styleUrls: ['./leave-management.component.scss'],
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('700ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('700ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ],
  encapsulation: ViewEncapsulation.None,
  changeDetection: ChangeDetectionStrategy.OnPush
})

export class LeaveManagementComponent implements OnInit, OnDestroy {

  leaves: any[] = [];
  @ViewChild('fullScreen') divRef;
  isFullscreen: boolean = false;
  searchStaff: string = '';
  private _unsubscribeAll: Subject<any> = new Subject<any>();
  @Output() leaveMore = new EventEmitter<any>()
  leavesDuration: boolean = true;

  constructor(
    private fullScreenService: FullScreenService,
    private service: ServiceService,
    private changeDetect: ChangeDetectorRef,
    private modalService: NgbModal
  ) { }

  ngOnInit(): void {
    this.service.leaves$.pipe(filter(res => res && res.data),
    takeUntil(this._unsubscribeAll))
    .subscribe((res: any) => {
      res?.data.forEach(element => {
        element.name = element.guard_first_name + ' ' + element.guard_last_name
      });
      this.leaves = res?.data
      this.changeDetect.markForCheck();
    });

    let data = {
      type: 'week'
    }
    this.service.getAllLeaves(data).subscribe()
  }

  openFullscreen() {
    const elem = this.divRef.nativeElement;
    this.fullScreenService.toggleFullscreen(elem);
    this.isFullscreen = !this.isFullscreen;
  }

  ngOnDestroy(): void {
    // Unsubscribe from all subscriptions
    this._unsubscribeAll.next(null);
    this._unsubscribeAll.complete();
  }

  openModal(guard, type?) {
    const modalRef = this.modalService.open(LeaveDetailsComponent, {
      scrollable: true, windowClass: "create"
      , size: 'small'
    })
    modalRef.componentInstance.fromParent = guard;
    modalRef.componentInstance.type = type;
    modalRef.result.then((result) => {
      console.log("Modal Result", `Closed with: ${result}`)
    }, (reason) => {
      let data = {
        type: this.leavesDuration ? 'week' : 'month'
      }
      this.service.getAllLeaves(data).subscribe()
      console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)

    });
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else if (reason === 'updated') {
      console.log(reason);
    }
  }

  showAllLeave() {
    this.leavesDuration = !this.leavesDuration
    this.leaves = []
    let data = {
      type: this.leavesDuration ? 'week' : 'month'
    }
    this.service.getAllLeaves(data).subscribe()
  }

  trackById(index: number, guard: any) {
    return guard.id
  }

}

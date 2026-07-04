import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { MatDrawer } from '@angular/material/sidenav';
import { Subject, takeUntil } from 'rxjs';
import { FuseMediaWatcherService } from '@fuse/services/media-watcher';
import { FileManagerService } from 'app/modules/admin/file-manager/file-manager.service';
import { Item, Items } from 'app/modules/admin/file-manager/file-manager.types';
import { MatDialog } from '@angular/material/dialog';
import { FileManagerDetailsComponent } from '../details/details.component';

@Component({
  selector: 'app-documents',
  templateUrl: './documents.component.html',
})
export class DocumentsComponent implements OnInit {


  @ViewChild('matDrawer', { static: true }) matDrawer: MatDrawer;
  drawerMode: 'side' | 'over';
  selectedItem: Item;
  items: any[] = [];
  private _unsubscribeAll: Subject<any> = new Subject<any>();
  userType: any;

  /**
   * Constructor
   */
  constructor(
    private _activatedRoute: ActivatedRoute,
    private _changeDetectorRef: ChangeDetectorRef,
    private _router: Router,
    private _fileManagerService: FileManagerService,
    private _fuseMediaWatcherService: FuseMediaWatcherService,
    private route: ActivatedRoute,
    public dialog: MatDialog
  ) {
  }

  // -----------------------------------------------------------------------------------------------------
  // @ Lifecycle hooks
  // -----------------------------------------------------------------------------------------------------

  /**
   * On init
   */
  ngOnInit(): void {

    this.route.params.subscribe(params => {
      const id = params['id'];
      this.userType = params['userType'];
      if (id) {
        this._fileManagerService.getSpecificStaffFiles(id, this.userType).subscribe(({ success, data }) => {
          if (success && data.file) {
            console.log(data);
            data.type = data.file.split('.').pop();
            this.items = data;
            this._changeDetectorRef.markForCheck();
          }
        },
          (error) => {
            console.error('Error fetching items:', error);
          }
        );
      }
    });


    // Subscribe to media query change
    this._fuseMediaWatcherService.onMediaQueryChange$('(min-width: 1440px)')
      .pipe(takeUntil(this._unsubscribeAll))
      .subscribe((state) => {

        // Calculate the drawer mode
        this.drawerMode = state.matches ? 'side' : 'over';

        // Mark for check
        this._changeDetectorRef.markForCheck();
      });
  }

  /**
   * On destroy
   */
  ngOnDestroy(): void {
    // Unsubscribe from all subscriptions
    this._unsubscribeAll.next(null);
    this._unsubscribeAll.complete();
  }

  /**
   * On backdrop clicked
   */
  onBackdropClicked(): void {
    // Go back to the list
    this._router.navigate(['./'], { relativeTo: this._activatedRoute });

    // Mark for check
    this._changeDetectorRef.markForCheck();
  }


  trackByFn(index: number, item: any): any {
    return item.id || index;
  }

  openRightSideModal(item) {
    const dialogRef = this.dialog.open(FileManagerDetailsComponent, {
      width: '400px', // Adjust the width as needed
      height: '100%',
      position: {
        right: '0', // Position the modal on the right side
      },
      data: {
        item
        // Pass any data you need to the modal component here
      },
      panelClass: 'custom-doc-detail',
    });

    dialogRef.afterClosed().subscribe((result) => {
      // Handle any actions after the modal is closed
    });
  }

}

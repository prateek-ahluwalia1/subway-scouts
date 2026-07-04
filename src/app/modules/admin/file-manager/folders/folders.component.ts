import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { MatDrawer } from '@angular/material/sidenav';
import { Subject, takeUntil } from 'rxjs';
import { FuseMediaWatcherService } from '@fuse/services/media-watcher';
import { FileManagerService } from 'app/modules/admin/file-manager/file-manager.service';
import { Item, Items } from 'app/modules/admin/file-manager/file-manager.types';
import { MatDialog } from '@angular/material/dialog';
import { FileManagerDetailsComponent } from '../details/details.component';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-folders',
  templateUrl: './folders.component.html',
})
export class FoldersComponent implements OnInit {
  searchText: string;

  @ViewChild('matDrawer', { static: true }) matDrawer: MatDrawer;
  drawerMode: 'side' | 'over';
  selectedItem: Item;
  items: any[] = [];
  private _unsubscribeAll: Subject<any> = new Subject<any>();
  filteredItems: any[] = [];
  userType
  constructor(
    private _activatedRoute: ActivatedRoute,
    private _changeDetectorRef: ChangeDetectorRef,
    private _router: Router,
    private _fileManagerService: FileManagerService,
    private _fuseMediaWatcherService: FuseMediaWatcherService,
    private route: ActivatedRoute, public dialog: MatDialog,
    private spinner: NgxSpinnerService,
  ) {
    this.searchText = '';
  }

  // -----------------------------------------------------------------------------------------------------
  // @ Lifecycle hooks
  // -----------------------------------------------------------------------------------------------------

  /**
   * On init
   */
  ngOnInit(): void {

    this.route.params.subscribe((params) => {
      console.log(params);

      const id = params['folderId'];
      this.userType = params['userType'];
      if (id) {
        this._fileManagerService.getSpecificStaffDocs(id, this.userType).subscribe(({ success, data }) => {
          if (success) {
            data?.forEach((element) => {
              element.name = [
                element.document_name,
              ]
                .filter(Boolean)
                .join(" ");

                element.type = element.file.split('.').pop();
            });
            this.items = data;
            this.filteredItems = [...this.items];
            console.log("Filtered type", this.filteredItems)
          }

          this._changeDetectorRef.markForCheck();

        },
          (error) => {
            console.error('Error fetching items:', error);
          }
        );
      }
      console.log('ID from URL:', id);

      // You can now use 'id' in your component as needed.
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

  // -----------------------------------------------------------------------------------------------------
  // @ Public methods
  // -----------------------------------------------------------------------------------------------------

  /**
   * On backdrop clicked
   */
  onBackdropClicked(): void {
    // Go back to the list
    this._router.navigate(['./'], { relativeTo: this._activatedRoute });

    // Mark for check
    this._changeDetectorRef.markForCheck();
  }

  /**
   * Track by function for ngFor loops
   *
   * @param index
   * @param item
   */
  trackByFn(index: number, item: any): any {
    return item.id || index;
  }


  performSearch(): void {
    if (this.searchText.trim() === '') {
      this.filteredItems = [...this.items]; // Reset filteredItems if search text is empty
    } else {
      // Implement your search logic here based on this.searchText
      // For example, filter items by name containing searchText
      this.filteredItems = this.items.filter((item) =>
        item.name.toLowerCase().includes(this.searchText.toLowerCase())
      );
    }
    this._changeDetectorRef.markForCheck();
  }

  goBack(){
    window.history.back();
  }

  openRightSideModal(item) {

    const filteredDoc = this.filteredItems.find(obj => obj.id === item);
    console.log("Filtered Doc", filteredDoc);
    
    const dialogRef = this.dialog.open(FileManagerDetailsComponent, {
      width: '400px', // Adjust the width as needed
      height: '100%',
      position: {
        right: '0', // Position the modal on the right side
      },
      data: {
        filteredDoc
        // Pass any data you need to the modal component here
      },
      panelClass: 'custom-doc-detail',
    });

    dialogRef.afterClosed().subscribe((result) => {
      // Handle any actions after the modal is closed
    });
  }

  downloadFile(fileUrl: string, fileName: string): void {
    const anchor = document.createElement('a');
    anchor.style.display = 'none';
    anchor.href = fileUrl;
    anchor.download = fileName;
    anchor.setAttribute('target', '_blank');
    document.body.appendChild(anchor);
    anchor.click();
    document.body.removeChild(anchor);
  }

  downloadPdf(url: string, fileName: string, item) {
    if (item?.type == 'xls') {
        this.downloadExcelFile(url, fileName)
    }
    const link = document.createElement('a');
    link.setAttribute('target', '_blank');
    link.setAttribute('href', url);
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    link.remove();
}
downloadExcelFile(fileUrl: string, fileName: string) {
    const link = document.createElement('a');
    link.href = fileUrl;
    link.target = '_blank';
    link.download = fileName;
    link.click();
}


}

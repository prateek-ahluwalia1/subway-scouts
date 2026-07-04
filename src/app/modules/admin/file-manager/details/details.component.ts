import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Inject, OnDestroy, OnInit, ViewEncapsulation } from '@angular/core';
import { MatDrawerToggleResult } from '@angular/material/sidenav';
import { Subject, takeUntil } from 'rxjs';
import { FileManagerListComponent } from 'app/modules/admin/file-manager/list/list.component';
import { FileManagerService } from 'app/modules/admin/file-manager/file-manager.service';
import { Item } from 'app/modules/admin/file-manager/file-manager.types';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';

@Component({
    selector: 'file-manager-details',
    templateUrl: './details.component.html',
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class FileManagerDetailsComponent implements OnInit {
    item;
    file
    constructor(
        @Inject(MAT_DIALOG_DATA) public data: any,
        public dialogRef: MatDialogRef<FileManagerDetailsComponent>,
        private http: HttpClient,private sanitizer: DomSanitizer
    ) {
        this.item = data?.filteredDoc
        this.file = this.sanitizer.bypassSecurityTrustResourceUrl(this.item.file);

        console.log("Item Data", this.item);

    }

    ngOnInit(): void {

    }

    onCloseClick(): void {
        this.dialogRef.close();
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


    downloadFile(fileUrl: string, fileName: string): void {
        console.log("Download file", fileUrl, fileName)
        const anchor = document.createElement('a');
        anchor.style.display = 'none';
        anchor.href = fileUrl;
        anchor.download = fileName;
        anchor.setAttribute('target', '_blank');
        document.body.appendChild(anchor);
        anchor.click();
        document.body.removeChild(anchor);
    }





}

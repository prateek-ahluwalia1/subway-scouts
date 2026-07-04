import { HttpHeaders } from '@angular/common/http';
import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { DomSanitizer } from '@angular/platform-browser';
import { ActivatedRoute } from '@angular/router';
import { CustomerService } from 'app/services/customer.service';
import { DocsService } from 'app/services/docs.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-files-list',
  templateUrl: './files-list.component.html',
  styleUrls: ['./files-list.component.scss']
})
export class FilesListComponent implements OnInit {

  @ViewChild('createFile') createFile: ElementRef;
  @ViewChild('createFolder') createFol: ElementRef;
  AddFolderName: FormGroup;

  AddFileName: FormGroup;
  folderId;
  childfolderId;
  id = '';
  dateNull = 'null';
  file: boolean = false;

  constructor(private fb: FormBuilder, private cusService: CustomerService, private toast: ToastServiceService,
    private route: ActivatedRoute, private global: GlobalVariable, private docService: DocsService,
    public dateAdapter: DateAdapter<Date>,
    private sanitizer: DomSanitizer, private spinner: NgxSpinnerService,) {
    this.dateAdapter.setLocale("en-AU");
    this.route.params.subscribe(params => {
      console.log(params);
      this.folderId = params['id'];
      this.childfolderId = params['subfolder'];
      if (this.childfolderId) {
        this.getAllFiles();
      }
      else {
        this.getAllFiles();
      }
    });
  }

  ngOnInit(): void {

    this.AddFolderName = this.fb.group({
      name: new FormControl(''),
    })

    this.AddFileName = this.fb.group({
      name: new FormControl(''),
      file_link: new FormControl(''),
      file_size: new FormControl(''),
      expiry_date: new FormControl(''),
    })
  }

  viewCondition;
  headingText;
  getData
  filename
  previewUrl

  file_name
  uploadFileUrl
  openFilecanvas(value, id) {
    if (value == 'open') {
      this.viewCondition = true;
      this.file = false;
      this.headingText = 'Upload File';
    }
    else if(value == 'edit') {
      this.viewCondition = true;
      this.headingText = 'Update File Details';
      let data = {
        id: id,
      }
      this.docService.getSpecFile(data).subscribe(({ success, data }) => {
        if (success) {
          this.AddFileName.get('name').setValue(data.name)
          this.AddFileName.get('expiry_date').setValue(data.expiry_date)
          this.AddFileName.get('file_link').setValue(data.file_link)
          if (data.file_link) {
            this.file = true
            this.file_name = 'View Uploaded File'
            this.uploadFileUrl = data.file_link
          }
        }
      })
    }
    else {
      this.viewCondition = false;
      this.headingText = 'File Details';
      let data = {
        id: id,
      }
      this.docService.getSpecFile(data).subscribe(({ success, data }) => {
        if (success) {
          this.getData = data;
          this.previewUrl = this.sanitizer.bypassSecurityTrustResourceUrl(this.getData.file_link);
        }
      })
    }
    this.createFile.nativeElement.classList.add('show');
  }
  closeFilecanvas() {
    this.createFile.nativeElement.classList.remove('show');
  }

  viewFile() {
    window.open(this.uploadFileUrl, '_blank')
  }

  fileuploaded: boolean = false
  handleFileInput() {
    this.fileuploaded = false
    this.file = false
  }

  uploadFile: any
  roundedFileSizeInKB
  uploadFiles(event) {
    this.spinner.show()
    this.uploadFile = event.target.files[0];

    if (this.uploadFile) {
      const fileSizeInBytes = this.uploadFile.size;
      const fileSizeInKB = fileSizeInBytes / 1024; // Kilobytes
      this.roundedFileSizeInKB = Math.round(fileSizeInKB);
      this.AddFileName.get('file_size').setValue(this.roundedFileSizeInKB);

      const myFormData = new FormData();
      const headers = new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      });

      myFormData.append('file', this.uploadFile, this.uploadFile.name);
      myFormData.append('folder', 'company_documents');

      this.cusService.uploadImgPdf(myFormData, {
        headers: headers
      }).subscribe(
        response => {
          if (response.success) {
            this.AddFileName.get('file_link').setValue(response.url);
            this.spinner.hide()
          } else {
            this.spinner.hide()
            this.toast.toastNotification1('Something went wrong please check your file size or connection', 'File Upload!');
          }
        },
        (error) => {
          this.spinner.hide()

          console.error(error);
        }
      );
    }
  }

  value
  createNew
  SaveFile() {
    if (this.childfolderId) {
      if(this.headingText === 'Update File Details'){
        this.value = {
          name: this.AddFileName.value.name,
          file_link: this.AddFileName.value.file_link,
          folder_id: this.childfolderId,
          created_by: this.global.admin.admin_id,
          file_size: this.AddFileName.value.file_size,
          expiry_date: this.AddFileName.value.expiry_date,
          id: this.getData.id
        }
      }
      else{
        this.value = {
          name: this.AddFileName.value.name,
          file_link: this.AddFileName.value.file_link,
          folder_id: this.childfolderId,
          created_by: this.global.admin.admin_id,
          file_size: this.AddFileName.value.file_size,
          expiry_date: this.AddFileName.value.expiry_date,
        }
      }
      this.docService.createFile(this.value).subscribe(({ status, success, message }) => {
        if (success) {
          status = "Create File"
          this.toast.toastNotification(message, status);
          this.AddFileName.reset();
          this.getAllFiles();
          this.closeFilecanvas();
        }
      })
    }
    else {
      if(this.headingText === 'Update File Details'){
        this.createNew = {
          name: this.AddFileName.value.name,
          file_link: this.AddFileName.value.file_link,
          folder_id: this.folderId,
          created_by: this.global.admin.admin_id,
          file_size: this.AddFileName.value.file_size,
          expiry_date: this.AddFileName.value.expiry_date,
          id: this.getData.id
        }
      }
      else{
        this.createNew = {
          name: this.AddFileName.value.name,
          file_link: this.AddFileName.value.file_link,
          folder_id: this.folderId,
          created_by: this.global.admin.admin_id,
          file_size: this.AddFileName.value.file_size,
          expiry_date: this.AddFileName.value.expiry_date,
        }
      }
      this.docService.createFile(this.createNew).subscribe(({ status, success, message }) => {
        if (success) {
          status = "Create File"
          this.toast.toastNotification(message, status);
          this.AddFileName.reset();
          this.getAllFiles();
          this.closeFilecanvas();
        }
      })
    }
  }

  allFiles;
  allFolders;
  data;
  getAllFiles() {
    if (this.childfolderId) {
      this.data = {
        folder_id: this.childfolderId
      }
    }
    else {
      this.data = {
        folder_id: this.folderId
      }
    }
    this.docService.getAllFiles(this.data).subscribe(({ success, files, folders }) => {
      if (success) {
        this.allFiles = files;
        this.allFolders = folders;
      }
    })
  }

  downloadFile() {
    const downloadLink = document.createElement('a');
    downloadLink.href = this.getData.file_link;
    downloadLink.target = '_blank';
    downloadLink.click();
  }

  deleteFile(file_id) {
    let data = {
      id: file_id
    }
    this.docService.delFiles(data).subscribe(({ success, msg, status }) => {
      if (success) {
        status = "Delete File"
        this.toast.toastNotification(msg, status);
        this.getAllFiles();
        this.closeFilecanvas();
      }
    })
  }

  getFileFormat(fileLink: string): string {
    const parts = fileLink.split('.');
    if (parts.length > 1) {
      return parts[parts.length - 1];
    }
    return 'Unknown';
  }

  openFoldercanvas() {
    this.createFol.nativeElement.classList.add('show');
  }

  closeFoldercanvas() {
    this.createFol.nativeElement.classList.remove('show');
  }

  Save() {
    if (this.selectedfolderId) {
      let data = {
        id: this.selectedfolderId,
        folder: this.AddFolderName.value.name,
        created_by: this.global.admin.admin_id,
        parent_id: this.folderId
      }
      this.docService.editFolder(data).subscribe(({ status, success, message }) => {
        if (success) {
          this.getAllFiles();
          this.closeFoldercanvas();
          status = "Update Folder"
          this.toast.toastNotification(message, status)
        }
      })
    }
    else {
      let data = {
        folder: this.AddFolderName.value.name,
        created_by: this.global.admin.admin_id,
        parent_id: this.folderId
      }

      this.docService.createFolder(data).subscribe(({ status, success, message }) => {
        if (success) {
          this.AddFolderName.reset();
          this.getAllFiles();
          this.closeFoldercanvas();
          status = "Create Folder"
          this.toast.toastNotification(message, status)
        }
      })
    }
  }

  deleteFolder(id) {
    let data = {
      id: id,
    }
    this.docService.deleteFolder(data).subscribe(({ status, success, message }) => {
      if (success) {
        this.getAllFiles();
        status = "Delete Folder"
        this.toast.toastNotification(message, status)
      }
    })
  }

  selectedfolderId
  editFolder(id) {
    this.selectedfolderId = id;
    let selectedFolder = null;
    for (const folder of this.allFolders) {
      if (folder.id === id) {
        selectedFolder = folder;
        break;
      }
    }

    if (selectedFolder) {
      this.AddFolderName.get('name').setValue(selectedFolder.folder)
      this.openFoldercanvas();
    }
    else {
      console.log("Folder not found with ID:", id);
    }
    console.log("Folder id", id, this.allFolders)
  }

}

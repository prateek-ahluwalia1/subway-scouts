import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { DocsService } from 'app/services/docs.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-company-doc',
  templateUrl: './company-doc.component.html',
  styleUrls: ['./company-doc.component.scss']
})
export class CompanyDocComponent implements OnInit {

  @ViewChild('createFolder') createFol: ElementRef;

  AddFolderName: FormGroup;
  UploadDoc: FormGroup;
  subfolder = '';

  constructor(private fb: FormBuilder, private docService: DocsService, private global: GlobalVariable,
    public toast: ToastServiceService,) { }

  ngOnInit(): void {
    this.AddFolderName = this.fb.group({
      name: new FormControl(''),
    })

    this.getAll();

  }

  openFoldercanvas(){
    this.createFol.nativeElement.classList.add('show');
  }
  closeFoldercanvas() {
    this.createFol.nativeElement.classList.remove('show');
  }

  Save(){
    if(this.folderId){
      let data = {
        id: this.folderId,
        folder: this.AddFolderName.value.name,
        created_by: this.global.admin.admin_id,
        parent_id: null,
      }
      this.docService.editFolder(data).subscribe(({ status, success, message }) => {
        if (success) {
          this.getAll();
          this.closeFoldercanvas();
          status = "Update Folder"
          this.toast.toastNotification(message, status)
        }
      }) 
    }
    else{
      let data = {
        folder: this.AddFolderName.value.name,
        created_by: this.global.admin.admin_id,
        parent_id: null,
      }
  
      this.docService.createFolder(data).subscribe(({ status, success, message }) => {
        if (success) {
          this.AddFolderName.reset();
          this.getAll();
          this.closeFoldercanvas();
          status = "Create Folder"
          this.toast.toastNotification(message, status)
        }
      }) 
    }
  }

  all;
  getAll() {
    this.docService.getAllFolders().subscribe(({ success, data }) => {
        if (success) {
          this.all = data;
        }
    })
  }

  folderId;
  editFolder(id){
    this.folderId = id;
    let selectedFolder = null;
    for (const folder of this.all) {
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
    console.log("Folder id", id, this.all)
  }

  deleteFolder(id){
    let data = {
      id: id,
    }
    this.docService.deleteFolder(data).subscribe(({ status, success, message }) => {
      if (success) {
        this.getAll();
        status = "Delete Folder"
        this.toast.toastNotification(message, status)
      }
    })
  }

}

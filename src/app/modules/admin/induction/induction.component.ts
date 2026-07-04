import { HttpHeaders } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ToastServiceService } from 'app/services/toast-service.service';
import { CustomerService } from 'app/services/customer.service';
import { AnnouncementInductionService } from 'app/services/announcement-induction.service';
import { ActivatedRoute, Router } from '@angular/router';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-induction',
  templateUrl: './induction.component.html',
  styleUrls: ['./induction.component.scss']
})
export class InductionComponent implements OnInit {

  options: string[] = ['MCQs', 'True/False', 'Short Question'];
  AddinductionForm : FormGroup;
  formId;
  allInduction
  specificInd
  fileuploaded: boolean = false
  file_name
  uploadFileUrl

  constructor(private fb: FormBuilder, private toast: ToastServiceService, private cusService: CustomerService,
    private service: AnnouncementInductionService, private route: ActivatedRoute, private globals: GlobalVariable,
    private router: Router ) { 
      this.route.params.subscribe(params => {
        this.formId = params['id'];
        this.service.getAllInduction().subscribe(({ success, data }) => {
          if (success) {
            this.allInduction = data;
            this.specificInd = this.allInduction.find(item => item.id == this.formId);
            if (this.specificInd) {
              this.AddinductionForm.get('title').setValue(this.specificInd.title);
              this.specificInd.questionnaire.forEach((question) => {
                this.questionnaire.push(this.fb.group(question));
                if (question.file) {
                  this.fileuploaded = true
                  this.file_name = 'View Uploaded File'
                  this.uploadFileUrl = question.file
                }
              });

              if (this.specificInd.sub_heading && this.specificInd.sub_heading.length) {
                this.specificInd.sub_heading.forEach(sub => {
                  this.subheading.push(new FormControl(sub));
                });
              }
              console.log("Populated subheadings:", this.subheading.value);
              console.log("Found quiz:", this.specificInd);
            } else {
            console.log("Quiz not found");
            }
          }
        })
      });
    }

  ngOnInit(): void {

    this.AddinductionForm = this.fb.group({
      title: new FormControl(''),
      questionnaire: this.fb.array([]),
      id: new FormControl(''),
      admin_id: this.globals.admin.admin_id,
      subheading: this.fb.array([]),
    })
  }

  get questionnaire() {
    return this.AddinductionForm.get('questionnaire') as FormArray;
  }

  get subheading(): FormArray {
    return this.AddinductionForm.get('subheading') as FormArray;
  }

  uploadFile: any;


  onFileChange(event, fieldIndex: number) {
    this.uploadFile = event.target.files[0];
    const myFormData = new FormData();
    const headers = new HttpHeaders({
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    })
    myFormData.append('file', this.uploadFile, this.uploadFile.name);
    myFormData.append('folder', 'induction form')
    this.cusService.uploadImgPdf(myFormData, {
      headers: headers
    }).subscribe(
      response => {
        if (response.success) {
          const fieldArray = this.questionnaire.controls;
            fieldArray[fieldIndex].get('file').setValue(response.url);
        }
        else {
          this.toast.toastNotification1('Something went wrong please check your file size or connection', 'File Upload!')
        }
      },
      (error) => {
        console.error(error);
      }
    );

  }

  Add(){
    this.questionnaire.push(this.fb.group({
      question: [''],
      type: [''],
      optiona: [''],
      optionb: [''],
      optionc: [''],
      optiond: [''],
      answer: [''],
      file: [''],
    }));
  }

  AddSubheading() {
    this.subheading.push(new FormControl(''));
  }

  submitForm(){
    if(this.formId){
      this.AddinductionForm.get('id').setValue(this.formId);
      this.service.addInduction(this.AddinductionForm.value).subscribe(res => {
        if (res.success) {
          let status = 'Induction Operation!'
          this.toast.toastNotification(res.message, status);
          this.router.navigate(['communication', 'induction']);
        }
      })
    }
    else{
      this.service.addInduction(this.AddinductionForm.value).subscribe(res => {
        if (res.success) {
          let status = 'Induction Operation!'
          this.toast.toastNotification(res.message, status)
          this.router.navigate(['communication', 'induction']);
        }
      })
    }
  }

  removeInduction(index){
    console.log("Delete Index", index);
    const fieldArray = this.questionnaire;
    if (index >= 0 && index < fieldArray.length) {
      fieldArray.removeAt(index);
    }
  }

  viewFile(i: number) {
    const fileUrl = this.questionnaire.controls[i].get('file').value;
    if (fileUrl) {
        window.open(fileUrl, '_blank');
    }
}

  handleFileInput(i: number) {
    this.fileuploaded = false;
    this.questionnaire.controls[i].get('file').setValue('');
}


removeSubheading(index: number) {
  this.subheading.removeAt(index);
}



}

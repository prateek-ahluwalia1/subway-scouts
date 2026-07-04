import { Component, ElementRef, Input, OnInit, ViewChild } from '@angular/core';
// import jsPDF from "jspdf";
import html2canvas from 'html2canvas';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormControl } from '@angular/forms';
import { FormBuildService } from 'app/services/form-build.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
@Component({ 
  selector: 'app-refrences-form',
  templateUrl: './refrences-form.component.html',
  styleUrls: ['./refrences-form.component.scss']
})
export class RefrencesFormComponent implements OnInit {

  @ViewChild("content") content: ElementRef;
  @Input() fromParent;

  AddRef
  guardsIds
  option1
  option2
  option3
  option4
  option5
  option6

  active1: number;
  active2: number;
  active3: number;
  active4: number;
  active5: number;
  active6: number;

  options1=[{name:"Yes"},{name:"No"},]
  options2=[{name:"Yes"},{name:"No"},]
  options3=[{name:"Yes"},{name:"No"},]
  options4=[{name:"Yes"},{name:"No"},]
  options5=[{name:"Yes"},{name:"No"},]
  options6=[{name:"Yes"},{name:"No"},]

  describeFormControl: FormControl = new FormControl('');

  constructor(private fb: FormBuilder, private formservice: FormBuildService, private toast: ToastServiceService,
    public global: GlobalVariable, private modalService: NgbModal) {

    this.guardsIds = localStorage.getItem('guardsIds');

   }

  ngOnInit(): void {

    this.getRefrenceForm(this.fromParent);

    this.AddRef = this.fb.group({
      comp_name_one: new FormControl(''),
      contact_per_one: new FormControl(''),
      phone_one: new FormControl(''),
      from_date_one: new FormControl(''),
      to_date_one: new FormControl(''),
      position_one: new FormControl(''),
      duties_one: new FormControl(''),
      comp_name_two: new FormControl(''),
      contact_per_two: new FormControl(''),
      phone_two: new FormControl(''),
      from_date_two: new FormControl(''),
      to_date_two: new FormControl(''),
      position_two: new FormControl(''),
      duties_two: new FormControl(''),
    })

  }

  setcar(index:any){
    this.active1=index;
    this.option1 = this.options1[index].name;
  }

  setcar2(index:any){
    this.active2=index;
    this.option2 = this.options2[index].name;
  }

  setcar3(index:any){
    this.active3=index;
    this.option3 = this.options3[index].name;
  }

  setcar4(index:any){
    this.active4=index;
    this.option4 = this.options4[index].name;
  }

  setcar5(index:any){
    this.active5=index;
    this.option5 = this.options5[index].name;
  }

  setcar6(index:any){
    this.active6=index;
    this.option6 = this.options6[index].name;
  }

  makePdf() {
    // const data = this.content.nativeElement;
    // html2canvas(data).then(canvas => {
    // let fileWidth = 208;
    // let fileHeight = (canvas.height * fileWidth) / canvas.width;
    // const FILEURI = canvas.toDataURL('image/png');
    // let PDF = new jsPDF('p', 'mm', 'a4');
    // let position = 0;
    // PDF.addImage(FILEURI, 'PNG', 0, position, fileWidth, fileHeight);
    // PDF.save('angular-demo.pdf');
    // });
  }

     close(){
    this.modalService.dismissAll();
  }
  submit(){
    const describeValue = this.describeFormControl.value;
    let data = {
      guard_id : this.guardsIds,
      description: describeValue,
      phy_dis: this.option1,
      ner_dis: this.option2,
      bron_dis: this.option3,
      med_cond: this.option4,
      work_inj: this.option5,
      smoke: this.option6,
      work_history: this.AddRef.value,
    }
    this.formservice.StoreReferenceform(data).subscribe(res => {
      if (res.success) {
        this.toast.toastNotification('Form submitted successfully:', 'Form Stored!')
      }
    })
    // console.log("Form data", data);
  }

  getRefrenceForm(id){
    let data = {
      guard_id: id,
    }
    this.formservice.GetReferenceform(data).subscribe(({ success, data}) => {
      if (success) {
        this.describeFormControl.setValue(data.description);
        this.option1 = data.phy_dis;
        this.option2 = data.ner_dis;
        this.option3 = data.bron_dis;
        this.option4 = data.med_cond;
        this.option5 = data.work_inj;
        this.option6 = data.smoke;
        this.AddRef.patchValue({
          comp_name_one: data.comp_name_one,
          contact_per_one: data.contact_per_one,
          phone_one: data.comp_name_one,
          from_date_one: data.contact_per_one,
          to_date_one: data.comp_name_one,
          position_one: data.contact_per_one,
          duties_one: data.comp_name_one,
          comp_name_two: data.contact_per_one,
          contact_per_two: data.contact_per_one,
          phone_two: data.comp_name_one,
          from_date_two: data.contact_per_one,
          to_date_two: data.comp_name_one,
          position_two: data.contact_per_one,
          duties_two: data.comp_name_one,
        });
        this.active1 = this.options1.findIndex(option => option.name === data.phy_dis);
        this.active2 = this.options2.findIndex(option => option.name === data.ner_dis);
        this.active3 = this.options3.findIndex(option => option.name === data.bron_dis);
        this.active4 = this.options4.findIndex(option => option.name === data.med_cond);
        this.active5 = this.options5.findIndex(option => option.name === data.work_inj);
        this.active6 = this.options6.findIndex(option => option.name === data.smoke);
      }
    })
  }
}

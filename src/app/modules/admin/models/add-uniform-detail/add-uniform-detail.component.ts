import { GlobalVariable } from "./../../../../shared/global";
import { Component, Input, OnInit, ViewChild } from "@angular/core";
import { FormArray, FormBuilder, NgForm } from "@angular/forms";
import { NgbActiveModal } from "@ng-bootstrap/ng-bootstrap";
import { CustomerService } from "app/services/customer.service";
import { SiteService } from "app/services/site.service";
import { StaffService } from "app/services/staff.service";
import { ToastServiceService } from "app/services/toast-service.service";
import { FormControl, FormGroup } from "@angular/forms";

export class Customers {
  id: number;
  name: string;
}

@Component({
  selector: "app-add-uniform-detail",
  templateUrl: "./add-uniform-detail.component.html",
  styleUrls: ["./add-uniform-detail.component.scss"],
})
export class AddUniformDetailComponent implements OnInit {
  @ViewChild("uniformForm", { static: false }) form: NgForm;
  // formValue: FormGroup = new FormGroup({});
  formValue;
  @Input() fromUserMenu;
  @Input() documentList;
  @Input() uniformId;
  @Input() guardDocumentType;
  customer_id;
  customers: Customers[] = [];
  dataFromChild: any = [];
  ids: any = [];
  UniformDetail: any;
  note;
  Uniform;

  optionArray: string[] = ["Dress Shirt", "Slacks", "Security Tag", "Vest", "T Shirt", "Polo", "Jacket", "Tie", "Shoes"]
  uniformSize: string[] = ["S", "M", "L", "XL", "XXL", "XXXL"]
  uniQuantity: string[] = ["1", "2", "3", "4", "5", "6"]
  shoesSize: string[] = ["36", "38", "40", "42", "44", "46", "48", "50", "52", "54", "56"]
  Adduniform: FormGroup;
  Updateuniform: FormGroup
  // uniformData = [
  //   {
  //     enable: false,
  //     uniformName: "Dress Shirt",
  //     uniformSize: ["S", "M", "L", "XL", "XXL", "XXXL"],
  //     quantity: ["One", "Two", "Three", "Four", "Five", "Six"],
  //     uniformValue: { size: "", quantity: "", enable: false, return_status: false },
  //     return_status: false
  //   },
  //   {
  //     enable: false,
  //     uniformName: "Slacks",
  //     uniformSize: ["S", "M", "L", "XL", "XXL", "XXXL"],
  //     quantity: ["One", "Two", "Three", "Four", "Five", "Six"],
  //     uniformValue: { size: "", quantity: "", enable: false, return_status: false },
  //     return_status: false

  //   },
  //   {
  //     enable: false,
  //     uniformName: "Security Tag",
  //     uniformSize: ["S", "M", "L", "XL", "XXL", "XXXL"],
  //     quantity: ["One", "Two", "Three", "Four", "Five", "Six"],
  //     uniformValue: { size: "", quantity: "", enable: false, return_status: false },
  //     return_status: false

  //   },
  //   {
  //     enable: false,
  //     uniformName: "Vest",
  //     uniformSize: ["S", "M", "L", "XL", "XXL", "XXXL"],
  //     quantity: ["One", "Two", "Three", "Four", "Five", "Six"],
  //     uniformValue: { size: "", quantity: "", enable: false, return_status: false },
  //     return_status: false

  //   },
  //   {
  //     enable: false,
  //     uniformName: "T Shirt",
  //     uniformSize: ["S", "M", "L", "XL", "XXL", "XXXL"],
  //     quantity: ["One", "Two", "Three", "Four", "Five", "Six"],
  //     uniformValue: { size: "", quantity: "", enable: false, return_status: false },
  //     return_status: false

  //   },
  //   {
  //     enable: false,
  //     uniformName: "Polo",
  //     uniformSize: ["S", "M", "L", "XL", "XXL", "XXXL"],
  //     quantity: ["One", "Two", "Three", "Four", "Five", "Six"],
  //     uniformValue: { size: "", quantity: "", enable: false, return_status: false },
  //     return_status: false

  //   },
  //   {
  //     enable: false,
  //     uniformName: "Jacket",
  //     uniformSize: ["S", "M", "L", "XL", "XXL", "XXXL"],
  //     quantity: ["One", "Two", "Three", "Four", "Five", "Six"],
  //     uniformValue: { size: "", quantity: "", enable: false, return_status: false },
  //     return_status: false

  //   },
  //   {
  //     enable: false,
  //     uniformName: "Tie",
  //     uniformSize: ["S", "M", "L", "XL", "XXL", "XXXL"],
  //     quantity: ["One", "Two", "Three", "Four", "Five", "Six"],
  //     uniformValue: { size: "", quantity: "", enable: false, return_status: false },
  //     return_status: false

  //   },
  //   {
  //     enable: false,
  //     uniformName: "Shoes",
  //     uniformSize: [
  //       "36",
  //       "38",
  //       "40",
  //       "42",
  //       "44",
  //       "46",
  //       "48",
  //       "50",
  //       "52",
  //       "54",
  //       "56",
  //     ],
  //     quantity: ["One"],
  //     uniformValue: { size: "", quantity: "", enable: false, return_status: false },
  //     return_status: false

  //   },
  // ];

  constructor(
    private toast: ToastServiceService,
    private cutomerService: CustomerService,
    public cus: SiteService,
    public userService: StaffService,
    public ngbActiveModal: NgbActiveModal,
    public global: GlobalVariable,
    private fb: FormBuilder,
  ) {
    this.getCustomers();
  }

  ngOnInit(): void {

    //   this.global.selectedCustomers = []
    //   this.global.selectedCustomer = []
    //   this.getCustomers();

    // if (this.uniformId) {
    //   this.userService.getUniformDetail(this.uniformId).subscribe(
    //     (res) => {
    //       if (res.success == true) {
    //         this.UniformDetail = res.data;
    //         this.global.selectedCustomers = this.UniformDetail.customer_id;
    //         this.note = this.UniformDetail.note;
    //         this.uniformData = this.uniformData.map(uniform => {
    //           const apiUniform = this.UniformDetail.uniform_type.find(apiUniform => apiUniform.uniformName === uniform.uniformName);
    //           if (apiUniform) {
    //             uniform.enable = apiUniform.enable;
    //             uniform.return_status = apiUniform.return_status;
    //             uniform.uniformValue.size = apiUniform.uniformSize;
    //             uniform.uniformValue.quantity = apiUniform.quantity;
    //           }
    //           return uniform;
    //         });
    //       }
    //     },
    //     (error) => { }
    //   );
    // }

    //   if (this.UniformDetail) {
    //     this.UniformDetail.uniform_type.forEach((element) => {
    //       this.uniformData.forEach((element2) => {
    //         if (element.uniformName == element2.uniformName) {
    //           console.log('found', element)
    //         }
    //       });
    //     });
    //   }
    this.Adduniform = this.fb.group({
      guard_id: this.fromUserMenu.id,
      admin_id: this.global.admin.admin_id,
      uniformDetail: this.fb.array([]),
    })


    

  }

  ngAfterViewInit() {
    if (this.uniformId) {
      this.userService.getUniformDetail(this.uniformId).subscribe(
        ({ success, data, message }) => {
          if (success) {
            console.log("Uniform", this.Uniform)
            this.global.selectedCustomer = data?.customer_id.id
            
            this.uniformDetail.push(this.fb.group({
              customer_id: [data?.customer_id.id],
              uniform_type: [data.uniform_type],
              size: [data.size],
              quantity: [data.quantity.toString()],
              return_status: [data.return_status],
              note: [data.note]
            }));
          }
          else {
            this.toast.toastNotification1(message, 'Uniform')
          }
        },(error=>{
          this.toast.toastNotification1('Something went wrong. Please contact with support team','Error')
        }))
    }
  }

  getCustomers() {
    this.cutomerService.getCust().subscribe(
      (res) => {
        if (res.success == true) {
          this.customers = res.data;
        }
      },
      (error) => { }
    );
  }

  // receiveDataFromChild(data: string) {
  //   this.dataFromChild = data;
  //   this.ids = this.dataFromChild.value.map((item) => item.id);
  // }

  // addUniform() {
  //   this.formValue = [...this.uniformData];
  //   console.log(" form value", this.formValue);

  //   const selectedUniforms = this.formValue
  //     .filter((data) => data.enable)
  //     .map((data) => ({
  //       uniformName: data.uniformName,
  //       uniformSize: data.uniformValue.size,
  //       quantity: data.uniformValue.quantity,
  //       enable: data.enable,
  //       return_status: data.return_status
  //     }));

  //   console.log("selectedUniforms", selectedUniforms);

  //   if (this.uniformId) {
  //     console.log("UniformId", this.uniformId);
  //     let data = {
  //       uniform_id: this.uniformId,
  //       uniform_type: selectedUniforms,
  //       admin_id: this.global.admin.admin_id,
  //       guard_id: this.fromUserMenu.id,
  //       customer_id: this.ids,
  //       note: this.note
  //     }
  //     console.log("UniformId Note", data)
  //     this.userService.adduniform(data)
  //       .subscribe(
  //         (res) => {
  //           console.log(res);
  //           if (res.success == true) {
  //             let status = 'Staff Operation';
  //             this.toast.toastNotification(res.message, status);
  //             this.close('uniform');
  //           }
  //         },
  //         (error) => {
  //           console.log(error);
  //         }
  //       );
  //   }
  //   else {
  //     let data = {
  //       uniform_type: selectedUniforms,
  //       admin_id: this.global.admin.admin_id,
  //       guard_id: this.fromUserMenu.id,
  //       customer_id: this.ids,
  //       note: this.note
  //     }
  //     console.log("Note", data)
  //     this.userService
  //       .adduniform(data)
  //       .subscribe(
  //         (res) => {
  //           console.log(res);
  //           if (res.success == true) {
  //             let status = 'Staff Operation';
  //             this.toast.toastNotification(res.message, status);
  //             this.close('uniform');
  //           }
  //         },
  //         (error) => {
  //           console.log(error);
  //         }
  //       );
  //   }
  // }


  close(data?) {
    this.ngbActiveModal.dismiss(data);
  }

  customerSignleId: any = []
  id;
  receiveDataFromChildSingle(data: string, index: number) {
    this.customerSignleId = data;
    this.id = this.customerSignleId.value.id;
    const formGroup = this.uniformDetail.at(index) as FormGroup;
    formGroup.get('customer_id').setValue(this.id);
    console.log("Select Customer", this.id)
  }

  get uniformDetail() {
    return this.Adduniform.get('uniformDetail') as FormArray;
  }

  Add() {
    this.uniformDetail.push(this.fb.group({
      customer_id: [''],
      uniform_type: [''],
      size: [''],
      quantity: [''],
      return_status: [false],
      note: ['']
    }));
  }

  submitForm() {
    if(this.uniformId){
      this.Adduniform.addControl('id', this.fb.control(this.uniformId));
      const topLevelObject = this.Adduniform.value;
      const uniformDetailObject = topLevelObject.uniformDetail[0];
      
      const mergedObject = {
        guard_id: topLevelObject.guard_id,
        admin_id: topLevelObject.admin_id,
        id: topLevelObject.id,
        customer_id: uniformDetailObject.customer_id,
        note: uniformDetailObject.note,
        quantity: uniformDetailObject.quantity,
        return_status: uniformDetailObject.return_status,
        size: uniformDetailObject.size,
        uniform_type: uniformDetailObject.uniform_type,
      };
      
      console.log("Merged Object", mergedObject);

      this.userService.updateuniform(mergedObject).subscribe(
        (res) => {
          console.log(res);
          if (res.success == true) {
            let status = 'Staff Operation';
            this.toast.toastNotification(res.message, status);
            this.close('uniform');
          }
        },
        (error) => {
          console.log(error);
        }
      );
    }
    else{
      this.userService.adduniform(this.Adduniform.value).subscribe(
        (res) => {
          console.log(res);
          if (res.success == true) {
            let status = 'Staff Operation';
            this.toast.toastNotification(res.message, status);
            this.close('uniform');
          }
        },
        (error) => {
          console.log(error);
        }
      );
    }
  }

  removeUniform(index) {
    console.log("Delete Index", index);
    const fieldArray = this.uniformDetail;
    if (index >= 0 && index < fieldArray.length) {
      fieldArray.removeAt(index);
    }
  }

  updateForm() {

  }


}


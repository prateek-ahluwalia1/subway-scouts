import { Injectable } from "@angular/core";
import { BehaviorSubject, Observable } from "rxjs";

@Injectable({
  providedIn: "root",
})
export class SharedDataService {
  public formData: any = {};
  public adminData: any = {};
  public customerData: any = {};
  private invoicePreview: BehaviorSubject<any> = new BehaviorSubject(null);

  constructor() {}

  // Shared Service
  private dataSubject = new BehaviorSubject<any>(null);
  data$ = this.dataSubject.asObservable();

  setFormData(data: any) {
    this.dataSubject.next(data);
  }

  get invoicePreview$(): Observable<any> {
    return this.invoicePreview.asObservable();
  }

}

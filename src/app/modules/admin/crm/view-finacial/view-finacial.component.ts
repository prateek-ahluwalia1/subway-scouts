import { Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { DialogBelonging } from '@costlydeveloper/ngx-awesome-popup';
import { AgentService } from 'app/services/crm/agent.service';
import { Subscription } from 'rxjs';
import * as html2pdf from 'html2pdf.js';
interface Row {
  productName: string;
  description: string;
  quantity: number;
  listPrice: any;
  amount: string;
  totalAmount: string;
  error: string;
}
@Component({
  selector: 'app-view-finacial',
  templateUrl: './view-finacial.component.html',
  styleUrls: ['../crm-client/create-quotation.component.scss']
})
export class ViewFinacialComponent implements OnInit, OnDestroy {

  id
  user: any = [];
  company_detail: any
  subTotal: number = 0;
  grandTotal: number = 0;
  discountValue: any = '0.0';
  rows: Row[] = [this.generateTableRow()]; // Initialize with a single row
  showDiscountInput: boolean = false;
  showGstInput = false;
  additionalNotesText: string = 'A finance charge of 1.5% will be made on unpaid balances after 30 days.';
  gstAmount: number;

  private subscriptions: Subscription = new Subscription();
  constructor(@Inject('dialogBelonging') public dialogBelonging: DialogBelonging, private service: AgentService,) { }

  ngOnInit(): void {
    const comp_detail = JSON.parse(localStorage.getItem('admin'))
    this.company_detail = comp_detail.apiKeys
    console.log(this.dialogBelonging);
    const id = this.dialogBelonging.customData.id
    if (id) {
      this.getCustomer(id)
    }
    // setTimeout(() => {
    //   this.dialogBelonging.eventsController.closeLoader();
    // }, 1500);

    this.subscriptions.add(

      this.dialogBelonging.eventsController.onButtonClick$.subscribe((_Button) => {
        if (_Button.ID === 'close') {
          this.dialogBelonging.eventsController.close();
        }
        else if (_Button.ID === 'pdf') {
          const element = document.getElementById('pdf-content');
          const options = {
            filename: `financial.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
          };
          html2pdf().from(element).set(options).save();
        }

      })
    );

  }


  getCustomer(id: number): void {
    this.service.editQuotation(id)
      .subscribe(
        (quote) => {
          if (quote.success) {
            this.onCustomerRetrieved(quote.data);
          }
        },
        // (error: any) => this.errorMessage = <any>error
      );
  }

  onCustomerRetrieved(quote): void {

    console.log(quote);
    this.dialogBelonging.eventsController.closeLoader();

    this.user = quote;
    if (this.user.discount) {
      this.showDiscountInput = true
      this.discountValue = this.user.discount
    }
    this.rows = this.user?.quote
    if (this.user.showGstInput) {
      this.showGstInput = true
      this.gstAmount = this.user.showGstInput
    }
    this.subTotal = this.user.sub_total
    this.grandTotal = this.user.grd_total
    this.additionalNotesText = this.user.add_notes

    // this.cdr.markForCheck()
  }
  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  generateTableRow(): Row {
    return {
      productName: '',
      description: '',
      quantity: null,
      listPrice: '0.00',
      amount: '0.00',
      totalAmount: '0.00',
      error: ''
    };
  }

  getCurrencySign(currency: string): string {
    switch (currency) {
      case 'AUD':
        return '$';
      case 'USD':
        return '$';
      case 'EUR':
        return '€';
      case 'GBP':
        return '£';
      case 'NZD':
        return '$';
      case 'AED':
        return 'AED';
      default:
        return ''; // Handle other cases or provide a default currency sign
    }
  }
}

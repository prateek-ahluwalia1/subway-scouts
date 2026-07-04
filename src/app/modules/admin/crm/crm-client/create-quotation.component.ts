import { Component, OnInit, OnDestroy, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { AbstractControl, FormBuilder, FormGroup, RequiredValidator, ValidatorFn, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { AgentService } from 'app/services/crm/agent.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import * as html2pdf from 'html2pdf.js';
import { EmailModalComponent } from './email-modal.component';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
interface Row {
    productName: string;
    description: string;
    quantity: number;
    listPrice: any;
    amount: string;
    totalAmount: string;
    error: string;
}

export function abnValidator(): ValidatorFn {
    return (control: AbstractControl): { [key: string]: any } | null => {
        if (control.value) {
            const valid = /^\d{11}$/.test(control.value);
            return valid ? null : { invalidAbn: true };
        }
        return null;
    };
}
@Component({
    selector: 'quotation-list',
    templateUrl: './create-quotation.component.html',
    styleUrls: ['./create-quotation.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush

})
export class CreateQuotationComponent implements OnInit, OnDestroy {
    contacted: any = []
    company_detail: any
    // filteredOptions: Observable<string[]>;
    customerForm: FormGroup
    showPdf: boolean = false
    user: any = [];
    additionalNotesText: string = 'A finance charge of 1.5% will be made on unpaid balances after 30 days.';
    errorMessage: string;
    backUrl: string = '/crm/quotation';
    typeSelected: string = 'line-spin-fade-rotating';

    subTotal: number = 0;
    grandTotal: number = 0;
    discountValue: any = '0.0';
    rows: Row[] = [this.generateTableRow()]; // Initialize with a single row
    showDiscountInput: boolean = false;
    showGstInput = false;
    lead_object
    gstAmount: number;
    submitted: boolean = false;
    selectedValue: string = 'Quote';
    disableContact: boolean = false
    constructor(private fb: FormBuilder, private global: GlobalVariable,
        private route: ActivatedRoute, private toast: ToastServiceService,
        private service: AgentService, private router: Router, private modalService: NgbModal,
        private spinnerService: NgxSpinnerService,
        private cdr: ChangeDetectorRef
    ) {
        this.global.showCrmTab = true;
    }
    ngOnInit(): void {
        const comp_detail = JSON.parse(localStorage.getItem('admin'))
        this.company_detail = comp_detail.apiKeys
        this.customerForm = this.fb.group({
            id: [''],
            qut_owner: [''],
            deal_name: [''],
            subject: [''],
            valid: ['', Validators.required],
            account_name: [''],
            team: [''],
            bill_country: [''],
            bill_code: [''],
            bill_state: [''],
            bill_city: [''],
            bill_street: [''],
            quote: [''],
            financial_type: ['quote', Validators.required],
            currency: ['', Validators.required],
            abn: [''],
            contacted_id: [''],
            account_number: [this.company_detail?.account_number],
            bsb: [this.company_detail?.bsb],
            bank_name: [this.company_detail?.bank_name]
        });

        this.customerForm.get('contacted_id').valueChanges.subscribe(contactedId => {
            if (contactedId) {
                // Set validators for bill_street, bill_city, and bill_country
                this.customerForm.get('bill_city').setValidators([Validators.required]);
                this.customerForm.get('bill_country').setValidators([Validators.required]);
            } else {
                // Clear validators for bill_street, bill_city, and bill_country
                this.customerForm.get('bill_city').clearValidators();
                this.customerForm.get('bill_country').clearValidators();
            }

            // Update the validity of the form controls
            this.customerForm.get('bill_city').updateValueAndValidity();
            this.customerForm.get('bill_country').updateValueAndValidity();
        });


        this.customerForm.get('financial_type').valueChanges.subscribe(value => {
            const abnControl = this.customerForm.get('abn');
            const account_numberControl = this.customerForm.get('account_number');
            const bsbControl = this.customerForm.get('bsb');
            const bank_nameControl = this.customerForm.get('bank_name');
            if (value === 'invoice') {
                abnControl.setValidators([Validators.required, abnValidator()]);
                account_numberControl.setValidators(Validators.required);
                bsbControl.setValidators(Validators.required);
                bank_nameControl.setValidators(Validators.required);
            } else {
                abnControl.clearValidators(); // Clear validators if condition is false
                account_numberControl.clearValidators(); // Clear validators if condition is false
                bsbControl.clearValidators(); // Clear validators if condition is false
                bank_nameControl.clearValidators(); // Clear validators if condition is false
            }
            abnControl.updateValueAndValidity(); // Update validation status
        });

        this.customerForm.get('qut_owner').setValue(this.global.admin.admin_name)

        this.getAllLead()

        this.route.params.subscribe(
            params => {
                let id = +params['id'];
                let contact = +params['contact'];
                console.log(contact);

                if (id) {
                    setTimeout(() => {
                        this.getCustomer(id);
                    }, 100);
                }
                if (contact) {
                    this.customerForm.get('contacted_id').setValue(contact)

                }
            }
        );

        this.cdr.markForCheck()
    }

    ngOnDestroy(): void {
        this.global.showCrmTab = false;
    }
    get f(): { [key: string]: AbstractControl } {
        return this.customerForm.controls;
    }

    isNameEmpty(row: any) {
        return !row.productName;
    }

    saveCustomer(): void {
        for (const row of this.rows) {
            if (this.isNameEmpty(row)) {
                row.error = 'Name is required';
                return
            } else {
                row.error = '';
            }
        }
        this.spinnerService.show()
        this.submitted = true
        let isValid = true;
        this.markFormControlsAsTouched(this.customerForm);
        // if (this.customerForm.invalid) {
        //     this.spinnerService.hide()
        //     return;
        // }
        this.customerForm.value.add_notes = this.additionalNotesText;
        this.customerForm.value.sub_total = this.subTotal;
        this.customerForm.value.grd_total = this.grandTotal;
        this.customerForm.value.discount = this.discountValue;
        if (this.showGstInput) {
            this.customerForm.value.showGstInput = this.gstAmount;
        }
        else {
            this.customerForm.value.showGstInput = '';
        }
        this.customerForm.value.quote = this.rows
        // this.rows.forEach(element => {
        //     if (!element.productName) {
        //         this.toast.toastNotification1('Quoted Items Product name is required in every field', 'Invalid Form!');
        //         this.spinnerService.hide()
        //         isValid = false;
        //     }
        // });
        if (!isValid || this.customerForm.invalid) {
            this.spinnerService.hide()
            return;
        }

        this.customerForm.value.admin_id = this.global.admin.admin_id
        this.customerForm.value.qut_owner = this.global.admin?.admin_name

        this.service.saveQuotation(this.customerForm.value)
            .subscribe(
                (response: any) => {
                    if (response.success) {
                        this.customerForm.reset();
                        this.router.navigate([this.backUrl]);
                        this.toast.toastNotification(response.msg, 'Financials Operation!')
                    }
                    this.spinnerService.hide()

                },
                (error: any) => this.errorMessage = <any>error
            );
    }

    markFormControlsAsTouched(formGroup: FormGroup) {
        Object.values(formGroup.controls).forEach((control) => control.markAsTouched());
    }


    onAdditionalNotesInput(event: any) {
        this.additionalNotesText = event.target.innerText;
    }


    onSaveComplete(): void {
        if (this.customerForm.value.contacted_id) {
            const newConfirmBox = new ConfirmBoxInitializer();
            let layoutType = DialogLayoutDisplay.SUCCESS
            let buttons: ButtonMaker[] = [];
            newConfirmBox.setTitle('Confirm Action');
            newConfirmBox.setMessage('Do you want to send this to customer?');
            buttons = [
                new ButtonMaker('Not Yet!', 'Not', ButtonLayoutDisplay.DANGER),
                new ButtonMaker('Yes', 'Yes', ButtonLayoutDisplay.SUCCESS)
            ];

            newConfirmBox.setConfig({
                layoutType,
                animationIn: AppearanceAnimation.ZOOM_IN_ROTATE,
                animationOut: DisappearanceAnimation.ZOOM_OUT_WIND,
                allowHtmlMessage: true,
                buttonPosition: 'center',
            });
            newConfirmBox.setButtons(buttons);

            newConfirmBox.openConfirmBox$().subscribe(resp => {
                if (resp.clickedButtonID == 'Yes') {
                    this.openModal()
                }
                this.cdr.markForCheck()
            });
        }
        else {
            this.customerForm.reset();
            this.router.navigate([this.backUrl]);
        }
        this.cdr.markForCheck()
    }

    getCustomer(id: number): void {
        this.service.editQuotation(id)
            .subscribe(
                (quote) => {
                    if (quote.success) {
                        this.onCustomerRetrieved(quote.data);
                    }
                },
                (error: any) => this.errorMessage = <any>error
            );
    }

    onCustomerRetrieved(quote): void {
        if (this.customerForm) {
            this.customerForm.reset();
        }

        this.user = quote;
        console.log(this.company_detail);
        
        this.customerForm.patchValue({
            id: this.user?.id,
            qut_owner: this.user.qut_owner,
            deal_name: this.user.deal_name,
            subject: this.user.subject,
            valid: this.user.valid,
            account_name: this.user.account_name,
            contacted_id: this.user.contacted_id,
            team: this.user.team,
            bill_country: this.user.bill_country,
            bill_code: this.user.bill_code,
            bill_state: this.user.bill_state,
            bill_city: this.user.bill_city,
            bill_street: this.user.bill_street,
            financial_type: this.user.financial_type,
            currency: this.user.currency,
            abn: parseInt(this.user.abn, 10),
            account_number: this.company_detail?.account_number,
            bsb: this.company_detail?.bsb,
            bank_name: this.company_detail?.bank_name
        });
        if (this.user.contacted_id) {
            this.disableContact = true
        }
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

        this.cdr.markForCheck()
    }

    parsePrice(number: number) {
        return number.toFixed(2).replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1,');
    }

    calculateTotalAmount(row: any): string {
        const amount = this.parseFloatHTML(row.listPrice) * this.parseFloatHTML(row.quantity);
        const discount = this.parseFloatHTML(row.discount);
        const tax = this.parseFloatHTML(row.tax);
        const totalAmount = amount - discount + tax;
        return this.parsePrice(totalAmount);
    }

    updateFormattedValue(row: any) {
        row.listPrice = parseFloat(row.listPrice.toFixed(2));
    }

    // append new item row
    onAddRowClick(): void {
        const newRow = this.generateTableRow();
        this.rows.push(newRow);
        this.calculateSubTotalAndGrandTotal();
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

    onDeleteRowClick(rowToDelete: Row): void {
        const indexToDelete = this.rows.indexOf(rowToDelete);
        if (indexToDelete !== -1) {
            this.rows.splice(indexToDelete, 1);
            this.calculateSubTotalAndGrandTotal();
        }
    }

    parseFloatHTML(element: string): number {
        return parseFloat(element.replace(/[^\d.\-]+/g, '')) || 0;
    }

    updateInvoice(): void {
        let total = 0;
        let discount = this.discountValue;

        for (const row of this.rows) {
            const price = row.listPrice * row.quantity;
            row.amount = price.toFixed(2);
            total += price;
        }

        this.subTotal = total - discount;
        this.calculateSubTotalAndGrandTotal();

        const balanceCells = [
            this.parsePrice(discount),
            this.parsePrice(this.subTotal),
            this.parsePrice(this.grandTotal)
        ];

        const prefix = '$';
        const prefixElements = document.querySelectorAll('[data-prefix]');
        for (const prefixElement of Array.from(prefixElements)) {
            prefixElement.textContent = prefix;
        }

        const priceElements = document.querySelectorAll('span[data-prefix] + span');
        for (const priceElement of Array.from(priceElements)) {
            if (document.activeElement !== priceElement) {
                const priceValue = this.parseFloatHTML(priceElement.textContent);
                priceElement.textContent = this.parsePrice(priceValue);
            }
        }
    }


    calculateAmount(row: Row): void {
        row.amount = (row.listPrice * row.quantity).toFixed(2);
        row.totalAmount = row.amount;
        this.calculateSubTotalAndGrandTotal();
    }



    displayContactName(option: any): string {
        return option ? option.name : '';
    }
    onDiscountCheckboxChange() {
        if (!this.showDiscountInput) {
            this.discountValue = 0; // Reset discount value to zero
        }
        this.calculateSubTotalAndGrandTotal();
    }

    onDiscountChange(): void {
        this.calculateSubTotalAndGrandTotal();
    }


    onGstChange(): void {
        this.calculateSubTotalAndGrandTotal();
    }


    calculateSubTotalAndGrandTotal(): void {
        const totalAmountWithoutDiscount = this.rows.reduce((total, row) => total + this.parseFloatHTML(row.totalAmount), 0);
        // Calculate total amount after discount
        const totalAmountAfterDiscount = totalAmountWithoutDiscount - this.discountValue;
        if (this.showGstInput) {
            const gstPercentage = 10; // GST percentage (10%)
            this.gstAmount = (totalAmountAfterDiscount * gstPercentage / 100);
            this.grandTotal = totalAmountAfterDiscount + this.gstAmount;
        } else {
            this.grandTotal = totalAmountAfterDiscount;
        }
        // Subtotal remains unchanged
        this.subTotal = totalAmountAfterDiscount;
    }

    getAddress(event) {
        const filteredItem = this.contacted.find(item => item.id === event.value);
        this.customerForm.patchValue({
            bill_country: filteredItem.country,
            bill_code: filteredItem.zip_code,
            bill_state: filteredItem.state,
            bill_city: filteredItem.city,
            bill_street: filteredItem.street,
            abn: filteredItem.abn,
            contacted_id: filteredItem.id,
        });
        this.cdr.markForCheck()
    }

    saveAsPDF(): void {
        this.markFormControlsAsTouched(this.customerForm);
        if (this.customerForm.invalid) {
            return;
        }
        this.showPdf = true
        this.spinnerService.show();
        setTimeout(() => {
            const element = document.getElementById('pdf-content');
            const options = {
                filename: `${this.customerForm.value.financial_type}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().from(element).set(options).save();
            this.showPdf = false
            this.spinnerService.hide();
            this.onSaveComplete()
        }, 2000);
    }

    openModal() {
        const filteredItem = this.contacted.find(item => item.id === this.customerForm.value.contacted_id);
        if (filteredItem) {
            const modalRef = this.modalService.open(EmailModalComponent, {
                backdrop: 'static',
                windowClass: "modal-right-email-lead",
            });
            modalRef.componentInstance.data = {
                ...filteredItem,
                email: filteredItem.email,
            };
            this.cdr.markForCheck()
            modalRef.result.then((result) => {
                this.router.navigate([this.backUrl]);
            }).catch((reason) => {
                this.router.navigate([this.backUrl]);
            });
        }
    }

    getLead() {
        this.customerForm.get('qut_owner').setValue(this.global.admin.admin_name)
        this.service.getContactList('contacted').subscribe(({ success, data }) => {
            if (success) {
                this.contacted = data
                this.cdr.markForCheck()

            }
        })
    }

    getAllLead() {
        this.service.getList('customer').subscribe(({ success, data }) => {
            if (success) {
                this.contacted = data
                console.log(this.contacted);
                if (this.customerForm.value.contacted_id) {
                    const filteredItem = this.contacted.find(item => item.id === this.customerForm.value.contacted_id);
                    this.customerForm.patchValue({
                        bill_country: filteredItem.country,
                        bill_code: filteredItem.zip_code,
                        bill_state: filteredItem.state,
                        bill_city: filteredItem.city,
                        bill_street: filteredItem.street,
                        abn: filteredItem.abn,
                        contacted_id: filteredItem.id,
                    });
                }

                this.cdr.markForCheck()
            }
        })
    }

    onSelectionChange(value: string) {
        this.selectedValue = this.capitalizeFirstLetter(value);
    }

    capitalizeFirstLetter(value: string): string {
        return value.charAt(0).toUpperCase() + value.slice(1);
    }

    onDiscountKeyDown(event: KeyboardEvent): void {
        if (event.key === '-' || event.key === '+') {
            event.preventDefault();
        }
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
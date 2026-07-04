import { Component, OnInit, AfterViewInit, OnDestroy, ViewChildren, ElementRef, TemplateRef, ViewChild, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormControlName, FormControl } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Breakpoints, BreakpointObserver } from '@angular/cdk/layout';
import { GenericValidator } from 'app/shared/generic-validator';
import { Subscription } from 'rxjs';
import { Observable, fromEvent, merge } from 'rxjs';
import { debounceTime, map, startWith } from 'rxjs/operators';
import { AgentService } from 'app/services/crm/agent.service';
import { ServiceService } from 'app/services/service.service';
import { GlobalVariable } from 'app/shared/global';
import { ToastServiceService } from 'app/services/toast-service.service';
import { HttpHeaders } from '@angular/common/http';
import { StaffService } from 'app/services/staff.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import moment from 'moment';
import { DateAdapter } from '@angular/material/core';
import { CustomerService } from 'app/services/customer.service';
import { MatAutocompleteSelectedEvent } from '@angular/material/autocomplete';

export interface State {
    color: string;
    name: string;
}

@Component({
    selector: 'customer-form',
    templateUrl: './customer-form.component.html',
    styleUrls: ['./customer-form.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class CustomerFormComponent implements OnInit, AfterViewInit, OnDestroy {
    @ViewChildren(FormControlName, { read: ElementRef }) formInputElements: ElementRef[];

    pageTitle: string = '';
    errorMessage: string;
    customerForm: FormGroup;
    user: any = [];
    private sub: Subscription;
    imageWidth: number = 100;
    imageMargin: number = 2;
    fieldColspan = 3;

    // Use with the generic validation message class
    displayMessage: { [key: string]: string } = {};
    private genericValidator: GenericValidator;

    private validationMessages: { [key: string]: { [key: string]: string } | {} } = {
        name: {
            required: 'Name is required.',
            minlength: 'Name must be at least five characters.',
            maxlength: 'Name cannot exceed 100 characters.'
        },
        company: {
            required: 'Company name is required.',
            minlength: 'Company name must be at least one characters.',
            maxlength: 'Company name cannot exceed 100 characters.'
        },
        sub_company: {
            required: 'Sub Company name is required.',
            minlength: 'Sub Company name must be at least one characters.',
            maxlength: 'Sub Company name cannot exceed 100 characters.'
        },
        email: {
            required: 'Email is required.',
            minlength: 'Email must be at least one characters.',
            maxlength: 'Email cannot exceed 200 characters.',
            email: 'Please enter a valid email address.'
        },
        saleperson_id: {
            required: 'Agent is required.',
        },
        lead_status: {
            required: 'Status is required.',
        },
        city: {
            required: 'City is required.',
        },
        state: {
            required: 'State is required.',
        },
        country: {
            required: 'Country is required.',
        },
        postal_code: {
            required: 'Zip-code is required.',
        },
    };
    backUrl: string;
    extractedUrl: string;
    url: string;
    type
    salePerson: any[] = []
    admins: any[] = []
    // here code for dropdown search
    options: string[] = ['Existing Client', 'New Client', 'Advertisement', 'Cold Call', 'Employee Referral',
        'External Referral', 'Online Store', 'Partner', 'Public Relations'
        , 'Sales Email Alias', 'Seminar Partner', 'Internal Partner', 'Trade Show'
        , 'Web Download', 'Web Research', 'Chat', 'Twitter', 'Facebook'
        , 'Google+'];
    industries: string[] = ['Travel Agents', 'Tourism Houses', 'Corporates', 'Retails', 'Events', 'ASP (Application Service Provider)', 'Data/Telecom OEM', 'ERP (Enterprise Resource Planning)',
        'Government/Military', 'Large Enterprise', 'ManagementISV', 'MSP Management Service Provider'
        , 'Network Equipment Enterprise', 'Non-management ISV', 'Optical Networking', 'Service Provider'
        , 'Small/Medium Enterprise', 'Storage Equipment', 'Storage Service Provider', 'System Integrator', 'Wireless Industry'
        , 'ERP', 'Management ISV', 'Others'];
    leadStatus = [
        { name: 'Attempted to Contact', color: '#ADD9FF' },
        { name: 'Contact in Future', color: '#F8E199' },
        { name: 'Contacted', color: '#FFD6BC' },
        { name: 'Junk Lead', color: '#EB4D4D' },
        { name: 'Lost Lead', color: '#C63D2F' },
        { name: 'Not Contacted', color: '#C4F0B3' },
        { name: 'Pre-Qualified', color: '#FFC6C6' },
        { name: 'Won', color: '#1A5D1A' },
        { name: 'Won Completed', color: '#1A5D1A' },
        { name: 'Won Voucher', color: '#1A5D1A' },
        { name: 'Qualified', color: '#FFC6ff' },
        { name: 'Not Qualified', color: '#F6C1FF' },
        // { name: '-Closed (lost)', color: 'red' },
        // { name: '-In progress', color: '#FFD6BC' },
        // { name: '-Closed (won)', color: '#1A5D1A' },
    ];

    revenueOptions: { label: string; value: string }[] = [
        { label: 'Less than $2000', value: '< $2000' },
        { label: 'Less than $5000', value: '< $5000' },
        { label: 'Less than $10000', value: '< $10000' },
        { label: 'Less than $30000', value: '< $30000' },
        { label: 'Less than $100000', value: '< $100000' },
        { label: 'More than $100000', value: '> $100000' },
        { label: 'Others', value: 'Others' }
    ];


    filteredOptions: Observable<string[]>;
    filteredOptionsIndustry: Observable<string[]>;
    filteredRevenue: Observable<{ label: string; value: string }[]>;


    showManualRevenueInput: boolean = false;
    showOtherIndustry: boolean = false;
    filteredStatus: Observable<State[]>;


    titles: string[] = ['Mr', 'Mrs', 'Ms', 'Dr', 'Prof.'];
    @ViewChild('addReason', { static: false }) addReasonModal!: TemplateRef<any>;
    @ViewChild('wonLead', { static: false }) wonLeadModel!: TemplateRef<any>;
    @ViewChild('followReminder', { static: false }) followReminderModel!: TemplateRef<any>;

    lostReason: string = '';
    loss_value: number;
    actualRevenue: number;
    lost;
    filteredIndustries: string[] = [];
    formattedAddress = ''

    optionsMap = {
        componentRestrictions: null
    };

    public file: any = {};

    userBusiness
    followUpDate: any;

    followUpReminder
    customerList: any[] = []
    filteredCustomers: Observable<any[]>;

    lead_won_date: any
    constructor(private fb: FormBuilder,
        private route: ActivatedRoute,
        private router: Router,
        private service: AgentService,
        public server: ServiceService,
        private breakpointObserver: BreakpointObserver,
        private global: GlobalVariable,
        private modalService: NgbModal,
        private toast: ToastServiceService, private staffDoc: StaffService,
        private cdr: ChangeDetectorRef, public dateAdapter: DateAdapter<Date>,
        private _customerService: CustomerService
    ) {
        this.dateAdapter.setLocale('en-AU');
        const business = JSON.parse(localStorage.getItem('business'))

        this.userBusiness = business.id
        this.global.showCrmTab = true

        breakpointObserver.observe([
            Breakpoints.HandsetLandscape,
            Breakpoints.HandsetPortrait
        ]).subscribe(result => {
            this.onScreensizeChange(result);
        });
        this.genericValidator = new GenericValidator(this.validationMessages);
    }

    ngOnInit(): void {



        this.initializeForm()
        this.handleQueryParam()

        this.sub = this.route.params.subscribe(
            params => {
                let id = +params['id'];
                if (id) {
                    this.getCustomer(id);
                }
            }
        );
        this.sub.add(null);
        const data = { type: 'saleperson' };
        this.server.getAdmin('active', data)
            .subscribe(users => {
                this.salePerson = users.data;
            },
                error => this.errorMessage = <any>error);


        this.filteredOptions = this.customerForm.get('lead_source')!.valueChanges.pipe(
            startWith(''),
            map((value) => this.filterOptions(value))
        );


        this.filteredOptionsIndustry = this.customerForm.get('industry')!.valueChanges.pipe(
            startWith(''),
            map((value) => this.filterOptionsIndustry(value, this.industries))
        );

        this.filteredStatus = this.customerForm.get('lead_status')!.valueChanges.pipe(
            startWith(''),
            map((value) => this.filterStatusOptions(value))
        );

        this.filteredRevenue = this.customerForm.get('annual_revenue')!.valueChanges.pipe(
            startWith(''),
            map((value) => this.filterRevenueOptions(value))
        );

        this.onboardingCustomers()

        this.filteredCustomers = this.customerForm.get('leaad_client_name')!.valueChanges
            .pipe(
                startWith({}),
                map(user => user && typeof user === 'object' ? user.name : user),
                map((name: string) => name ? this.filter(name) : this.customerList.slice())
            );
    }

    ngOnDestroy(): void {
        this.global.showCrmTab = false
        this.sub.unsubscribe();
        this.customerForm.reset();
    }

    ngAfterViewInit(): void {
        const controlBlurs: Observable<any>[] = this.formInputElements
            .map((formControl: ElementRef) => fromEvent(formControl.nativeElement, 'blur'));
        merge(this.customerForm.valueChanges, ...controlBlurs).pipe(
            debounceTime(500)
        ).subscribe(() => {
            this.displayMessage = this.genericValidator.processMessages(this.customerForm);
        });
    }

    getCustomer(id: number): void {
        this.service.getUser(id, this.extractedUrl)
            .subscribe(
                (customer) => {
                    this.onCustomerRetrieved(customer.data);
                },
                (error: any) => this.errorMessage = <any>error
            );
    }


    onCustomerRetrieved(customer): void {
        if (this.customerForm) {
            this.customerForm.reset();
        }
        this.user = customer;
        if (this.extractedUrl == 'edit/lead') {
            if (this.user.lead_status === 'Contacted') {
                this.leadStatus = [
                    { name: 'Contacted', color: '#FFD6BC' },
                    { name: 'Contacted Progress', color: '#FFD6BC' },
                    { name: 'Contacted Follow Up', color: '#FFD6BC' },
                    { name: 'Lost Lead', color: '#C63D2F' },
                    { name: 'Won', color: '#1A5D1A' },
                ];
            }
            if (this.user.annual_revenue == 'Others') {
                this.showManualRevenueInput = true
            }
            else {
                this.showManualRevenueInput = false
            }
            if (this.user.industry == 'Others') {
                this.showOtherIndustry = true
            }
            else {
                this.showOtherIndustry = false
            }
            this.customerForm.patchValue({
                name: this.user.name,
                company: this.user.company,
                email: this.user.email,
                saleperson_id: parseInt(this.user.saleperson_id),
                phone: this.user.phone,
                title: this.user.title,
                job_title: this.user.job_title,
                annual_revenue: this.user.annual_revenue,
                manual_revenue: this.user.manual_revenue,
                city: this.user.city,
                address: this.user.address,
                street: this.user.street,
                country: this.user.country,
                description: this.user.description,
                fax: this.user.fax,
                lead_status: this.user.lead_status,
                industry: this.user.industry,
                other_industry: this.user.other_industry,
                comp_size: this.user.comp_size,
                lead_source: this.user.lead_source,
                no_emp: this.user.no_emp,
                rating: this.user.rating,
                secondary_email: this.user.secondary_email,
                skype_id: this.user.skype_id,
                state: this.user.state,
                twitter: this.user.twitter,
                priority: this.user.priority,
                website: this.user.website,
                postal_code: this.user.postal_code,
                lost_lead_reason: this.user.lost_lead_reason,
                loss_value: this.user.loss_value,
                lost_lead_option: this.user.lost_lead_option,
                abn: this.user.abn,
                voucher: this.user?.voucher,
                no_of_traveler: this.user?.no_of_traveler,
                sub_company: this.user?.sub_company,
                actual_revenue: this.user?.actual_revenue,
                booking_expense: this.user?.booking_expense,
                travel_date: this.user?.travel_date
            });
            this.file.preview = this.user?.voucher;
            this.file.url = this.user?.voucher;
            this.lead_won_date = this.user?.lead_won_date
            if (this.user.leaad_client_name && this.customerList.length > 0) {
                let foundCustomer = this.customerList.find(customer => customer.id === parseInt(this.user.leaad_client_name));
                if (foundCustomer) {
                    this.customerForm.get('leaad_client_name').setValue(foundCustomer);
                }
            }
            else {
                let params = {
                    status: 'active',
                };
                this._customerService.getCustomer(params).subscribe(({ success, data }) => {
                    if (success) {
                        let foundCustomer = data.find(customer => customer.id === parseInt(this.user.leaad_client_name));
                        if (foundCustomer) {
                            this.customerForm.get('leaad_client_name').setValue(foundCustomer);
                        }
                    }
                });
            }

        }
        this.cdr.markForCheck()
    }

    saveCustomer(): void {
        if (this.customerForm.value.lead_status === 'Lost Lead' && !this.customerForm.value.lost_lead_option) {
            this.openLostReasonModal();
            return;
        }

        if (typeof this.customerForm.value.leaad_client_name === 'object') {
            this.customerForm.value.leaad_client_name = this.customerForm.value?.leaad_client_name?.id;
        }

        if (this.customerForm.valid) {
            this.customerForm.value.admin_id = this.global.admin.admin_id
            const customer = Object.assign({}, this.user, this.customerForm.value);
            this.service.saveCustomer(customer, this.type)
                .subscribe(
                    (response: any) => {
                        if (response.success) {
                            this.onSaveComplete();
                            this.toast.toastNotification(response.message, 'Leads Operation')
                            if (customer && customer.id && this.followUpDate) {
                                this.saveRemnder()
                            }
                        }
                        else {
                            this.toast.toastNotification1(response.message, 'Leads Operation')
                        }
                    },
                    (error: any) => this.errorMessage = <any>error
                );
        } else if (!this.customerForm.dirty) {
            this.onSaveComplete();
        }
        else {
            this.genericValidator = new GenericValidator(this.validationMessages);
        }
    }

    onSaveComplete(): void {
        this.customerForm.reset();
        this.router.navigate([this.backUrl]);
    }

    onScreensizeChange(result: any) {
        const isLess600 = this.breakpointObserver.isMatched('(max-width: 599px)');
        const isLess1000 = this.breakpointObserver.isMatched('(max-width: 959px)');
        if (isLess1000) {
            if (isLess600) {
                this.fieldColspan = 12;
            }
            else {
                this.fieldColspan = 6;
            }
        }
        else {
            this.fieldColspan = 4;
        }
    }


    filterOptions(value: string): string[] {
        if (!value) {
            return this.options;
        }

        const filterValue = value.toLowerCase();
        const matchingOptions = this.options.filter((option) => option.toLowerCase().includes(filterValue));
        const remainingOptions = this.options.filter((option) => !option.toLowerCase().includes(filterValue));
        return [...matchingOptions, ...remainingOptions];
    }


    filterOptionsIndustry(value: string, options: string[]): string[] {
        if (value === null) {
            return options;
        }

        const filterValue = value.toLowerCase();
        const matchingOptions = options.filter((option) => option.toLowerCase().includes(filterValue));
        // const remainingOptions = options.filter((option) => !option.toLowerCase().includes(filterValue));
        return [...matchingOptions];
    }


    filterRevenueOptions(value: string): { label: string; value: string }[] {
        if (value === null) {
            return this.revenueOptions; // Return all options when value is null
        }

        const filterValue = value.toLowerCase();
        const matchingOptions = this.revenueOptions.filter(
            (option) => option.label.toLowerCase().includes(filterValue)
        );
        const remainingOptions = this.revenueOptions.filter(
            (option) => !option.label.toLowerCase().includes(filterValue)
        );
        return [...matchingOptions, ...remainingOptions];
    }



    isRevenueSelected(option: number): boolean {
        return this.customerForm.get('annual_revenue').value === option;
    }


    filterStatusOptions(value: string): State[] {
        const filterValue = (value || '').toLowerCase();
        const matchingStatus = this.leadStatus.filter((status) => status.name.toLowerCase().includes(filterValue));
        const remainingStatus = this.leadStatus.filter((status) => !status.name.toLowerCase().includes(filterValue));
        return [...matchingStatus, ...remainingStatus];
    }



    onOptionSelectedRevenue(event: any) {
        const selectedValue = event.value;
        this.showManualRevenueInput = selectedValue === 'Others';
    }

    onOptionSelectedIndustry(event: any) {
        const selectedValue = event.option.value;
        this.showOtherIndustry = selectedValue === 'Others';
    }


    isOptionSelected(option: string): boolean {
        return this.customerForm.get('industry').value === option;
    }
    isStatusSelected(status: State): boolean {
        return this.customerForm.get('lead_status').value === status.name;
    }

    isIndustrySelected(option: string): boolean {
        return this.customerForm.get('industry').value === option;
    }

    isLeadSourceSelected(option: string): boolean {
        return this.customerForm.get('lead_source').value === option;
    }

    close(reason?: any): void {
        this.modalService.dismissAll()
    }

    openLostReasonModal(): void {
        const dialogRef = this.modalService.open(this.addReasonModal, {
            size: 'sm',
            centered: true,
            backdrop: 'static',
            keyboard: false,
        });
        dialogRef.result.then((result: string | undefined) => {
            if (result === 'cancel') {
            } else if (result === 'save') {
            }
        }, (reason: any) => {
            console.log('Modal dismissed with reason:', reason);
        });
    }

    saveLossReason(): void {
        if (this.lost && this.lost.trim() !== '') {
            this.customerForm.get('lost_lead_reason').setValue(this.lostReason);
            this.customerForm.get('lost_lead_option').setValue(this.lost);
            this.customerForm.get('loss_value').setValue(this.loss_value);
            this.close(this.lostReason);
        } else {
            console.log('Lost reason is empty');
        }
    }

    public handleAddressChange(address: any) {
        if (address) {
            this.formattedAddress = address.formatted_address;
            this.customerForm.get('address').setValue(this.formattedAddress);
            address.address_components.forEach(component => {
                if (component.types.includes('street_number')) {
                    this.customerForm.get('street').setValue(component.long_name);
                }
                if (component.types.includes('administrative_area_level_2')) {
                    this.customerForm.get('city').setValue(component.long_name);
                }
                if (component.types.includes('administrative_area_level_1')) {
                    this.customerForm.get('state').setValue(component.long_name);
                }
                if (component.types.includes('postal_code')) {
                    this.customerForm.get('postal_code').setValue(component.long_name);
                }
                if (component.types.includes('country')) {
                    this.customerForm.get('country').setValue(component.long_name);
                }
            });

        } else {
            this.customerForm.get('address').setValue('');
            this.customerForm.get('city').setValue('');
            this.customerForm.get('postal_code').setValue('');
            this.customerForm.get('state').setValue('');
            this.customerForm.get('street').setValue('');
            this.customerForm.get('country').setValue('');
        }
    }

    onOptionSelected(event: any) {
        if (event.value == 'Lost Lead') {
            this.openLostReasonModal();
        }
        else if (event.value == 'Won') {
            this.getAdmin()
            this.openWonModal()
        }

        else if (event.value == 'Contacted Follow Up') {
            this.openfollowUpModal()
        }
    }

    onFileChange(fileList: FileList) {
        const file = fileList[0];
        this.file = file;
        this.previewFile(file);
        this.handleFileInput(file);
    }

    previewFile(file: File) {
        const reader = new FileReader();
        reader.onload = () => {
            file['preview'] = reader.result as string;
        };
        reader.readAsDataURL(file);
    }

    handleFileInput(file: File) {
        const myFormData = new FormData();
        const headers = new HttpHeaders({
            'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
        })
        myFormData.append('file', file, file.name);
        myFormData.append('folder', 'leads')
        this.staffDoc.uploadImgPdf(myFormData, {
            headers: headers
        }).subscribe(
            response => {
                if (response.success) {
                    this.customerForm.get('voucher').setValue(response.url)
                    file['url'] = response.url;
                    this.cdr.markForCheck()
                }
                else {
                    this.toast.toastNotification1(response.message, 'Request Incomplete!')
                }
            },
            () => {
                this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Request Incomplete!')
            }
        );
    }

    viewFile(url) {
        if (url) {
            window.open(url, '_blank')
        }
    }

    removeFile() {
        this.customerForm.get('voucher').setValue(null)
        this.file = {}
    }

    checkExtension(): boolean {
        if (this.customerForm.value.voucher) {
            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
            const splitUrl = this.customerForm.value.voucher.split('/');
            const fileNameWithExt = splitUrl[splitUrl?.length - 1];
            const extension = fileNameWithExt.split('.').pop()?.toLowerCase();
            return extension ? imageExtensions.includes(extension) : false;
        }
    }

    initializeForm(): void {
        this.customerForm = this.fb.group({
            title: [''],
            name: ['', [Validators.required, Validators.minLength(5), Validators.maxLength(100), Validators.pattern(/[a-zA-Z]/)]],
            company: ['', this.userBusiness === 87 ? [Validators.required] : []],
            email: ['', [Validators.required, Validators.minLength(5), Validators.email, Validators.maxLength(200)]],
            phone: ['', Validators.required],
            job_title: [''],
            saleperson_id: ['', [Validators.required]],
            website: [''],
            lead_source: [''],
            lead_status: ['', [Validators.required]],
            industry: [''],
            no_emp: ['', Validators.min(1)],
            annual_revenue: [''],
            manual_revenue: [''],
            rating: [''],
            skype_id: [''],
            secondary_email: [''],
            twitter: [''],
            priority: [''],
            address: [''],
            street: [''],
            state: [''],
            country: [''],
            city: [''],
            postal_code: [''],
            description: [''],
            fax: [''],
            lost_lead_reason: [''],
            loss_value: [''],
            lost_lead_option: [''],
            other_industry: [''],
            comp_size: [''],
            abn: [''],
            voucher: [''],
            no_of_traveler: [''],
            sub_company: ['', this.userBusiness === 87 ? [Validators.required] : []],
            leaad_client_name: [''],
            actual_revenue: [''],
            booking_expense: [''],
            assign_operation: [''],
            travel_date: [''],
            lead_won_date: ['']
        });
    }

    handleQueryParam(): void {
        this.route.queryParams.subscribe(params => {
            const optionalParam = params['type'];
            if (optionalParam == 'won') {
                this.backUrl = '/crm/won'
                this.pageTitle = 'Update Lead'
            }
            else if (optionalParam == 'lost') {
                this.backUrl = '/crm/lost'
                this.pageTitle = 'Update Lead'
            }
            else if (optionalParam == 'lead') {
                this.backUrl = '/crm/lead'
                this.pageTitle = 'Update Lead'
            }
            else if (optionalParam == 'contacted') {
                this.backUrl = '/crm/contacted'
                this.pageTitle = 'Update Lead'
            }
        })
        this.url = this.route.snapshot.url.join('/');
        const lastSlashIndex = this.url.lastIndexOf('/');
        this.extractedUrl = lastSlashIndex !== -1 ? this.url.substring(0, lastSlashIndex) : this.url;

        if (this.url == 'create/sale-person') {
            this.type = this.url
            this.backUrl = '/crm/sales-person'
            this.pageTitle = 'Create Sale Person'
        }
        else if (this.extractedUrl == 'edit/sale-person') {
            this.type = this.extractedUrl
            this.backUrl = '/crm/sales-person'
            this.pageTitle = 'Update Sale Person'
        }
        else if (this.url == 'create/lead') {
            this.type = this.url
            this.backUrl = '/crm/lead'
            this.pageTitle = 'Create Lead'
        }
    }

    onDiscountKeyDown(event: KeyboardEvent): void {
        if (event.key === '-') {
            event.preventDefault();
        }
    }
    onPhoneInput(event: any) {
        event.target.value = event.target.value.replace(/[^0-9+]/g, '');
    }

    openWonModal(): void {
        const dialogRef = this.modalService.open(this.wonLeadModel, {
            // size: 'sm',
            centered: true,
            backdrop: 'static',
            keyboard: false,
        });

        dialogRef.result.then((result: string | undefined) => {
            if (result === 'cancel') {
                console.log('Modal closed without saving');
            } else if (result === 'save') {
            }
        }, (reason: any) => {
            console.log('Modal dismissed with reason:', reason);
        });
    }

    openfollowUpModal(): void {
        const dialogRef = this.modalService.open(this.followReminderModel, {
            size: 'sm',
            centered: true,
            backdrop: 'static',
            keyboard: false,
        });

        dialogRef.result.then((result: string | undefined) => {
            if (result === 'cancel') {
                console.log('Modal closed without saving');
            } else if (result === 'save') {
            }
        }, (reason: any) => {
            console.log('Modal dismissed with reason:', reason);
        });
    }

    wonRevenue(): void {
        if (this.actualRevenue) {
            this.customerForm.get('actual_revenue').setValue(this.actualRevenue);
            const reminderDate = moment(this.followUpDate);
            this.customerForm.get('lead_won_date').setValue(reminderDate);
            this.close(this.actualRevenue);
        } else {
            console.log('Lost reason is empty');
            this.toast.toastNotification('Please provide actual revenue to make it won lead', 'warning');
        }
    }


    receiveDataFromChild(data: any) {
        const id = data.value.id
        this.customerForm.get('assign_operation').setValue(id);
    }

    getAdmin() {
        this.server.getAdmin('active').subscribe(({ data, success }) => {
            if (success) {
                this.admins = data
            }
        })
    }


    followReminders() {
        if (this.followUpDate) {
            const reminderDate = moment(this.followUpDate);
            if (reminderDate.isValid()) {
                let reminder = reminderDate.format('MM-DD-YYYY');
                this.close();
            } else {
                this.toast.toastNotification('The selected date is invalid.', 'warning');
            }
        } else {
            this.close();
            this.toast.toastNotification('You have not selected any date so the reminder will not be set.', 'warning');
        }
    }

    saveRemnder(): void {
        const reminderDate = moment(this.followUpDate);
        let date = reminderDate.format('YYYY-MM-DD');
        const reminder: Reminder = {
            created_by: this.global.admin?.admin_id,
            lead_id: this.user.id,
            subject: this.user.name,
            date: date,
            description: "N/A",
            notify_too: ""
        };
        if (reminderDate.isValid()) {
            this.service.addReminder({ reminders: [reminder] }).subscribe(({ success, message }) => {
                if (success) {
                    this.toast.toastNotification(message, 'Task Reminder!')
                }
            });
        }
    }


    onboardingCustomers() {
        let params = {
            status: 'active',
        };
        this._customerService.getCustomer(params).subscribe(({ success, data }) => {
            if (success) {
                this.customerList = data;
                this.cdr.markForCheck()
            }
        });
    }

    filter(name: string) {
        return this.customerList.filter(option =>
            option.name.toLowerCase().indexOf(name.toLowerCase()) === 0);
    }

    displayFn(user: any): string {
        return user ? user.name : '';
    }

    status: boolean = false;
    onCustomerSelected(event: MatAutocompleteSelectedEvent) {
        const selectedOption = event.option.value;
        if (selectedOption) {
            this.customerForm.get('email').setValue(selectedOption.email);
            this.customerForm.get('phone').setValue(selectedOption.phone);
            this.customerForm.get('city').setValue(selectedOption.city);
            this.customerForm.get('country').setValue(selectedOption.country);

            this.status = true;
        }

        else {
            this.status = false;
        }
    }

    onCustomerInput() {
        this.status = false;
    }
}


interface Reminder {
    created_by: number;
    lead_id: number;
    subject: string;
    date: string; // format: "YYYY-MM-DD"
    description: string;
    notify_too: string;
}


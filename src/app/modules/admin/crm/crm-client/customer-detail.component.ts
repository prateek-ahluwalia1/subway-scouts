import { HttpHeaders } from '@angular/common/http';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { FormArray, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { AgentService } from 'app/services/crm/agent.service';
import { GlobalVariable } from 'app/shared/global';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';
import { NgbModal, NgbPopover } from '@ng-bootstrap/ng-bootstrap';
import { PermissionsService } from 'app/services/permissions.service';
import { MatDialog } from '@angular/material/dialog';
import { ServiceService } from 'app/services/service.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { CustomerService } from 'app/services/customer.service';
import { MatAutocompleteSelectedEvent } from '@angular/material/autocomplete';
import { MailboxComposeComponent } from '../../mailbox/compose/compose.component';
import { MsalService } from '@azure/msal-angular';
import { MailboxService } from '../../mailbox/mailbox.service';


export interface State {
    color: string;
    name: string;
}
@Component({
    selector: 'customer-detail',
    templateUrl: './customer-details.component.html',
    styleUrls: ['./customer-detail.component.css'],
    // changeDetection: ChangeDetectionStrategy.OnPush,
    styles: [`
  @import url(https://fonts.googleapis.com/css?family=Montserrat:900|Raleway:400,400i,700,700i);
  $black: #1d1f20;
  $blue: #83e4e2;
  $green: #a2ed56;
  $yellow: #fafafa;
  $white: #fafafa;
  %boxshadow {
    box-shadow: 0.25rem 0.25rem 0.6rem rgba(0,0,0,0.05), 0 0.5rem 1.125rem rgba(75,0,0,0.05);
  }
  main {
    display: block;
    margin: 0 auto;
    max-width: 45rem;
    padding: 1rem;
  }
  
  
  ol.gradient-list {
    counter-reset: gradient-counter;
    list-style: none;
    margin: 1.75rem 0;
    padding-left: 1rem;
    > li {
      background: white;
      border-radius: 0 0.5rem 0.5rem 0.5rem;
      @extend %boxshadow;
      counter-increment: gradient-counter;
      margin-top: 1rem;
      min-height: 3rem;
      padding: 1rem 1rem 1rem 3rem;
      position: relative;
      &::before,
      &::after {
        background: linear-gradient(135deg, $blue 0%,$green 100%);
        border-radius: 1rem 1rem 0 1rem;
        content: '';
        height: 3rem;
        left: -1rem;
        overflow: hidden;
        position: absolute;
        top: -1rem;
        width: 3rem;
      }
      &::before {
        align-items: flex-end;
        @extend %boxshadow;
        content: counter(gradient-counter);
        color: $black;
        display: flex;
        font: 900 1.5em/1 'Montserrat';
        justify-content: flex-end;
        padding: 0.125em 0.25em;
        z-index: 1;
      }
      @for $i from 1 through 5 {
        &:nth-child(10n+#{$i}):before {
          background: linear-gradient(135deg, rgba($green, $i * 0.2) 0%,rgba($yellow, $i * 0.2) 100%);
        }
      }
      @for $i from 6 through 10 {
        &:nth-child(10n+#{$i}):before {
          background: linear-gradient(135deg, rgba($green, 1 - (($i - 5) * 0.2)) 0%,rgba($yellow, 1 - (($i - 5) * 0.2)) 100%);
        }
      }
      + li {
        margin-top: 2rem;
      }
    }
  }
  `]

})
export class CustomerDetailComponent implements OnInit {


    sectionTypes = 'Overview'
    customerForm: FormGroup;
    timeLine: any[] = []
    id
    comments: any = []
    attachments: any[] = []
    vouchers: any[] = []
    refrenceSheets: any[] = []
    quote: any = []
    emails: any = []
    errorMessage: string;
    user: any = [];
    type = 'create/lead'
    showTooltip = false;
    showOtherIndustry: boolean = false;


    leadStatus: State[] = [
        { name: '-Null-', color: 'red' },
        { name: 'Attempted to Contact', color: '#ADD9FF' },
        { name: 'Contact in Future', color: '#F8E199' },
        { name: 'Contacted', color: '#FFD6BC' },
        { name: 'Junk Lead', color: '#EB4D4D' },
        { name: 'Lost Lead', color: '#CED9FF' },
        { name: 'Not Contacted', color: '#C4F0B3' },
        { name: 'Pre-Qualified', color: '#FFC6C6' },
        { name: 'Won', color: '#FFC6ac' },
        { name: 'Won Completed', color: '#1A5D1A' },
        { name: 'Won Voucher', color: '#1A5D1A' },
        { name: 'Qualified', color: '#FFC6DC' },
        { name: 'Not Qualified', color: '#000000' },
    ];
    industries: string[] = ['Travel Agents', 'Tourism Houses', 'Corporates', 'Retails', 'Events', 'ASP (Application Service Provider)', 'Data/Telecom OEM', 'ERP (Enterprise Resource Planning)',
        'Government/Military', 'Large Enterprise', 'ManagementISV', 'MSP Management Service Provider'
        , 'Network Equipment Enterprise', 'Non-management ISV', 'Optical Networking', 'Service Provider'
        , 'Small/Medium Enterprise', 'Storage Equipment', 'Storage Service Provider', 'System Integrator', 'Wireless Industry'
        , 'ERP', 'Management ISV', 'Others'];
    options: string[] = ['Existing Client', 'New Client', 'Advertisement', 'Cold Call', 'Employee Referral',
        'External Referral', 'Online Store', 'Partner', 'Public Relations'
        , 'Sales Email Alias', 'Seminar Partner', 'Internal Partner', 'Trade Show'
        , 'Web Download', 'Web Research', 'Chat', 'Twitter', 'Facebook'
        , 'Google+'];

    revenueOptions: { label: string; value: string }[] = [
        { label: 'Less than $2000', value: '< $2000' },
        { label: 'Less than $5000', value: '< $5000' },
        { label: 'Less than $10000', value: '< $10000' },
        { label: 'Less than $30000', value: '< $30000' },
        { label: 'Less than $100000', value: '< $100000' },
        { label: 'More than $100000', value: '> $100000' },
        { label: 'Others', value: 'Others' }
    ];

    filteredOptionsIndustry: Observable<string[]>;

    filteredRevenue: Observable<{ label: string; value: string }[]>;

    filteredOptions: Observable<string[]>;

    filteredStatus: Observable<State[]>;

    showManualRevenueInput: boolean = false;

    isModalOpen = false;
    adminPermissions: any;

    formattedAddress = ''
    optionsMap = {
        componentRestrictions: {
            country: ['AU']
        }
    }
    @ViewChild('addReason', { static: false }) addReasonModal!: TemplateRef<any>;
    @ViewChild('wonLead', { static: false }) wonLeadModel!: TemplateRef<any>;
    lostReason: string = '';
    loss_value: number
    actualRevenue: number;
    lost;
    salePerson: any;
    userBusiness: any;
    admins: any[] = []

    isEditingWon = false;
    tempRevenue: string;

    reminderForm: FormGroup;
    remindersList: any[] = []
    combineAdminSaleperson: any[] = []
    pendingReminders: any[] = []
    won_status
    leadObject: any;
    @ViewChild('popover3') popover3: NgbPopover;

    customerList: any[] = []
    filteredCustomers: Observable<any[]>;

    isAuthenticated: boolean = false
    outlookEmails: any[] = []

    constructor(private route: ActivatedRoute, private fb: FormBuilder,
        private service: AgentService, private global: GlobalVariable,
        private modalService: NgbModal, private permissionService: PermissionsService, public server: ServiceService,
        private toast: ToastServiceService, private changeDetect: ChangeDetectorRef, private _customerService: CustomerService,
        private _matDialog: MatDialog, private _msalService: MsalService, private _mailboxService: MailboxService,
    ) {
        this.global.showCrmTab = true

        const business = JSON.parse(localStorage.getItem('business'))
        this.userBusiness = business.id
        this.reminderForm = this.fb.group({
            reminders: this.fb.array([])
        });

        this.onboardingCustomers();
    }


    ngOnInit(): void {
        const per = this.permissionService.getPermissionsByTitle('CRM');
        this.adminPermissions = per?.childPage?.find(item => item.title === 'Leads');
        this.customerForm = this.fb.group({
            name: ['', Validators.required],
            admin_email: [''],
            admin_name: [''],
            admin_phone: [''],
            admin_id: [''],
            phone: [''],
            email: [''],
            company: [''],
            website: [''],
            lead_source: [''],
            lead_status: [''],
            industry: [''],
            no_emp: [''],
            annual_revenue: [''],
            manual_revenue: [''],
            rating: [''],
            skype_id: [''],
            secondary_email: [''],
            twitter: [''],
            street: [''],
            state: [''],
            address: [''],
            country: [''],
            city: [''],
            postal_code: [''],
            description: [''],
            fax: [''],
            createdby_name: [''],
            handledby_email: [''],
            handledby_name: [''],
            handledby_phone: [''],
            comment: [''],
            lost_lead_reason: [''],
            lost_lead_option: [''],
            loss_value: [''],
            saleperson_id: [''],
            other_industry: [''],
            no_of_traveler: [''],
            sub_company: [''],
            leaad_client_name: [''],
            actual_revenue: [''],
            booking_expense: [''],
            assign_operation: [''],
            travel_date: ['']
        });



        this.route.params.subscribe(
            params => {
                let id = +params['id'];
                if (id) {
                    this.getReminders(id)
                    let data = {
                        type: "saleperson",
                    }
                    this.server.getAdmin('active', data)
                        .subscribe(users => {
                            this.salePerson = users.data;
                        },

                            error => this.errorMessage = <any>error);
                    this.id = id
                    this.getCustomer(id);
                    this.getNotes(id)
                    this.getAttachments(id)
                    this.getQuote(id)
                    this.emailHistory(id)

                }
            }
        );



        this.filteredOptions = this.customerForm.get('lead_source').valueChanges.pipe(
            startWith(''),
            map((value) => this.filterOptions(value))
        );

        this.filteredOptionsIndustry = this.customerForm.get('industry').valueChanges.pipe(
            startWith(''),
            map((value) => this.filterOptionsIndustry(value, this.industries))
        );

        this.filteredStatus = this.customerForm.get('lead_status').valueChanges.pipe(
            startWith(''),
            map((value) => this.filterStatusOptions(value))
        );

        this.filteredRevenue = this.customerForm.get('annual_revenue')!.valueChanges.pipe(
            startWith(''),
            map((value) => this.filterRevenueOptions(value))
        );



        this.filteredCustomers = this.customerForm.get('leaad_client_name')!.valueChanges
            .pipe(
                startWith({}),
                map(user => user && typeof user === 'object' ? user.name : user),
                map((name: string) => name ? this.filter(name) : this.customerList.slice())
            );

        this.addReminderForm();
        this.changeDetect.markForCheck()


        this.isAuthenticated = this._msalService.instance.getAllAccounts().length > 0;

    }

    getCustomer(id: number): void {
        this.service.getUser(id)
            .subscribe(
                (customer) => {
                    if (customer.success) {
                        this.onCustomerRetrieved(customer.data);
                    }
                },
                (error: any) => this.errorMessage = <any>error
            );
    }

    onCustomerSelected(custID: number): void {
        const selectedAgent = this.salePerson.find(agent => agent.id === custID);

        if (selectedAgent) {
            this.customerForm.patchValue({
                handledby_email: selectedAgent.email,
                saleperson_id: selectedAgent.id,
                handledby_phone: selectedAgent.phone,
            });
        }
    }
    onCustomerRetrieved(customer): void {

        if (this.customerForm) {
            this.customerForm.reset();
        }
        this.user = customer;
        console.log("User", this.user)
        if (this.user.annual_revenue == 'Others') {
            this.showManualRevenueInput = true
        }
        else {
            this.showManualRevenueInput = false
        }

        if (this.user.industry == 'Others') {
            this.showOtherIndustry = true
        }

        this.customerForm.patchValue({
            handledby_email: this.user?.handledby_email,
            handledby_name: this.user?.handledby_id,
            handledby_phone: this.user?.handledby_phone,
            createdby_name: this.user?.createdby_name,
            name: this.user?.name,
            email: this.user?.email,
            company: this.user?.company,
            phone: this.user?.phone,
            postal_code: this.user?.postal_code,
            website: this.user?.website,
            twitter: this.user?.twitter,
            street: this.user?.street,
            state: this.user?.state,
            address: this.user?.address,
            skype_id: this.user?.skype_id,
            secondary_email: this.user?.secondary_email,
            rating: this.user?.rating,
            no_emp: this.user?.no_emp,
            lead_status: this.user?.lead_status,
            lead_source: this.user?.lead_source,
            industry: this.user?.industry,
            fax: this.user?.fax,
            description: this.user?.description,
            country: this.user?.country,
            city: this.user?.city,
            annual_revenue: this.user?.annual_revenue,
            manual_revenue: this.user.manual_revenue,
            lost_lead_reason: this.user.lost_lead_reason,
            lost_lead_option: this.user.lost_lead_option,
            loss_value: this.user.loss_value,
            saleperson_id: this.user.saleperson_id,
            other_industry: this.user.other_industry,
            no_of_traveler: this.user?.no_of_traveler,
            sub_company: this.user?.sub_company,
            booking_expense: this.user?.booking_expense,
            assign_operation: this.user?.assign_operation,
            actual_revenue: this.user?.actual_revenue,
            travel_date: this.user?.travel_date

        });
        this.won_status = this.user?.won_status
        if (this.user?.leaad_client_name) {
            // console.log('User lead client name:', this.user.leaad_client_name);
            this.customerForm.get('leaad_client_name').setValue(this.user.leaad_client_name);

            let clientIntegerName = parseInt(this.user.leaad_client_name);
            // console.log('Parsed client integer name:', clientIntegerName);

            if (!isNaN(clientIntegerName)) {
                let foundCustomer = this.customerList.find(customer => {
                    return customer.id === clientIntegerName;
                });

                if (foundCustomer) {
                    this.customerForm.get('leaad_client_name').setValue(foundCustomer);
                } else {
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
        }

        if (this.isAuthenticated) {
            this.getEmailsByRecipient();
        }
        this.changeDetect.detectChanges()
    }



    sectionTYpe(type) {
        this.sectionTypes = type
        this.route.params.subscribe(
            params => {
                let id = +params['id'];
                if (id) {
                    let data = {
                        id: id
                    }
                    if (type == 'Timeline') {
                        this.service.getTimeLine(data).subscribe(({ data, success }) => {
                            if (success) {
                                this.timeLine = data.map(item => {
                                    return {
                                        ...item,
                                        after_update: JSON.parse(item.after_update)
                                    };
                                });
                                this.changeDetect.markForCheck();
                            }
                        })
                    }
                    else if (type == 'Reminder') {
                        this.server.getAdmin('active').subscribe({
                            next: ({ data: adminsData, success }) => {
                                if (success) {
                                    const requestData = { type: 'saleperson' };
                                    this.server.getAdmin('active', requestData).subscribe({
                                        next: users => {
                                            this.combineAdminSaleperson = adminsData.concat(users.data);
                                        },
                                        error: error => this.errorMessage = error
                                    });
                                }
                            },
                            error: error => this.errorMessage = error
                        });
                    }


                }
            })
    }

    uploadFile
    handleFileInput(type) {
        let input = document.createElement("input");
        input.type = "file";
        input.accept = "image/*,application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"; // Add .xlsx file type

        input.onchange = (_) => {
            let files = Array.from(input.files);
            if (!files || files.length === 0) return;
            this.uploadFile = files[0];
            const myFormData = new FormData();
            const headers = new HttpHeaders({
                'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
            })
            myFormData.append('file', this.uploadFile, this.uploadFile.name);
            myFormData.append('folder', 'crm_customers_file')
            myFormData.append('customer_id', this.id)
            myFormData.append('admin_id', this.global.admin.admin_id)
            myFormData.append('type', type)
            this.service.uploadImgPdf(myFormData, {
                headers: headers
            }).subscribe(
                response => {
                    if (response.success) {
                        this.getAttachments(this.id)
                    }
                    this.changeDetect.markForCheck()
                },
                (error) => {
                    console.error(error);
                }
            );

        };
        input.click();
        this.changeDetect.detectChanges()

    }


    okey() {
        const payload = {
            comment: this.customerForm.value.comment,
            customer_id: this.id
        };
        this.service.saveNotes(payload).subscribe(({ success }) => {
            if (success) {
                this.getNotes(this.id)
                this.customerForm.get('comment').setValue('')
                this.changeDetect.markForCheck()
            }

        })
    }


    getNotes(id) {

        this.service.getNotes(id).subscribe(({ data, success }) => {
            if (success) {
                this.comments = data
            }
            this.changeDetect.detectChanges()
        })
    }

    getAttachments(id) {
        this.service.getAttachments(id).subscribe(({ data, success }) => {
            if (success) {
                this.attachments = data.filter(item => item.type === 'attachments');
                this.vouchers = data.filter(item => item.type === 'voucher');
                this.refrenceSheets = data.filter(item => item.type === 'refrence');
                this.changeDetect.markForCheck()
            }
            else {
                this.attachments = []
            }

        })
    }

    getQuote(id) {
        this.service.getQuote(id).subscribe(({ data, success }) => {
            if (success) {
                this.quote = data
            }
            this.changeDetect.markForCheck()

        })
    }


    emailHistory(id) {
        this.service.emailHistory(id).subscribe(({ data, success }) => {
            if (success) {
                this.emails = data
            }
            this.changeDetect.markForCheck()
        })
    }

    roundSize(size: string): string {
        let sizeInKB = parseFloat(size);
        const sizes = ['KB', 'MB', 'GB', 'TB'];
        let i = 0;
        while (sizeInKB >= 1024 && i < sizes.length - 1) {
            sizeInKB /= 1024;
            i++;
        }
        return sizeInKB.toFixed(2) + ' ' + sizes[i];
    }




    onSelectionChange() {
        this.customerForm.value.admin_id = this.global.admin.admin_id
        this.saveFields()
    }
    onSelectionChangeLeadSource() {
        this.customerForm.value.admin_id = this.global.admin.admin_id
        this.saveFields()
    }

    saveFields() {
        this.customerForm.markAllAsTouched()
        if (typeof this.customerForm.value.leaad_client_name === 'object') {
            console.log("Submit data", this.customerForm.value.leaad_client_name)
            this.customerForm.value.leaad_client_name = parseInt(this.customerForm.value.leaad_client_name.id);
        }
        this.customerForm.value.admin_id = this.global.admin.admin_id
        const customer = Object.assign({}, this.user, this.customerForm.value);
        console.log("Form value", this.customerForm.value)
        if (this.customerForm.valid) {
            this.service.saveCustomer(customer, this.type)
                .subscribe(
                    (response: any) => {
                        if (response.success) {
                            this.toast.toastNotification(response.message, 'Lead Operation!')
                            this.getCustomer(this.id)
                            this.changeDetect.markForCheck()

                        }
                        else {
                            this.toast.toastNotification1(response.message, 'Leads Operation')
                        }
                    },
                    (error: any) => this.errorMessage = <any>error
                );
        }
    }

    viewImage(url) {
        window.open(url, '_blank');
    }

    ngOnDestroy(): void {
        this.global.showCrmTab = false
    }

    filterOptions(value: string | null): string[] {
        if (!value) {
            return this.options;
        }

        const filterValue = value.toLowerCase();
        const matchingOptions = this.options.filter((option) => option.toLowerCase().includes(filterValue));
        const remainingOptions = this.options.filter((option) => !option.toLowerCase().includes(filterValue));
        return [...matchingOptions, ...remainingOptions];
    }


    filterOptionsIndustry(value: string | null, options: string[]): string[] {
        if (!value) {
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
        if (!value) {
            return this.leadStatus;
        }

        const filterValue = value.toLowerCase();
        const matchingStatus = this.leadStatus.filter((status) => status.name.toLowerCase().includes(filterValue));
        const remainingStatus = this.leadStatus.filter((status) => !status.name.toLowerCase().includes(filterValue));
        return [...matchingStatus, ...remainingStatus];
    }



    onOptionSelectedRevenue(event: any) {
        const selectedValue = event.value;
        this.showManualRevenueInput = selectedValue === 'Others';
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

    isAddressAvailable(): boolean {
        // const street = this.customerForm.get('street').value;
        const city = this.customerForm.get('city').value;
        // const state = this.customerForm.get('state').value;
        const country = this.customerForm.get('country').value;

        return !!city && !!country;
    }


    openModal() {
        if (this.isAuthenticated) {
            const dialogRef = this._matDialog.open(MailboxComposeComponent, {
                data: { email: this.user?.email, readonly: true }
            });
            dialogRef.afterClosed().subscribe((result) => {
                this.getEmailsByRecipient()
                console.log('Compose dialog was closed!', result);
            });
        }
        else {
            this.login()
        }
    }
    login() {
        const loginRequest = {
            scopes: ['User.Read', 'Mail.ReadBasic', 'Mail.Read', 'Mail.Send', 'Mail.ReadWrite.Shared', 'Mail.ReadWrite', 'MailboxSettings.ReadWrite'],
        };

        this._msalService
            .loginPopup(loginRequest)
            .subscribe((response) => {
                console.log(response);
                this.isAuthenticated = true;
                this.getEmailsByRecipient();
            }, () => {
                alert('Login failed');
            });

        this.isAuthenticated = this._msalService.instance.getAllAccounts().length > 0;

    }

    getEmailsByRecipient() {
        if (this.user) {
            this._mailboxService.getMailsByRecipient().subscribe(
                (emails) => {
                    this.outlookEmails = emails.filter((email) => {
                        const fromCheck = email.from?.emailAddress?.address === this.user.email;
                        const toCheck = email.toRecipients?.some(
                            (recipient) => recipient?.emailAddress?.address === this.user.email
                        );
                        return fromCheck || toCheck;
                    });
                    this.outlookEmails.forEach(element => {
                        if (element.hasAttachments) {
                            this._mailboxService.fetchEmailAttachments(element.id).subscribe((attachments) => {
                                element.attachments = attachments;
                            });
                        }
                    });
                    this.changeDetect.detectChanges();
                },
                (error) => {
                    console.error('Failed to fetch emails', error);
                }
            );
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
            this.customerForm.get('suburb').setValue('');
            this.customerForm.get('country').setValue('');
        }
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
                console.log('Modal closed without saving');
            } else if (result === 'save') {
                // console.log('Lost Reason: ', this.lostReason, this.lost);
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

    close(reason: any): void {
        this.modalService.dismissAll(reason)
    }

    onOptionSelected(event: any) {
        if (event.option.value == 'Lost Lead') {
            this.openLostReasonModal();
        }
        else if (event.option.value == 'Won' && this.userBusiness == 87) {
            this.getAdmin()
            this.openWonModal()
        }
        else {
            this.actualRevenue = null
            this.customerForm.get('actual_revenue').setValue('')
        }
    }

    onOptionSelectedIndustry(event: any) {
        const selectedValue = event.option.value;
        this.showOtherIndustry = selectedValue === 'Others';
    }

    delFile(att) {
        let data = {
            id: att.id
        }
        this.service.delFile(data).subscribe(res => {
            if (res.success) {
                this.getAttachments(this.id)
                this.toast.toastNotification(res.message, 'File!')
            }
            this.changeDetect.detectChanges()
            this.changeDetect.markForCheck()

        })
    }

    openWonModal(): void {
        const dialogRef = this.modalService.open(this.wonLeadModel, {
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


    startEdit() {
        this.tempRevenue = this.customerForm.get('actual_revenue').value || '';
    }

    saveActualRevenue() {
        this.customerForm.get('actual_revenue').setValue(this.tempRevenue);
        this.saveFields(); // Assuming saveFields contains the logic to persist the form's state
    }


    get reminders(): FormArray {
        return this.reminderForm.get('reminders') as FormArray;
    }

    addReminderForm(): void {
        const reminderFormGroup = this.fb.group({
            created_by: [this.global.admin?.admin_id], // Assuming taskOwner is pre-set and readonly
            created_by_admin: [{ value: this.global.admin.admin_name, disabled: true }],
            subject: ['', Validators.required],
            lead_id: [this.id],
            date: ['', Validators.required],
            description: [''],
            notify_too: [''] // Assuming this is handled by your custom component
        });
        this.reminders.push(reminderFormGroup);
    }

    onSubmit(): void {
        if (this.reminderForm.valid) {
            this.service.addReminder(this.reminderForm.value).subscribe(({ success, message }) => {
                if (success) {
                    this.resetForm();
                    this.toast.toastNotification(message, 'Task Reminder!')
                    this.getReminders(this.id)
                    this.changeDetect.markForCheck()
                }
            });
        } else {
            this.reminderForm.markAllAsTouched(); // Mark all fields as touched to show validation errors
            console.error('Form is not valid');
        }
    }


    receiveDataFromChildSingle(selectedUsersControl: any, index: number): void {
        const userObjects = selectedUsersControl.value;
        const userIds = userObjects.map(user => user.id);
        const reminderGroup = this.reminders.at(index) as FormGroup;
        reminderGroup.patchValue({
            notify_too: userIds
        });
    }





    getReminders(id) {
        this.service.getReminders(id).subscribe(({ success, data }) => {
            if (success) {
                this.remindersList = data
                this.pendingReminders = data?.filter(item => item.status === 'pending').length;
                this.changeDetect.markForCheck()
            }
        })
    }

    resetForm(): void {
        this.reminderForm.reset();
        const reminders = this.reminderForm.get('reminders') as FormArray;
        while (reminders.length !== 0) {
            reminders.removeAt(0);
        }
        this.addReminderForm();

        this.changeDetect.markForCheck()
    }


    delReminder(id) {
        this.service.delReminder(id).subscribe(({ success, message }) => {
            if (success) {
                this.toast.toastNotification(message, 'Lead Reminder!');
                this.getReminders(this.id)
                this.changeDetect.markForCheck()
            }
            else {
                this.toast.toastNotification1(message, 'Lead Reminder!');
            }
        },
            (() => {
                this.toast.toastNotification1(this.global.apiError, 'Lead Reminder!');
            }))
    }


    markAsComplete(id) {
        this.service.markAsComplete(id).subscribe(({ success, message }) => {
            if (success) {
                this.toast.toastNotification(message, 'Mark As Complete');
                this.getReminders(this.id)
                this.changeDetect.markForCheck()
            }
        },
            (() => {
                this.toast.toastNotification1(this.global.apiError, 'Mark As Complete');
            }))
    }


    wonProgressStatus(lead: any) {
        console.log(lead);
        
        this.leadObject = lead

    }


    updateWonStatus() {
        if (this.won_status) {
            this.customerForm.value.won_status = this.won_status
            this.saveFields()
        }
        else {
            this.toast.toastNotification('Select value to continue', 'Warning');
        }

    }


    closePopover3() {
        this.popover3.close();
    }

    removeReminder(index: number): void {
        this.reminders.removeAt(index);
    }


    onboardingCustomers() {
        let params = {
            status: 'active',
        };
        this._customerService.getCustomer(params).subscribe(({ success, data }) => {
            if (success) {
                this.customerList = data;
                console.log("Customer list", this.customerList)
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

    onCustomersSelected(event: MatAutocompleteSelectedEvent) {
        const selectedOption = event.option.value;
        console.log("On customer selected", selectedOption)
        if (selectedOption) {
            this.customerForm.get('email').setValue(selectedOption.email);
            this.customerForm.get('phone').setValue(selectedOption.phone);
            this.customerForm.get('city').setValue(selectedOption.city);
            this.customerForm.get('country').setValue(selectedOption.country);
        }
    }
}
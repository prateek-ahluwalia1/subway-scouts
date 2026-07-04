import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { Component, HostListener, OnInit, Renderer2, Input, ElementRef, ViewChild } from '@angular/core';
import { AbstractControl, FormBuilder, FormControl, FormGroup, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';
import { MatDatepickerInputEvent } from '@angular/material/datepicker';
import moment from 'moment';
import { DateAdapter } from '@angular/material/core';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { AdminService } from 'app/services/admin.service';
import { CustomerService } from 'app/services/customer.service';
import { GlobalVariable } from 'app/shared/global';
import { ContractorService } from 'app/services/contractor.service';
import { AfterViewInit } from '@angular/core';
import { map, startWith } from 'rxjs/operators';
import { NgxSpinnerService } from 'ngx-spinner';
import { PermissionsService } from 'app/services/permissions.service';
import { Observable } from 'rxjs';
export class Customers {
  id: number;
  name: string;
}
@Component({
  selector: 'app-person-detail',
  templateUrl: './person-detail.component.html',
  styleUrls: ['./person-detail.component.scss']
})
export class PersonDetailComponent implements OnInit, AfterViewInit {

  tabType: string = 'Personal details';
  @Input() public staff;
  @Input() userData;
  @Input() type;
  date
  base64String: string;
  profileImage: string;
  selectedDate
  guardType
  newStaff: FormGroup;
  listener;
  contractorList = []
  @ViewChild('passwordScroll', { static: false }) passwordScrollElement: ElementRef;
  /*AutoComplete Addres input */
  formattedAddress = ''
  options = {
    componentRestrictions: {
      country: ['AU']
    }
  }
  map: any;
  lat: any;
  lng: any;
  pf_img: any;
  stafffPermissions: any;

  public handleAddressChange(address: any) {
    if (address) {
      this.formattedAddress = address.formatted_address;
      this.newStaff.get('address').setValue(this.formattedAddress);

      address.address_components.forEach(component => {
        if (component.types.includes('locality')) {
          this.newStaff.get('suburb').setValue(component.long_name);
        }
        if (component.types.includes('administrative_area_level_2')) {
          this.newStaff.get('city').setValue(component.long_name);
        }
        if (component.types.includes('administrative_area_level_1')) {
          this.newStaff.get('state').setValue(component.long_name);
        }
        if (component.types.includes('postal_code')) {
          this.newStaff.get('postal_code').setValue(component.long_name);
        }
      });

      this.lat = address.geometry.location.lat();
      this.lng = address.geometry.location.lng();
      var coordinate = this.lat + ',' + this.lng;
      this.newStaff.get('coordinates').setValue(coordinate);

    } else {
      this.newStaff.get('address').setValue('');
      this.newStaff.get('city').setValue('');
      this.newStaff.get('postal_code').setValue('');
      this.newStaff.get('state').setValue('');
      this.newStaff.get('suburb').setValue('');
      this.newStaff.get('coordinates').setValue('');
    }
  }
  filteredCountries: { id: string; name: string }[] = [];
  countries = [
    { id: "AFG", name: "AFGHANISTAN" },
    { id: "ALB", name: "ALBANIA" },
    { id: "DZA", name: "ALGERIA" },
    { id: "ASM", name: "AMERICAN SAMOA" },
    { id: "AND", name: "ANDORRA" },
    { id: "AGO", name: "ANGOLA" },
    { id: "AIA", name: "ANGUILLA" },
    { id: "ATA", name: "ANTARCTICA" },
    { id: "ATG", name: "ANTIGUA AND BARBUDA" },
    { id: "ARG", name: "ARGENTINA" },
    { id: "ARM", name: "ARMENIA" },
    { id: "ABW", name: "ARUBA" },
    { id: "AUS", name: "AUSTRALIA" },
    { id: "AUT", name: "AUSTRIA" },
    { id: "AZE", name: "AZERBAIJAN" },
    { id: "BHS", name: "BAHAMAS" },
    { id: "BHR", name: "BAHRAIN" },
    { id: "BGD", name: "BANGLADESH" },
    { id: "BRB", name: "BARBADOS" },
    { id: "BLR", name: "BELARUS" },
    { id: "BEL", name: "BELGIUM" },
    { id: "BLZ", name: "BELIZE" },
    { id: "BEN", name: "BENIN" },
    { id: "BMU", name: "BERMUDA" },
    { id: "BTN", name: "BHUTAN" },
    { id: "BOL", name: "BOLIVIA" },
    { id: "BES", name: "BONAIRE, SINT EUSTATIUS AND SABA" },
    { id: "BIH", name: "BOSNIA AND HERZEGOVINA" },
    { id: "BWA", name: "BOTSWANA" },
    { id: "BVT", name: "BOUVET ISLAND" },
    { id: "BRA", name: "BRAZIL" },
    { id: "IOT", name: "BRIT INDIAN OCN TERR" },
    { id: "BRN", name: "BRUNEI DARUSSALAM" },
    { id: "BGR", name: "BULGARIA" },
    { id: "BFA", name: "BURKINA FASO" },
    { id: "MMR", name: "MYANMAR" },
    { id: "BDI", name: "BURUNDI" },
    { id: "CPV", name: "CABO VERDE" },
    { id: "KHM", name: "CAMBODIA" },
    { id: "CMR", name: "CAMEROON" },
    { id: "CAN", name: "CANADA" },
    { id: "CYM", name: "CAYMAN ISLANDS" },
    { id: "CAF", name: "CENTRAL AFRICAN REPUBLIC" },
    { id: "TCD", name: "CHAD" },
    { id: "CHL", name: "CHILE" },
    { id: "CHN", name: "CHINA" },
    { id: "CXR", name: "CHRISTMAS ISLAND" },
    { id: "CCK", name: "COCOS (KEELING) ISL." },
    { id: "COL", name: "COLOMBIA" },
    { id: "COM", name: "COMOROS" },
    { id: "COG", name: "CONGO" },
    { id: "COD", name: "CONGO, DEMOCRATIC REPUBLIC OF THE" },
    { id: "COK", name: "COOK ISLANDS" },
    { id: "CRI", name: "COSTA RICA" },
    { id: "CIV", name: "COTE D'IVOIRE" },
    { id: "HRV", name: "CROATIA" },
    { id: "CUB", name: "CUBA" },
    { id: "CUW", name: "CURACAO" },
    { id: "CYP", name: "CYPRUS" },
    { id: "CZE", name: "CZECH REPUBLIC" },
    { id: "DNK", name: "DENMARK" },
    { id: "DJI", name: "DJIBOUTI" },
    { id: "DMA", name: "DOMINICA" },
    { id: "DOM", name: "DOMINICAN REPUBLIC" },
    { id: "ECU", name: "ECUADOR" },
    { id: "EGY", name: "EGYPT" },
    { id: "SLV", name: "EL SALVADOR" },
    { id: "GNQ", name: "EQUATORIAL GUINEA" },
    { id: "ERI", name: "ERITREA" },
    { id: "EST", name: "ESTONIA" },
    { id: "SWZ", name: "ESWATINI" },
    { id: "ETH", name: "ETHIOPIA" },
    { id: "FLK", name: "FALKLAND ISLANDS" },
    { id: "FRO", name: "FAROE ISLANDS" },
    { id: "FJI", name: "FIJI" },
    { id: "FIN", name: "FINLAND" },
    { id: "FRA", name: "FRANCE" },
    { id: "GUF", name: "FRENCH GUIANA" },
    { id: "PYF", name: "FRENCH POLYNESIA" },
    { id: "ATF", name: "FRENCH SOUTHERN TERRITORIES" },
    { id: "GAB", name: "GABON" },
    { id: "GMB", name: "GAMBIA" },
    { id: "GEO", name: "GEORGIA" },
    { id: "DEU", name: "GERMANY" },
    { id: "GHA", name: "GHANA" },
    { id: "GIB", name: "GIBRALTAR" },
    { id: "GRC", name: "GREECE" },
    { id: "GRL", name: "GREENLAND" },
    { id: "GRD", name: "GRENADA" },
    { id: "GLP", name: "GUADELOUPE" },
    { id: "GUM", name: "GUAM" },
    { id: "GTM", name: "GUATEMALA" },
    { id: "GGY", name: "GUERNSEY" },
    { id: "GIN", name: "GUINEA" },
    { id: "GNB", name: "GUINEA-BISSAU" },
    { id: "GUY", name: "GUYANA" },
    { id: "HTI", name: "HAITI" },
    { id: "HMD", name: "HEARD ISLAND AND MCDONALD ISLANDS" },
    { id: "VAT", name: "HOLY SEE" },
    { id: "HND", name: "HONDURAS" },
    { id: "HKG", name: "HONG KONG" },
    { id: "HUN", name: "HUNGARY" },
    { id: "ISL", name: "ICELAND" },
    { id: "IND", name: "INDIA" },
    { id: "IDN", name: "INDONESIA" },
    { id: "IRN", name: "IRAN" },
    { id: "IRQ", name: "IRAQ" },
    { id: "IRL", name: "IRELAND" },
    { id: "IMN", name: "ISLE OF MAN" },
    { id: "ISR", name: "ISRAEL" },
    { id: "ITA", name: "ITALY" },
    { id: "JAM", name: "JAMAICA" },
    { id: "JPN", name: "JAPAN" },
    { id: "JEY", name: "JERSEY" },
    { id: "JOR", name: "JORDAN" },
    { id: "KAZ", name: "KAZAKHSTAN" },
    { id: "KEN", name: "KENYA" },
    { id: "KIR", name: "KIRIBATI" },
    { id: "PRK", name: "NORTH KOREA" },
    { id: "KOR", name: "SOUTH KOREA" },
    { id: "KWT", name: "KUWAIT" },
    { id: "KGZ", name: "KYRGYZSTAN" },
    { id: "LAO", name: "LAO PEOPLE'S DEMOCRATIC REPUBLIC" },
    { id: "LVA", name: "LATVIA" },
    { id: "LBN", name: "LEBANON" },
    { id: "LSO", name: "LESOTHO" },
    { id: "LBR", name: "LIBERIA" },
    { id: "LBY", name: "LIBYA" },
    { id: "LIE", name: "LIECHTENSTEIN" },
    { id: "LTU", name: "LITHUANIA" },
    { id: "LUX", name: "LUXEMBOURG" },
    { id: "MAC", name: "MACAO" },
    { id: "MKD", name: "NORTH MACEDONIA" },
    { id: "MDG", name: "MADAGASCAR" },
    { id: "MWI", name: "MALAWI" },
    { id: "MYS", name: "MALAYSIA" },
    { id: "MDV", name: "MALDIVES" },
    { id: "MLI", name: "MALI" },
    { id: "MLT", name: "MALTA" },
    { id: "MHL", name: "MARSHALL ISLANDS" },
    { id: "MTQ", name: "MARTINIQUE" },
    { id: "MRT", name: "MAURITANIA" },
    { id: "MUS", name: "MAURITIUS" },
    { id: "MYT", name: "MAYOTTE" },
    { id: "MEX", name: "MEXICO" },
    { id: "FSM", name: "MICRONESIA" },
    { id: "MDA", name: "MOLDOVA" },
    { id: "MCO", name: "MONACO" },
    { id: "MNG", name: "MONGOLIA" },
    { id: "MNE", name: "MONTENEGRO" },
    { id: "MSR", name: "MONTSERRAT" },
    { id: "MAR", name: "MOROCCO" },
    { id: "MOZ", name: "MOZAMBIQUE" },
    { id: "NAM", name: "NAMIBIA" },
    { id: "NRU", name: "NAURU" },
    { id: "NPL", name: "NEPAL" },
    { id: "NLD", name: "NETHERLANDS" },
    { id: "NCL", name: "NEW CALEDONIA" },
    { id: "NZL", name: "NEW ZEALAND" },
    { id: "NIC", name: "NICARAGUA" },
    { id: "NER", name: "NIGER" },
    { id: "NGA", name: "NIGERIA" },
    { id: "NIU", name: "NIUE" },
    { id: "NFK", name: "NORFOLK ISLAND" },
    { id: "MNP", name: "NORTHERN MARIANA ISLANDS" },
    { id: "NOR", name: "NORWAY" },
    { id: "OMN", name: "OMAN" },
    { id: "PAK", name: "PAKISTAN" },
    { id: "PLW", name: "PALAU" },
    { id: "PSE", name: "PALESTINE" },
    { id: "PAN", name: "PANAMA" },
    { id: "PNG", name: "PAPUA NEW GUINEA" },
    { id: "PRY", name: "PARAGUAY" },
    { id: "PER", name: "PERU" },
    { id: "PHL", name: "PHILIPPINES" },
    { id: "PCN", name: "PITCAIRN" },
    { id: "POL", name: "POLAND" },
    { id: "PRT", name: "PORTUGAL" },
    { id: "PRI", name: "PUERTO RICO" },
    { id: "QAT", name: "QATAR" },
    { id: "REU", name: "REUNION" },
    { id: "ROU", name: "ROMANIA" },
    { id: "RUS", name: "RUSSIA" },
    { id: "RWA", name: "RWANDA" },
    { id: "BLM", name: "SAINT BARTHELEMY" },
    { id: "SHN", name: "SAINT HELENA" },
    { id: "KNA", name: "SAINT KITTS AND NEVIS" },
    { id: "LCA", name: "SAINT LUCIA" },
    { id: "MAF", name: "SAINT MARTIN" },
    { id: "SPM", name: "SAINT PIERRE AND MIQUELON" },
    { id: "VCT", name: "SAINT VINCENT AND THE GRENADINES" },
    { id: "WSM", name: "SAMOA" },
    { id: "SMR", name: "SAN MARINO" },
    { id: "STP", name: "SAO TOME AND PRINCIPE" },
    { id: "SAU", name: "SAUDI ARABIA" },
    { id: "SEN", name: "SENEGAL" },
    { id: "SRB", name: "SERBIA" },
    { id: "SYC", name: "SEYCHELLES" },
    { id: "SLE", name: "SIERRA LEONE" },
    { id: "SGP", name: "SINGAPORE" },
    { id: "SXM", name: "SINT MAARTEN" },
    { id: "SVK", name: "SLOVAKIA" },
    { id: "SVN", name: "SLOVENIA" },
    { id: "SLB", name: "SOLOMON ISLANDS" },
    { id: "SOM", name: "SOMALIA" },
    { id: "ZAF", name: "SOUTH AFRICA" },
    { id: "SGS", name: "SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS" },
    { id: "SSD", name: "SOUTH SUDAN" },
    { id: "ESP", name: "SPAIN" },
    { id: "LKA", name: "SRI LANKA" },
    { id: "SDN", name: "SUDAN" },
    { id: "SUR", name: "SURINAME" },
    { id: "SJM", name: "SVALBARD AND JAN MAYEN" },
    { id: "SWE", name: "SWEDEN" },
    { id: "CHE", name: "SWITZERLAND" },
    { id: "SYR", name: "SYRIA" },
    { id: "TWN", name: "TAIWAN" },
    { id: "TJK", name: "TAJIKISTAN" },
    { id: "TZA", name: "TANZANIA" },
    { id: "THA", name: "THAILAND" },
    { id: "TLS", name: "TIMOR-LESTE" },
    { id: "TGO", name: "TOGO" },
    { id: "TKL", name: "TOKELAU" },
    { id: "TON", name: "TONGA" },
    { id: "TTO", name: "TRINIDAD AND TOBAGO" },
    { id: "TUN", name: "TUNISIA" },
    { id: "TUR", name: "TURKEY" },
    { id: "TKM", name: "TURKMENISTAN" },
    { id: "TCA", name: "TURKS AND CAICOS ISLANDS" },
    { id: "TUV", name: "TUVALU" },
    { id: "UGA", name: "UGANDA" },
    { id: "UKR", name: "UKRAINE" },
    { id: "ARE", name: "UNITED ARAB EMIRATES" },
    { id: "GBR", name: "UNITED KINGDOM" },
    { id: "USA", name: "UNITED STATES" },
    { id: "UMI", name: "UNITED STATES MINOR OUTLYING ISLANDS" },
    { id: "URY", name: "URUGUAY" },
    { id: "UZB", name: "UZBEKISTAN" },
    { id: "VUT", name: "VANUATU" },
    { id: "VEN", name: "VENEZUELA" },
    { id: "VNM", name: "VIETNAM" },
    { id: "VGB", name: "VIRGIN ISLANDS, BRITISH" },
    { id: "VIR", name: "VIRGIN ISLANDS, U.S." },
    { id: "WLF", name: "WALLIS AND FUTUNA" },
    { id: "ESH", name: "WESTERN SAHARA" },
    { id: "YEM", name: "YEMEN" },
    { id: "ZMB", name: "ZAMBIA" },
    { id: "ZWE", name: "ZIMBABWE" }
  ];

  constructor(private fb: FormBuilder, public ngbActiveModal: NgbActiveModal,
    public dateAdapter: DateAdapter<Date>, public renderer2: Renderer2,
    private service: StaffService, private toast: ToastServiceService,
    private imgUpload: AdminService, private cus: CustomerService,
    public global: GlobalVariable, private server: ContractorService,
    private spinner: NgxSpinnerService, private permissionService: PermissionsService) {
    this.stafffPermissions = this.getStaffPermissions();
    this.dateAdapter.setLocale('en-AU');
    // this.setupScrollListener();

  }

  private getStaffPermissions() {
    const permissions = this.permissionService.getPermissionsByTitle('Onboarding');
    return permissions?.childPage?.find(item => item.title === 'Current Staff');
  }


  addEvent(type: string, event: MatDatepickerInputEvent<Date>) {
    this.date = moment(event.value).format('DD-MM-YYYY');
    this.newStaff.value.dob = this.date
  }


  customers: Customers[] = [];
  hide = true;
  isEdit: boolean = false;
  ngOnInit(): void {
    if (this.userData) {
      this.initializeFormWithUserData();
    } else {
      this.initializeEmptyForm();
    }

    this.newStaff
      .get('home_country')
      .valueChanges.pipe(startWith(''), map((value) => this._filter(value)))
      .subscribe((filteredCountries) => {
        this.filteredCountries = filteredCountries;
      });
  }

  private initializeFormWithUserData() {
    this.isEdit = true;
    this.checkGuardType(this.userData.guard_type)
    this.profileImage = this.userData.profile_image
    console.log('this.userData.dob', this.userData.joining_date);

    let dob = moment(this.userData.dob, ['YYYY-DD-MM', 'DD-MM-YYYY']);
    let joining_date = moment(this.userData.joining_date, ['YYYY-DD-MM', 'DD-MM-YYYY']);
    console.log('aftermoment: ', joining_date);

    const short = this.countries.find(item => item.id === this.userData.home_country)
    this.newStaff = this.fb.group({
      first_name: new FormControl(this.userData.first_name, [Validators.required, this.alphabetValidator]),
      middle_name: new FormControl(this.userData.middle_name),
      last_name: new FormControl(this.userData.last_name, Validators.required),
      email: new FormControl(this.userData.email, [Validators.required, Validators.email]),
      phone: new FormControl(this.userData.phone,),
      guard_type: new FormControl(this.userData.guard_type),
      guard_postion: new FormControl(this.userData.guard_postion),
      joining_date: new FormControl(joining_date.format(), [Validators.required, this.dateValidator()]),
      staff_type: new FormControl(this.userData.staff_type, Validators.required),
      state: new FormControl(this.userData.state, Validators.required),
      dob: new FormControl(dob.format(), [Validators.required, this.dateValidator()]),
      gender: new FormControl(this.userData.gender),
      suburb: new FormControl(this.userData.suburb, Validators.required),
      city: new FormControl(this.userData.city, Validators.required),
      coordinates: new FormControl(this.userData.coordinates, Validators.required),
      postal_code: new FormControl(this.userData.postal_code, Validators.required),
      emergency_contact_name: new FormControl(this.userData.emergency_contact_name, [this.alphabetValidator]),
      emergency_contact_phone: new FormControl(this.userData.emergency_contact_phone),
      emergency_contact_email: new FormControl(this.userData.emergency_contact_email, [Validators.email]),
      emergency_contact_relation: new FormControl(this.userData.emergency_contact_relation, [this.alphabetValidator]),
      address: new FormControl(this.userData.address, [Validators.required, this.customAddressValidator()]),
      home_country: new FormControl(short?.name),
      contractor_id: new FormControl(this.userData.contractor_id),
      password: new FormControl('', [this.passwordComplexityValidator()]),
      c_password: new FormControl(''),
    },
      {
        validators: this.passwordMatchValidator
      }
    )
  }


  private initializeEmptyForm() {
    this.isEdit = false;
    this.newStaff = this.fb.group({
      first_name: new FormControl('', [Validators.required, this.alphabetValidator]),
      middle_name: new FormControl(''),
      last_name: new FormControl('', Validators.required),
      email: new FormControl('', [Validators.required, Validators.email]),
      phone: new FormControl(''),
      guard_type: new FormControl(''),
      guard_postion: new FormControl(''),
      joining_date: ['', [Validators.required, this.dateValidator()]],
      staff_type: new FormControl('', Validators.required),
      state: new FormControl('', Validators.required),
      dob: new FormControl('', [Validators.required, this.dateValidator()]),
      gender: new FormControl(''),
      suburb: new FormControl('', Validators.required),
      city: new FormControl('', Validators.required),
      coordinates: new FormControl('', Validators.required),
      postal_code: new FormControl('', Validators.required),
      emergency_contact_name: new FormControl('', [this.alphabetValidator]),
      emergency_contact_phone: new FormControl(''),
      emergency_contact_email: new FormControl('', [Validators.email]),
      emergency_contact_relation: new FormControl('', [this.alphabetValidator]),
      address: new FormControl('', [Validators.required, this.customAddressValidator()]),
      profile_image: new FormControl(''),
      home_country: new FormControl(''),
      // annual_leave: new FormControl('', Validators.min(0)),
      // sick_leave: new FormControl('', Validators.min(0)),
      contractor_id: new FormControl(''),
      password: new FormControl('', [Validators.required, Validators.minLength(8), this.passwordComplexityValidator()]),
      c_password: new FormControl('', [Validators.required]),
    },
      {
        validators: this.passwordMatchValidator
      }
    )
  }


  ngAfterViewInit() {
    if (this.type === 'passwordScroll') {
      setTimeout(() => {
        if (this.passwordScrollElement && this.passwordScrollElement.nativeElement) {
          this.passwordScrollElement.nativeElement.scrollIntoView({ behavior: 'smooth' });
        }
      }, 0);
    }
  }

  // validatePhone(control: FormControl) {
  //   const pattern = /^61(?:2|3|4|7|8)\d{8}$/;
  //   if (control.value && !pattern.test(control.value)) {
  //     return { invalidPhone: true };
  //   }
  //   return null;
  // }

  dateValidator(): ValidatorFn {
    return (control: AbstractControl): { [key: string]: any } | null => {
      const selectedDate = control.value;
      const currentDate = new Date();
      if (selectedDate && selectedDate > currentDate) {
        return { dateInvalid: true };
      }
      return null;
    };
  }

  alphabetValidator(control: AbstractControl): ValidationErrors | null {
    const value = control.value;
    if (value && !/^[A-Za-z][A-Za-z0-9\s]*$/.test(value)) {
      return { alphabet: true };
    }
    return null;
  }

  customAddressValidator(): ValidatorFn {
    return (control: FormControl): { [key: string]: any } | null => {
      const value = control.value;
      if (!value) {
        return null;
      }
      const onlySpecialCharacters = /^[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]*$/.test(value);
      const onlyDigits = /^\d+$/.test(value);
      const onlyLetters = /^[a-zA-Z\s]*$/.test(value);
      if (onlySpecialCharacters || onlyDigits || onlyLetters) {
        return { invalidAddress: true };
      }
      return null;
    };
  }

  convertToTitleCase(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  getTabType(selected: string, el: HTMLElement) {
    this.tabType = selected;
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
    }
  }

  close(data?) {
    this.ngbActiveModal.dismiss(data);
  }

  uploadingImage = false;
  uploadProfile() {
    this.base64String = '';
    let input = document.createElement("input");
    input.type = "file";
    input.accept = "image/jpeg, image/png, image/jpg";
    input.onchange = (_) => {
      let files = Array.from(input.files);
      const file = files[0];
      this.uploadingImage = true;
      const reader = new FileReader();
      reader.onloadend = () => {
        this.base64String = (<string>reader.result).split(",")[1];
        this.profileImage = "data:image/jpeg;base64," + this.base64String;
        this.imgUpload.uploadImage(this.base64String, 'guard').subscribe(res => {
          if (res.success) {
            this.pf_img = res.path

          }
        }, (error) => {
          this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Error');
        },
          () => {
            this.uploadingImage = false; // Stop the loading indicator
          }
        );
      };
      reader.readAsDataURL(file);
    };
    input.click();
  }

  ///////gettting the validation error
  get f(): { [key: string]: AbstractControl } {
    return this.newStaff.controls;
  }
  submitted = false;


  ///////Create new Staff///////
  submitNewStaff() {
    this.spinner.show()
    this.submitted = true;
    this.markFormControlsAsTouched(this.newStaff);

    if (this.newStaff.invalid) {
      this.toast.toastNotification1('Please check error on field and fill this field', 'Invalid Form!');
      this.spinner.hide()
      return;
    }
    this.formatDateFields(this.newStaff, 'dob');
    this.formatDateFields(this.newStaff, 'joining_date');
    const short = this.countries.find(item => item.name === this.newStaff.value.home_country)
    this.newStaff.value.home_country = short?.id
    if (this.profileImage) {
      this.newStaff.value.profile_image = this.pf_img
    }

    const operationObservable = this.userData
      ? this.updateStaff(this.newStaff)
      : this.createNewStaff(this.newStaff);
    operationObservable.subscribe(
      (res) => {
        const status = 'Staff Operation';
        if (res.success == 'true' || res.success) {
          this.toast.toastNotification(res.message, status);
          this.close('QuickStaff');
        }
        else {
          this.toast.toastNotification1(res.message, 'Error');
        }
        this.spinner.hide();
      },
      (err) => {
        console.log("eror", err)
        this.toast.toastNotification1(err, 'Error');
        // console.log(error);
        this.spinner.hide();
      }
    );

  }

  updateStaff(newStaff: FormGroup): Observable<any> {
    this.newStaff.value.admin_id = this.global.admin.admin_id
    this.newStaff.value.id = this.userData.id
    return this.service.updateStaff(newStaff.value);
  }

  createNewStaff(newStaff: FormGroup): Observable<any> {
    this.newStaff.value.admin_id = this.global.admin.admin_id
    return this.service.createNewStaff(newStaff.value);
  }

  markFormControlsAsTouched(formGroup: FormGroup) {
    Object.values(formGroup.controls).forEach((control) => control.markAsTouched());
  }

  formatDateFields(formGroup: FormGroup, fieldName: string) {
    const field = formGroup.get(fieldName);
    if (field && field.value && field.value !== 'Invalid date') {
      field.setValue(moment(field.value).format('DD-MM-YYYY'));
    }
    else {
      field.setValue('');
    }
  }

  checkGuardType(value) {
    this.guardType = value
    if (value == 'contractor') {
      let params = {
        status: 'active',
      }
      this.server.getContractor(params)
        .subscribe(({ success, data }) => {
          if (success) {
            this.contractorList = data;
            const con_id = parseInt(this.userData.contractor_id, 10);
            if (this.userData) {
              this.newStaff.get('contractor_id').setValue(con_id)

            }
          }
        });
    }
  }

  private _filter(value: string): { id: string; name: string }[] {
    const filterValue = value.toLowerCase();
    return this.countries.filter((country) =>
      country.name.toLowerCase().includes(filterValue)
    );
  }

  passwordMatchValidator: ValidatorFn = (control: AbstractControl): ValidationErrors | null => {
    const newPassword = control.get('password');
    const c_password = control.get('c_password');
    if (newPassword && c_password && newPassword.value !== c_password.value) {
      c_password.setErrors({ passwordMismatch: true });
      return { passwordMismatch: true };
    }
    return null;
  };

  passwordComplexityValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value;
      if (!value) {
        return null;
      }
      const hasUpperCase = /[A-Z]+/.test(value);
      const hasSpecialChar = /[\W_]+/.test(value);
      const hasNumeric = /[0-9]+/.test(value);
      const isValid = hasUpperCase && hasSpecialChar && hasNumeric;
      return !isValid ? { passwordComplexity: true } : null;
    };
  }
}

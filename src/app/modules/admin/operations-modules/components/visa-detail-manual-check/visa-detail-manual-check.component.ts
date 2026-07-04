import { HttpClient } from '@angular/common/http';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { Router } from '@angular/router';
import { PermissionsService } from 'app/services/permissions.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { NgxSpinnerService } from 'ngx-spinner';
import { map, startWith } from 'rxjs/operators';
export class Customers {
  id: number;
  name: string;
}
interface ApiResponse {
  message: string;
  results: any[];
  status: string;
}

@Component({
  selector: 'app-visa-detail-manual-check',
  templateUrl: './visa-detail-manual-check.component.html',
  styleUrls: ['./visa-detail-manual-check.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush

})
export class VisaDetailManualCheckComponent implements OnInit {


  fieldColspan = 4;

  visaForm: FormGroup
  customers: Customers[] = [];
  guardsList: any = []
  fromMultiCustomer: string;
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

  showDetail: boolean = false

  results: any = []
  filteredCountries: { id: string; name: string }[] = [];
  staffInfo: any = []
  textNgx = 'Connecting to Online Immi Website 2s...'
  adminPermissions: any;
  singleStaff: any = {};

  constructor(private fb: FormBuilder, public userService: StaffService, public dateAdapter: DateAdapter<Date>,
    private spinner: NgxSpinnerService, private http: HttpClient, private cdr: ChangeDetectorRef, private global: GlobalVariable,
    private toast: ToastServiceService, private permissionService: PermissionsService, private router: Router) {
    const per = this.permissionService.getPermissionsByTitle('WFM Tools');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'New Entry/Query');
    console.log(this.adminPermissions);
    this.dateAdapter.setLocale('en-AU');
  }

  ngOnInit(): void {

    this.visaForm = this.fb.group({
      passport_number: ['', Validators.required],
      family_name: ['', Validators.required],
      select_country: ['', Validators.required],
      dob: ['', Validators.required],
      date_of_birth: [''],
      email: [''],
      password: [''],
    });

    this.getGuards()

    this.visaForm
      .get('select_country')
      .valueChanges.pipe(startWith(''), map((value) => this._filter(value)))
      .subscribe((filteredCountries) => {
        this.filteredCountries = filteredCountries;
      });
  }


  receiveDataFromChildGuardsSingle(data: any) {
    this.singleStaff = data
    this.userService.getSpecificStaffData(this.singleStaff.value.id).subscribe({
      next: ({ success, data }) => {
        if (success) {
          this.updateFormValues(data);
        }
      },
      error: (error) => {
        console.error('Failed to fetch staff data:', error);
      }
    });
  }

  private updateFormValues(data: any) {
    const formattedDOB = moment(data.dob, 'DD-MM-YYYY').format('YYYY-MM-DD');
    const selectedCountry = this.countries.find(country => country.id === data.home_country);
    this.visaForm.patchValue({
      dob: formattedDOB,
      family_name: this.singleStaff.value.name,
      passport_number: data.passport_number,
      select_country: selectedCountry || '',
    });
  }

  // get all guards
  getGuards() {
    let data = {
      guard_status: 'active',
      residence: 'citizen',
      pageIndex: 0,
      pageSize: 10000
    }
    this.userService.getStaff(data).subscribe(res => {
      if (res.success) {
        res.data.forEach(element => {
          element.name = element.first_name + ' ' + element.last_name
        });
        this.guardsList = res.data
      }
    })
  }

  convertDateFormat(inputDate: Date): string {
    const day = ('0' + inputDate.getDate()).slice(-2);
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const month = monthNames[inputDate.getMonth()];
    const year = inputDate.getFullYear();

    return `${day} ${month} ${year}`;
  }

  checkVisaInfo() {

    this.showDetail = false
    if (this.visaForm.invalid) {
      this.toast.toastNotification1('Please complete the form correctly', "Request Incomplete!");
      return;
    }

    if (!this.visaForm.value.passport_number) {
      this.toast.toastNotification1('Please enter your document number', "Request Incomplete!")
      return
    }

    if (!this.visaForm.value.family_name) {
      this.toast.toastNotification1('Family name can not be null', "Request Incomplete!")
      return
    }

    const dob = this.visaForm.get('dob').value ? this.convertDateFormat(new Date(this.visaForm.get('dob').value)) : '';
    this.visaForm.get('date_of_birth').setValue(dob);

    const apiKeys = this.global.admin?.apiKeys;
    const formValue = {
      ...this.visaForm.value,
      select_country: this.visaForm.value.select_country.id,
      email: apiKeys?.visa_mail,
      password: apiKeys?.visa_password
    };
    // Show spinner with initial message
    this.spinner.show();
    setTimeout(() => {
      this.textNgx = 'Connecting to Online Immi Website...'
    }, 2000);
    this.spinner.show();
    setTimeout(() => {
      this.textNgx = 'Establishing connection...'
    }, 1000);
    setTimeout(() => {
      this.textNgx = 'Searching Records...'
    }, 4000);
    this.textNgx = 'Retrieving Data...'


    // Make the API call
    this.http.post<ApiResponse>('https://apis.thescouts.com.au/api/guard/visaVarification', formValue).subscribe(response => {
      if (response.status == 'error') {
        this.spinner.hide();
        this.toast.toastNotification1('Something went wrong', "Request not complete")
      }
      else if (response.status) {
        this.showDetail = true
        setTimeout(() => {
          this.textNgx = response.message
        }, 4000);
        this.results = response.results;
        this.spinner.hide();
        this.cdr.markForCheck()
      }
    }, (error) => {
      this.toast.toastNotification1('Something went wrong', "Request not complete")
      this.spinner.hide()
    });
  }

  private _filter(value: any): { id: string; name: string }[] {
    let filterValue = '';
    if (typeof value === 'object' && value !== null && value.name) {
      filterValue = value.name.toLowerCase();
    } else if (typeof value === 'string') {
      filterValue = value.toLowerCase();
    }
    return this.countries.filter((country) =>
      country.name.toLowerCase().includes(filterValue)
    );
  }


  navigateToURL() {
    this.router.navigate(['/visacheck']);
  }


  displayFn(country: any): string {
    return country && country.name ? country.name : '';
  }

}

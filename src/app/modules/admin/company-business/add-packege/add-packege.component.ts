import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { fuseAnimations } from '@fuse/animations';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastServiceService } from 'app/services/toast-service.service';
import { compactNavigation } from 'app/mock-api/common/navigation/data';
import { FuseAlertType } from '@fuse/components/alert';
import { GlobalVariable } from 'app/shared/global';
import { BusinessServiceService } from '../business-service.service';
@Component({
  selector: 'app-add-packege',
  templateUrl: './add-packege.component.html',
  styleUrls: ['./add-packege.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: fuseAnimations
})
export class AddPackegeComponent implements OnInit {

  submitted: boolean = false;
  name: string = '';
  price: string = '';
  navigationItems = compactNavigation.map((item) => ({
    title: item.title,
    url: item.link,
    parentEnabled: false,
    childPage: item.childPage?.map((childPageTitle) => ({
      title: childPageTitle,
      childEnabled: false,
      ...(childPageTitle === 'Staff Roster/Scheduling' && {
        subChildPages: [
          { name: 'Select State', enabled: false, key: 'slect_state' },
          { name: 'Select Customer', enabled: false, key: 'select_customer' },
          { name: 'Select Sites', enabled: false, key: 'select_sites' },
          { name: 'Job Roster Filter', enabled: false, key: 'job_roster_filter' },
          { name: 'Un-published Site', enabled: false, key: 'un_published_site' },
          { name: 'Payroll', enabled: false, key: 'payroll', },
          { name: 'Job Instrcutions File', enabled: false, key: 'job_instructions_file', },
          { name: 'Coordinates', enabled: false, key: 'coordinates', },
          { name: 'Copy Shifts', enabled: false, key: 'copy_shifts', },
          { name: 'Clear Shifts', enabled: false, key: 'clear_week', },
          { name: 'Unpublish Week', enabled: false, key: 'unpublish_week', },
          { name: 'Unassign Week', enabled: false, key: 'unassign_week', },
          { name: 'Add Multiple Shifts', enabled: false, key: 'add_multiple_shifts', },
          { name: 'Sign In Detail', enabled: false, key: 'sign_in_detail', },
          { name: 'Sign Out Detail', enabled: false, key: 'sign_out_detail', },
          { name: 'Break Detail', enabled: false, key: 'break_detail', },
          { name: 'Green Call', enabled: false, key: 'green_call', },
          { name: 'Welfare Call', enabled: false, key: 'welfare_call', },
          { name: 'Tracker', enabled: false, key: 'tracker', },
          { name: 'Incident Report', enabled: false, key: 'incident_report', },
          { name: 'Shift Task', enabled: false, key: 'shift_task', },
          { name: 'Shift Activity', enabled: false, key: 'shift_activity', },
          { name: 'Operation Notes', enabled: false, key: 'operation_notes', },
          { name: 'Create Shift Button', enabled: false, key: 'create_shift_button', },
          { name: 'Shift Colors', enabled: false, key: 'shift_colors', },
          { name: 'Site Status', enabled: false, key: 'site_type', },
          { name: 'Site Trained', enabled: false, key: 'site_trained', },
          { name: 'Breaks', enabled: false, key: 'breaks', },
          { name: 'Site Hours', enabled: false, key: 'site_hours', },
          { name: 'Signin Radius', enabled: false, key: 'signin_radius', },
          { name: 'Radius Alert', enabled: false, key: 'radius_alert', },
          { name: 'Job Instrcutions', enabled: false, key: 'job_instructions', },
          { name: 'SOS Phone', enabled: false, key: 'sos_phone', },
          { name: 'Tasks', enabled: false, key: 'tasks', },
          { name: 'Start End Date', enabled: false, key: 'start_end_date', },
          { name: 'View By Staff', enabled: false, key: 'view_by_guards', },
          { name: 'Shift Site Name', enabled: false, key: 'shift_site_name', },
          { name: 'Shift Guard Name', enabled: false, key: 'shift_guard_name', },
          { name: 'Shift Available Guards', enabled: false, key: 'shift_available_guards', },
          { name: 'Shift Payable', enabled: false, key: 'shift_payable', },
          { name: 'Paid by', enabled: false, key: 'paid_by', },
          { name: 'Shift Start Time', enabled: false, key: 'shift_start_time', },
          { name: 'Shift End Time', enabled: false, key: 'shift_end_time', },
          { name: 'Travel Time', enabled: false, key: 'travel_time', },
          { name: 'Action', enabled: false, key: 'action', },
          { name: 'Publish', enabled: false, key: 'publish', },
          { name: 'Add Site', enabled: false, key: 'add_site', },
          { name: 'Ad-hoc Shift', enabled: false, key: 'ad_hoc_shift', },
          { name: 'Report', enabled: false, key: 'report', },
          { name: 'Add Guards', enabled: false, key: 'add_guards', },
          { name: 'Send SMS', enabled: false, key: 'send_sms', },
          { name: 'Send Email', enabled: false, key: 'send_email', },
          { name: 'Rollover this week', enabled: false, key: 'rollover_this_week', },
          { name: 'Shift Status', enabled: false, key: 'shift_status', },
          { name: 'Continuation', enabled: false, key: 'continuation', },
          { name: 'Break Management', enabled: false, key: 'break_management', },
          { name: 'Covid Marshal', enabled: false, key: 'covid_marshal', },
          { name: 'Custom Payrates', enabled: false, key: 'custom_payrates', },
          { name: 'Documents Bypass', enabled: false, key: 'documents_bypass', },
          { name: 'Charge Rates And Level', enabled: false, key: 'charge_rates_and_level', },
          { name: 'Copy this to Current Week', enabled: false, key: 'copy_this_to_current_week', },
          { name: 'Three Dots For Multiple Shifts', enabled: false, key: 'three_dots_for_multiple_shifts', },
          { name: 'Shift operational Notes icon and color', enabled: false, key: 'shift_operational_notes_icon_and_color', },
        ],
      }),
      ...(childPageTitle === 'Other Staff' && {
        subChildPages: [
          { name: 'Active Guards', enabled: false, key: 'active_guards', },
          { name: 'Inactive Guards', enabled: false, key: 'inactive_guards', },
          { name: 'New Guards', enabled: false, key: 'new_guards', },
          { name: 'Pending Guards', enabled: false, key: 'pending_guards', },
          { name: 'Deleted Guards', enabled: false, key: 'deleted_guards', },
          { name: 'Add Guards', enabled: false, key: 'add_guards', },
          { name: 'Guard Uniform', enabled: false, key: 'guard_uniform', },
          { name: 'Guard Work Limitation', enabled: false, key: 'guard_work_limitation', },
          { name: 'Leave Management', enabled: false, key: 'leave_management', },
          { name: 'Select Customer', enabled: false, key: 'select_customer', },
          { name: 'Gender', enabled: false, key: 'gender', },
          { name: 'Address', enabled: false, key: 'address', },
          { name: 'Dob', enabled: false, key: 'dob', },
          { name: 'Postal Code', enabled: false, key: 'postal_code', },
          { name: 'State', enabled: false, key: 'state', },
          { name: 'City', enabled: false, key: 'city', },
          { name: 'Suburb', enabled: false, key: 'suburb', },
          { name: 'Site Trained', enabled: false, key: 'site_trained', },
          { name: 'Guard Type', enabled: false, key: 'guard_type', },
          { name: 'Password', enabled: false, key: 'password', },
          { name: 'Email', enabled: false, key: 'email', },
          { name: 'Profile image', enabled: false, key: 'profile_image', },
          { name: 'Coordinates', enabled: false, key: 'coordinates', },
          { name: 'Include Casual Guards In Leave Management', enabled: false, key: 'include_casual_guards_in_leave_management', },
        ],
      }),
    })) || [],
  }));
  packege_id: number

  alert: { type: FuseAlertType; message: string } = {
    type: 'success',
    message: ''
  };
  showAlert: boolean = false;
  constructor(private toast: ToastServiceService, private changeDetect: ChangeDetectorRef,
    private route: ActivatedRoute, private global: GlobalVariable, private router: Router, private businessService: BusinessServiceService) { }

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      this.packege_id = params['id'];
      if (this.packege_id) {
        this.editPackege(this.packege_id)
      }
    });
  }

  onSaveButtonClick() {
    if (!this.name.trim() && !this.price.trim()) {
      this.apiError()
    }
    this.submitted = true
    let dataToSend = {
      name: this.name,
      price: this.price,
      permission: this.navigationItems,
      id: this.packege_id ?? null
    };
    if (this.packege_id) {
      this.businessService.update(dataToSend).subscribe(({ success, message }) => {
        this.onResponse(success, message)

      }, (error => {
        this.submitted = false
        this.toast.toastNotification1(this.global.apiError, 'Pricing Plan!')
      }))
    }
    else {
      this.businessService.store(dataToSend).subscribe(({ success, message }) => {
        this.onResponse(success, message)

      }, (error => {
        this.submitted = false

        this.toast.toastNotification1(this.global.apiError, 'Pricing Plan!')
      }))
    }

  }

  editPackege(id) {
    let data = {
      id: id
    };

    this.businessService.edit(data).subscribe(({ success, data }) => {
      if (success) {
        this.navigationItems.forEach(parentKey => {
          const matchingPermission = data.permission.find(resKey => resKey.title === parentKey.title);
          if (matchingPermission) {
            parentKey.parentEnabled = matchingPermission.parentEnabled;

            if (matchingPermission.childPage) {
              parentKey.childPage?.forEach(childKey => {
                const matchingChildPermission = matchingPermission.childPage.find(resChildKey => resChildKey.title === childKey.title);
                if (matchingChildPermission) {
                  childKey.childEnabled = matchingChildPermission.childEnabled;

                  if (matchingChildPermission.subChildPages) {
                    childKey.subChildPages?.forEach(subChildKey => {
                      const matchingSubChildPermission = matchingChildPermission.subChildPages.find(resSubChildKey => resSubChildKey.name === subChildKey.name);
                      if (matchingSubChildPermission) {
                        subChildKey.enabled = matchingSubChildPermission.enabled;
                      }
                    });
                  }
                }
              });
            }
          }
        });
        this.price = data.price;
        this.name = data.name;
      }
      this.changeDetect.markForCheck();
    });
  }


  onResponse(success, message) {
    if (success) {
      this.submitted = false
      this.toast.toastNotification(message, 'Pricing Plan!')
      this.router.navigate(['company-business']);
    }
    else {
      this.toast.toastNotification1(message, 'Pricing Plan!')
    }
  }

  apiError() {
    this.showAlert = true;
    this.alert.type = 'error';
    this.alert.message = 'Packege Name and Packege Price are required.';
    return
  }
}

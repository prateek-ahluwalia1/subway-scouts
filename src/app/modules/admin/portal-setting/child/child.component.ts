import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { PermissionsService } from 'app/services/permissions.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

@Component({
  selector: 'app-child',
  templateUrl: './child.component.html',
  styleUrls: ['./child.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ChildComponent implements OnInit {


  users = [
    // { name: 'Company Profile', text: 'It is used to create company profile.', url: 'company-profile', icon: 'settings_suggest', image: '/assets/images/nav-images/api_settings.png', enabled: false },
    { name: 'Public Holidays', text: 'It is used to create public holidays.', url: 'ph-setting', icon: 'settings_suggest', image: '/assets/images/nav-images/roles_and_permissions.png', enabled: false },
    { name: 'Roles & Permissions', text: 'It is used to assign permissions to the created roles.', url: 'role-and-permissions', icon: 'settings_suggest', image: '/assets/images/nav-images/roles_and_permissions.png', enabled: false },
    { name: 'Color Settings', text: 'Used to customise the colors of the portal.', url: 'color-setting', icon: 'settings_suggest', image: '/assets/images/nav-images/colorSettings.jpg', enabled: false },
    // { name: 'Api Settings', text: 'Configurations to manage and control API functionality.', url: 'api-setting', icon: 'settings_suggest', image: '/assets/images/nav-images/api_settings.png', enabled: false },
  ]

  adminPermissions
  routeId
  constructor(public router: Router, private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService) {
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
    this.adminPermissions = this.permissionService.getPermissionsByTitle('Settings');
    for (const user of this.users) {
      const matchingChild = this.adminPermissions?.childPage?.find(child => child.title === user.name);
      if (matchingChild) {
        user.enabled = matchingChild.enabled;
      } else {
        user.enabled = false;
      }
    }
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Portal Settings Page', 'Exit Portal Settings Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Portal Settings Page', 'Enter in Portal Settings Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }


}

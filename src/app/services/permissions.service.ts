import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class PermissionsService {
  private permissions: Record<string, any>[] = [];
  constructor() {


  }

  getPermissionsByTitle(title: string): any | undefined {
    this.permissions = []
    const mainPermission = JSON.parse(localStorage.getItem('role_permissions'));
    if (mainPermission && mainPermission.permissions) {
      this.permissions = JSON.parse(mainPermission.permissions);
    }
    return this.permissions.find((perm) => perm.title === title);
  }

  getPermissionsByUrl(url: string): any | undefined {
    this.permissions = []
    const mainPermission = JSON.parse(localStorage.getItem('role_permissions'));
    if (mainPermission && mainPermission.permissions) {
      this.permissions = JSON.parse(mainPermission.permissions);
    }
    return this.permissions.find((perm) => perm.url === url);
  }
}

import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'searchStaff'
})
export class SearchStaffPipe implements PipeTransform {

  transform(staffList: any[], searchLocation: string): unknown {
    if (!staffList || !searchLocation) {
      return staffList;
    }

    // Convert the search string to lowercase for case-insensitive matching
    searchLocation = searchLocation.toLowerCase();

    return staffList.filter((staff) => {
      const fullName = `${staff.first_name} ${staff.middle_name ? staff.middle_name + ' ' : ''}${staff.last_name}`;
      const fullNameLowerCase = fullName.toLowerCase();

      // Check if the searchLocation is present in the full name
      return fullNameLowerCase.includes(searchLocation);
    });
  }

}

import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'time'
})
export class TimePipe implements PipeTransform {
  transform(dateTimeString: string): string {
    if (!dateTimeString) return '';
    
    // Split the datetime string by space to separate date and time
    const parts = dateTimeString.split(' ');

    if (parts.length > 1) {
      // If there is a time portion, return it
      return parts[1];
    } else {
      return '';
    }
  }
}

import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'siteFilter'
})
export class siteFilterPipe implements PipeTransform {

  transform(items: any[], searchTerm: string): any[] {
    if (!items || !searchTerm) {
      return items;
    }
    searchTerm = searchTerm.toLowerCase();
    return items.filter(item =>
      item.site_name.toLowerCase().includes(searchTerm) ||
      (item.email && item.email.toLowerCase().includes(searchTerm))
    );
  }
}

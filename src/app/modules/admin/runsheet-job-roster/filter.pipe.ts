import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'filter'
})
export class FilterPipe implements PipeTransform {

    transform(items: any[], searchTerm: string): any[] {
        if (!items || !searchTerm) {
            return items;
        }
        searchTerm = searchTerm.toLowerCase();
        return items.filter(item =>
            item.first_name.toLowerCase().includes(searchTerm) || item.middle_name.toLowerCase().includes(searchTerm) ||
            item.last_name.toLowerCase().includes(searchTerm) ||
            (item.middle_name && item.middle_name.toLowerCase().includes(searchTerm))
        );
    }
}

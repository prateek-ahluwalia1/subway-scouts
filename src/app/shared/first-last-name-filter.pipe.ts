import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'firstLastNamefilter'
})
export class firstLastNameFilterPipe implements PipeTransform {

    transform(items: any[], searchTerm: string): any[] {
        if (!items || !searchTerm) {
            return items;
        }
        searchTerm = searchTerm.toLowerCase();
        return items.filter(item =>
            item.first_name.toLowerCase().includes(searchTerm) ||
            item.last_name.toLowerCase().includes(searchTerm)
        );
    }
}

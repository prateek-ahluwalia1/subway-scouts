import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'tableColumnExcludeSelect'
})
export class TableColumnExcludeSelectPipe implements PipeTransform {

    transform(value: string[], args?: string): any {
        console.log('value', value);
        console.log('args', args);
        return value.filter(item => item !== args)

    }

}
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'decimalFormat'
})
export class DecimalFormatPipe implements PipeTransform {
  transform(value: number): string {
    if (isNaN(value)) {
      return '0.00';
    }
    return value.toFixed(2);
  }
}

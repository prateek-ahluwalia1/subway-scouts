import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'absNumber'
})
export class AbsNumberPipe implements PipeTransform {
    transform(value: number): number {
        return Math.abs(value);
    }
}

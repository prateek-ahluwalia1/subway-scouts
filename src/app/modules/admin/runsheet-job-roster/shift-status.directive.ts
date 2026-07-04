import { Directive, Input, ElementRef, Renderer2, HostBinding } from '@angular/core';

@Directive({
    selector: '[appShiftStatus]'
})
export class ShiftStatusDirective {
    @Input() set job(job: any) {
        this.renderer.removeClass(this.el.nativeElement, 'select-shift');
        this.renderer.removeClass(this.el.nativeElement, 'copy-shift');
        this.renderer.removeClass(this.el.nativeElement, 'publish-shifts');
        this.renderer.removeClass(this.el.nativeElement, 'guard-shift');
        this.renderer.removeClass(this.el.nativeElement, 'unpublish-shift');
        this.renderer.removeClass(this.el.nativeElement, 'pending-shift');
        this.renderer.removeClass(this.el.nativeElement, 'complete-shift');
        this.renderer.removeClass(this.el.nativeElement, 'rejected-shift');
        this.renderer.removeClass(this.el.nativeElement, 'missed-shift');
        this.renderer.removeClass(this.el.nativeElement, 'confirmed-shift');
        this.renderer.removeClass(this.el.nativeElement, 'mock-shift');
        this.renderer.removeClass(this.el.nativeElement, 'uncoverd-shifts');

        if (job.status === 'pending' && job.first_name && job.publish_status === 0) {
            this.renderer.addClass(this.el.nativeElement, 'guard-shift');
        } else if (job.status === 'pending' && job.first_name === null && !job.unprofile_name) {
            this.renderer.addClass(this.el.nativeElement, 'pending-shift');
        } else if (job.status === 'completed') {
            this.renderer.addClass(this.el.nativeElement, 'complete-shift');
        } else if (job.status === 'rejected') {
            this.renderer.addClass(this.el.nativeElement, 'rejected-shift');
        } else if (job.status === 'missed') {
            this.renderer.addClass(this.el.nativeElement, 'missed-shift');
        } else if (job.status === 'confirmed') {
            this.renderer.addClass(this.el.nativeElement, 'confirmed-shift');
        }
        else if (job.publish_status === 1) {
            this.renderer.addClass(this.el.nativeElement, 'publish-shift');
        }
        else if (job.unprofile_name) {
            this.renderer.addClass(this.el.nativeElement, 'mock-shift');
        }
    }

    constructor(private el: ElementRef, private renderer: Renderer2) { }
}

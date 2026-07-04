import { Component, OnInit, Renderer2, ViewChild, ElementRef, Input } from '@angular/core';
import { ToasterService } from './toaster.service';
import { trigger, state, style, animate, transition, } from '@angular/animations';

@Component({
  selector: 'toaster-message',
  templateUrl: './toaster.component.html',
  styleUrls: ['./toaster.component.scss'],
  animations: [
    
    trigger('slideIn', [
      state('flyIn', style({ 
        opacity: 1,
        transform: 'translateX(0)'
      })),
      transition(':enter', [
        style({ transform: 'translateX(20px)', opacity: 0, }),
        animate('0.2s 150ms ease-in')
      ])
    ])
  ]
})
export class ToasterComponent implements OnInit {
  @ViewChild('toasterWrapper') toasterWrapper: ElementRef
  @Input() message: string;
  @Input() title: string;
  constructor(
    private toasterService: ToasterService,
    private renderer: Renderer2
    ) { 
  }

  ngOnInit() {
  }

  showToast(){
    const message = 'This is a custom toast message.';
    const title = "Custom Toast";
    this.toasterService.dispatchToaster(message,title);
   
  }

  closeToast(){
    this.toasterService.dismissToaster();
  }

}
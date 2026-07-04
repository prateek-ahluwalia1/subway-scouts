import { Injectable } from '@angular/core';
import {
  ToastNotificationInitializer,
  DialogLayoutDisplay,
  DisappearanceAnimation,
  AppearanceAnimation,
  ToastPositionEnum,
  ConfirmBoxInitializer,
  ToastUserViewTypeEnum,
} from '@costlydeveloper/ngx-awesome-popup';
@Injectable({
  providedIn: 'root'
})
export class ToastServiceService {

  constructor() { }

  toasts: any[] = [];

  toastNotification(msg, status, succ?) {
    console.log(msg, '1', status);

    const newToastNotification = new ToastNotificationInitializer();

    newToastNotification.setTitle(status);
    newToastNotification.setMessage(msg);

    // Choose layout color type
    newToastNotification.setConfig({
      autoCloseDelay: 5000, // optional
      textPosition: 'right', // optional
      // TOP_LEFT | TOP_CENTER | TOP_RIGHT | TOP_FULL_WIDTH | BOTTOM_LEFT | BOTTOM_CENTER | BOTTOM_RIGHT | BOTTOM_FULL_WIDTH
      toastUserViewType: ToastUserViewTypeEnum.STANDARD, // STANDARD | SIMPLE
      layoutType: DialogLayoutDisplay.CUSTOM_TWO, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.ZOOM_IN, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.FLIP_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      // TOP_LEFT | TOP_CENTER | TOP_RIGHT | TOP_FULL_WIDTH | BOTTOM_LEFT | BOTTOM_CENTER | BOTTOM_RIGHT | BOTTOM_FULL_WIDTH
      toastPosition: ToastPositionEnum.TOP_RIGHT,
      allowHtmlMessage: true,
      customStyles: {
        titleCSS: 'color: #ffffff; font-size: 14px;background: var(--color-primary);',
        textCSS: 'color: #fffff; font-size: 16px;',}
        // customStyles: {
        //  titleCSS: 'color: #ddd; background: #333; font-size: 20px; padding: 20px',
        //  buttonSectionCSS: 'background: #333',
        //  buttonCSS: 'font-size: 14px;',
        //  textCSS: 'color: #ddd; font-size: 16px; background: #333;',
        //  wrapperCSS: 'background: #333;'
        //   }
    });

    // Simply open the popup
    newToastNotification.openToastNotification$();
  }
  toastNotification1(msg, status) {
    console.log(msg, '1', status);

    const newToastNotification = new ToastNotificationInitializer();

    newToastNotification.setTitle(status);
    newToastNotification.setMessage(msg);

    // Choose layout color type
    newToastNotification.setConfig({
      autoCloseDelay: 5000, // optional
      textPosition: 'right', // optional
      layoutType: DialogLayoutDisplay.DANGER, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.ZOOM_IN, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.ZOOM_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      // TOP_LEFT | TOP_CENTER | TOP_RIGHT | TOP_FULL_WIDTH | BOTTOM_LEFT | BOTTOM_CENTER | BOTTOM_RIGHT | BOTTOM_FULL_WIDTH
      toastPosition: ToastPositionEnum.TOP_RIGHT,
      allowHtmlMessage: true,
      disableIcon: true,
    });

    // Simply open the popup
    newToastNotification.openToastNotification$();
  }

}

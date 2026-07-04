import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class FullScreenService {

  private isFullscreen = false;

  constructor() { }

  toggleFullscreen(element: HTMLElement) {
    if (!this.isFullscreen) {
      this.enterFullscreen(element);
    } else {
      this.exitFullscreen();
    }
  }

  enterFullscreen(element: HTMLElement) {
    if (element.requestFullscreen) {
      element.requestFullscreen();
    }
    this.isFullscreen = true;
  }

  exitFullscreen() {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    }
    this.isFullscreen = false;
  }

  isFullScreen() {
    return this.isFullscreen;
  }
}

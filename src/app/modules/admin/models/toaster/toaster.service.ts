import { Injectable, Inject, ComponentFactoryResolver, ReflectiveInjector } from '@angular/core';
import { DOCUMENT } from '@angular/common';
import { ToasterComponent } from './toaster.component';

@Injectable()
export class ToasterService {
  factoryResolver;
  rootViewContainer;

  constructor(
    @Inject(ComponentFactoryResolver) factoryResolver,
    @Inject(DOCUMENT) private document: Document
  ) { 
    this.factoryResolver = factoryResolver
  }

  setRootViewContainerRef(viewContainerRef) {
    this.rootViewContainer = viewContainerRef
  }

  dispatchToaster(message: string,title: string){
   const factory = this.factoryResolver.resolveComponentFactory(ToasterComponent)
   const component = factory.create(this.rootViewContainer.parentInjector)
   component.instance.message = message;
   component.instance.title = title;
   this.rootViewContainer.detach();
   this.rootViewContainer.insert(component.hostView)
  }

  dismissToaster(){
    this.rootViewContainer.detach()
  }

}
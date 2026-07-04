import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { ExtraOptions, PreloadAllModules, RouterModule } from '@angular/router';
import { MarkdownModule } from 'ngx-markdown';
import { FuseModule } from '@fuse';
import { FuseConfigModule } from '@fuse/services/config';
import { FuseMockApiModule } from '@fuse/lib/mock-api';
import { CoreModule } from 'app/core/core.module';
import { appConfig } from 'app/core/config/app.config';
import { mockApiServices } from 'app/mock-api';
import { LayoutModule } from 'app/layout/layout.module';
import { AppComponent } from 'app/app.component';
import { appRoutes } from 'app/app.routing';
import { GlobalVariable } from "./shared/global";
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { AddDocumentComponent } from './modules/admin/models/add-document/add-document.component';
import { DatePipe } from '@angular/common';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';
import { LocationStrategy, HashLocationStrategy } from '@angular/common';
import {
  ConfirmBoxConfigModule, DialogConfigModule, NgxAwesomePopupModule, ToastNotificationConfigModule,
} from '@costlydeveloper/ngx-awesome-popup';
import { EmailVerifyComponent } from './modules/auth/email-verify/email-verify.component';
import { MaterialModule } from './shared/material.module';
import { NgxSpinnerModule } from 'ngx-spinner';
import { ToasterService } from './modules/admin/models/toaster/toaster.service';
import { ToasterComponent } from './modules/admin/models/toaster/toaster.component';
import {
  IPublicClientApplication,
  PublicClientApplication,
  BrowserCacheLocation,
  LogLevel,
  InteractionType,
  BrowserUtils,
} from '@azure/msal-browser';
import {
  MSAL_INSTANCE,
  MSAL_INTERCEPTOR_CONFIG,
  MsalInterceptorConfiguration,
  MSAL_GUARD_CONFIG,
  MsalGuardConfiguration,
  MsalService,
  MsalGuard,
  MsalRedirectComponent,
  MsalModule,
  MsalInterceptor,
} from '@azure/msal-angular';
import { GraphService } from './services/graph.service';

const GRAPH_ENDPOINT = 'https://graph.microsoft.com/v1.0/me';

const isIE =
  window.navigator.userAgent.indexOf('MSIE ') > -1 ||
  window.navigator.userAgent.indexOf('Trident/') > -1;

export function MSALInstanceFactory(): IPublicClientApplication {
  const isLocal = window.location.hostname === 'localhost';
  const redirectUri = isLocal ? 'http://localhost:4200/' : 'https://app.thescouts.com.au/';

  return new PublicClientApplication({
    auth: {
      clientId: '7b6f791b-eac5-449b-96cf-0e4c83682fe1',
      authority: 'https://login.microsoftonline.com/common',
      redirectUri: redirectUri,
    },
    cache: {
      cacheLocation: BrowserCacheLocation.LocalStorage,
      storeAuthStateInCookie: isIE, // set to true for IE 11
    },
    system: {
      loggerOptions: {
        logLevel: LogLevel.Info,
        piiLoggingEnabled: false,
      },
    },
  });
}

export function MSALInterceptorConfigFactory(): MsalInterceptorConfiguration {
  const protectedResourceMap = new Map<string, Array<string>>();
  protectedResourceMap.set(GRAPH_ENDPOINT, ['User.Read', 'Mail.ReadBasic', 'Mail.Read', 'Mail.Send', 'Mail.ReadWrite.Shared', 'Mail.ReadWrite', 'MailboxSettings.ReadWrite']);

  return {
    interactionType: InteractionType.Redirect,
    protectedResourceMap,
  };
}

export function MSALGuardConfigFactory(): MsalGuardConfiguration {
  return {
    interactionType: InteractionType.Redirect,
    authRequest: {
      scopes: ['user.read'],
    },
  };
}
const routerConfig: ExtraOptions = {
  preloadingStrategy: PreloadAllModules, scrollPositionRestoration: 'enabled',
  initialNavigation:
    !BrowserUtils.isInIframe() && !BrowserUtils.isInPopup()
      ? 'enabledNonBlocking'
      : 'disabled',
};

@NgModule({
  declarations: [
    AppComponent,
    AddDocumentComponent,
    EmailVerifyComponent,
    ToasterComponent,
  ],
  imports: [
    MaterialModule,
    FormsModule,
    ReactiveFormsModule,
    BrowserModule,
    BrowserAnimationsModule,
    RouterModule.forRoot(appRoutes, routerConfig),
    // Fuse, FuseConfig & FuseMockAPI
    FuseModule,
    FuseConfigModule.forRoot(appConfig),
    FuseMockApiModule.forRoot(mockApiServices),
    GlobalVariable,
    DatePipe,
    // Core module of your application
    CoreModule,

    // Layout module of your application
    LayoutModule,

    // 3rd party modules that require global configuration via forRoot
    MarkdownModule.forRoot({}),
    NgbNavModule,
    HttpClientModule,

    NgxAwesomePopupModule.forRoot({
      colorList: {
        success: '#3caea3', // optional
        info: '#2f8ee5', // optional
        warning: '#ffc107', // optional
        danger: '#e46464', // optional
        customOne: '#3ebb1a', // optional
        customTwo: '#01A37E', // optional (up to custom five)
      },
    }),
    ToastNotificationConfigModule.forRoot(),

    DialogConfigModule.forRoot(), // optional
    ConfirmBoxConfigModule.forRoot(), // optional
    NgxSpinnerModule,
    MsalModule
  ],
  bootstrap: [
    AppComponent, MsalRedirectComponent
  ],
  providers: [MatDatepickerModule, DatePipe, { provide: LocationStrategy, useClass: HashLocationStrategy },
    ToasterService,
    {
      provide: HTTP_INTERCEPTORS,
      useClass: MsalInterceptor,
      multi: true,
    },
    {
      provide: MSAL_INSTANCE,
      useFactory: MSALInstanceFactory,
    },
    {
      provide: MSAL_GUARD_CONFIG,
      useFactory: MSALGuardConfigFactory,
    },
    {
      provide: MSAL_INTERCEPTOR_CONFIG,
      useFactory: MSALInterceptorConfigFactory,
    },
    MsalService,
    GraphService,
  ]
})
export class AppModule { }

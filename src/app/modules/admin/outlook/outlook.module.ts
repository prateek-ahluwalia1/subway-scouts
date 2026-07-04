import { NgModule } from '@angular/core';
import { OutlookComponent } from './outlook.component';
import { Route, RouterModule } from '@angular/router';
import { TruncatePipe } from './truncate.pipe';
import { AdminModule } from '../admin.module';

import { MSAL_GUARD_CONFIG, MSAL_INSTANCE, MSAL_INTERCEPTOR_CONFIG, MsalBroadcastService, MsalGuard, MsalGuardConfiguration, MsalInterceptor, MsalInterceptorConfiguration, MsalModule, MsalRedirectComponent, MsalService } from '@azure/msal-angular';
import { BrowserCacheLocation, IPublicClientApplication, InteractionType, LogLevel, PublicClientApplication } from '@azure/msal-browser';
import { HTTP_INTERCEPTORS } from '@angular/common/http';
import { FuseCardModule } from '@fuse/components/card';

// Azure code
const isIE = window.navigator.userAgent.indexOf("MSIE ") > -1 || window.navigator.userAgent.indexOf("Trident/") > -1;

export function loggerCallback(logLevel: LogLevel, message: string) {
  console.log(logLevel);
  console.log(message);
}
export function MSALInstanceFactory(): IPublicClientApplication {
  const isLocal = window.location.hostname === 'localhost';
  console.log(isLocal);
  const redirectUri = isLocal ? 'http://localhost:4200/' : 'https://app.thescouts.com.au/';
  let type = localStorage.getItem('type')
  let authOptions;

  if (type == 'business') {
    authOptions = {
      clientId: '8e9c18f9-0a01-4050-8aa8-d7220ac6fe0c',
      authority: 'https://login.microsoftonline.com/organizations',
      redirectUri: redirectUri,
      navigateToLoginRequestUrl: false,
    };

  } else {
    authOptions = {
      clientId: 'f965637c-8d3c-4a96-be3d-2537c1ded042',
      authority: 'https://login.microsoftonline.com/consumers',
      redirectUri: redirectUri,
      navigateToLoginRequestUrl: false,
    };
  }

  return new PublicClientApplication({
    auth: authOptions,
    cache: {
      cacheLocation: BrowserCacheLocation.LocalStorage,
      storeAuthStateInCookie: isIE, // set to true for IE 11
    },
    system: {
      loggerOptions: {
        loggerCallback,
        logLevel: LogLevel.Info,
        piiLoggingEnabled: false,
      },
      tokenRenewalOffsetSeconds: 300,
    }
  });
}

export function MSALInterceptorConfigFactory(): MsalInterceptorConfiguration {
  const protectedResourceMap = new Map<string, Array<string>>();
  //add permissions here
  protectedResourceMap.set('https://graph.microsoft.com/v1.0/me', ['User.Read', 'Mail.ReadBasic', 'Mail.Read', 'Mail.Send', 'Mail.ReadWrite.Shared', 'Mail.ReadWrite','MailboxSettings.ReadWrite']);

  return {
    interactionType: InteractionType.Redirect,
    protectedResourceMap
  };
}

export function MSALGuardConfigFactory(): MsalGuardConfiguration {
  return {
    interactionType: InteractionType.Redirect,
    authRequest: {
      scopes: ['user.read']
    }
  };
}

// end here

const exampleRoutes: Route[] = [
  {
    path: '',
    component: OutlookComponent
  }
]
@NgModule({
  declarations: [
    OutlookComponent, TruncatePipe
  ],
  imports: [
    RouterModule.forChild(exampleRoutes), AdminModule, MsalModule, FuseCardModule
  ],
  bootstrap: [
    MsalRedirectComponent
  ],
  providers: [,
    {
      provide: HTTP_INTERCEPTORS,
      useClass: MsalInterceptor,
      multi: true
    },

    {
      provide: MSAL_INSTANCE,
      useFactory: MSALInstanceFactory
    },
    {
      provide: MSAL_GUARD_CONFIG,
      useFactory: MSALGuardConfigFactory
    },
    {
      provide: MSAL_INTERCEPTOR_CONFIG,
      useFactory: MSALInterceptorConfigFactory
    },
    MsalService,
    MsalGuard,
    MsalBroadcastService,],
})
export class OutlookModule { }

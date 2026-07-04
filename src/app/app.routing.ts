import { Route } from '@angular/router';
import { AuthGuard } from 'app/core/auth/guards/auth.guard';
import { NoAuthGuard } from 'app/core/auth/guards/noAuth.guard';
import { LayoutComponent } from 'app/layout/layout.component';
import { InitialDataResolver } from 'app/app.resolvers';


// @formatter:off
/* eslint-disable max-len */
/* eslint-disable @typescript-eslint/explicit-function-return-type */
export const appRoutes: Route[] = [

    // Redirect empty path to '/example'
    { path: '', pathMatch: 'full', redirectTo: 'dashboard' },


    // Redirect signed in user to the '/example'
    // After the user signs in, the sign in page will redirect the user to the 'signed-in-redirect'
    // path. Below is another redirection for that path to redirect the user to the desired
    // location. This is a small convenience to keep all main routes together here on this file.
    // { path: 'signed-in-redirect', pathMatch: 'full', redirectTo: 'dashboard' },


    {
        path: '',
        component: LayoutComponent,
        canActivate: [AuthGuard],
        canActivateChild: [AuthGuard],
        data: {
            layout: 'empty'
        },
        children: [
            { path: 'form/:id', loadChildren: () => import('app/modules/admin/form/form.module').then(m => m.FormModule) },
            { path: 'built-in/check-list', loadChildren: () => import('app/modules/admin/check-list-form/check-list-form.module').then(m => m.CheckListFormModule) },
            { path: 'built-in/employee-details', loadChildren: () => import('app/modules/admin/employee-details/employee-details.module').then(m => m.EmployeeDetailsModule) },
            { path: 'built-in/uniform-form', loadChildren: () => import('app/modules/admin/uniform-form/uniform-form.module').then(m => m.UniformFormModule) },
            { path: 'built-in/emergency-contact', loadChildren: () => import('app/modules/admin/emergency-contact/emergency-contact.module').then(m => m.EmergencyContactModule) },
            { path: 'built-in/refrences-form', loadChildren: () => import('app/modules/admin/refrences-form/refrences-form.module').then(m => m.RefrencesFormModule) },
            { path: 'built-in/personal-refrence-form', loadChildren: () => import('app/modules/admin/personal-refrence-form/personal-refrence-form.module').then(m => m.PersonalRefrenceFormModule) },
            { path: 'business-login', loadChildren: () => import('app/modules/admin/login-page/login-page.module').then(m => m.LoginPageModule) },
            // { path: 'business-list', loadChildren: () => import('app/modules/admin/business-list/business-list.module').then(m => m.BusinessListModule) },
            // { path: 'packege', loadChildren: () => import('app/modules/admin/business-settings/business-settings.module').then(m => m.BusinessSettingsModule) },
            // { path: 'packege/:id', loadChildren: () => import('app/modules/admin/business-settings/business-settings.module').then(m => m.BusinessSettingsModule) },
            {
                path: 'company-business',
                loadChildren: () => import('app/modules/admin/company-business/company-business.module').then(m => m.CompanyBusinessModule)
            },
        ]
    },

    // Auth routes for guests
    {
        path: '',
        canActivate: [NoAuthGuard],
        canActivateChild: [NoAuthGuard],
        component: LayoutComponent,
        data: {
            layout: 'empty'
        },
        children: [
            { path: 'confirmation-required', loadChildren: () => import('app/modules/auth/confirmation-required/confirmation-required.module').then(m => m.AuthConfirmationRequiredModule) },
            { path: 'forgot-password', loadChildren: () => import('app/modules/auth/forgot-password/forgot-password.module').then(m => m.AuthForgotPasswordModule) },
            { path: 'reset-password', loadChildren: () => import('app/modules/auth/reset-password/reset-password.module').then(m => m.AuthResetPasswordModule) },
            { path: 'sign-in', loadChildren: () => import('app/modules/auth/sign-in/sign-in.module').then(m => m.AuthSignInModule) },
            { path: 'sign-up', loadChildren: () => import('app/modules/auth/sign-up/sign-up.module').then(m => m.AuthSignUpModule) },
            { path: 'email-verify', loadChildren: () => import('app/modules/auth/email-verify/email-verify.module').then(m => m.EmailVerifyModule) },
            { path: 'business-login', loadChildren: () => import('app/modules/admin/login-page/login-page.module').then(m => m.LoginPageModule) },
        ]
    },
    // Auth routes for authenticated users
    {
        path: '',
        canActivate: [AuthGuard],
        canActivateChild: [AuthGuard],
        component: LayoutComponent,
        data: {
            layout: 'empty'
        },
        children: [
            { path: 'sign-out', loadChildren: () => import('app/modules/auth/sign-out/sign-out.module').then(m => m.AuthSignOutModule) },
            { path: 'unlock-session', loadChildren: () => import('app/modules/auth/unlock-session/unlock-session.module').then(m => m.AuthUnlockSessionModule) }
        ]
    },


    // Admin routes
    {
        path: '',
        canActivate: [AuthGuard],
        canActivateChild: [AuthGuard],
        component: LayoutComponent,
        resolve: {
            initialData: InitialDataResolver,
        },
        children: [
            { path: 'dashboard', loadChildren: () => import('app/modules/admin/dashboard/dashboard.module').then(m => m.DashboardModule) },
            { path: 'crm-dashboard', loadChildren: () => import('app/modules/admin/new-dashboard/new-dashboard.module').then(m => m.NewDashboardModule) },
            // { path: 'time-sheet', loadChildren: () => import('app/modules/admin/users-reports/time-sheet/time-sheet.module').then(m => m.TimeSheetModule) },
            { path: 'announcement', loadChildren: () => import('app/modules/admin/announcements/announcements.module').then(m => m.AnnouncementsModule) },
            { path: 'job-tracker', loadChildren: () => import('app/modules/admin/job-tracker/job-tracker.module').then(m => m.JobTrackerModule) },
            // { path: 'reports/:reportType', loadChildren: () => import('app/modules/admin/all-reports/all-reports.module').then(m => m.AllReportsModule) },
            { path: 'communication', loadChildren: () => import('app/modules/admin/communication/communication.module').then(m => m.CommunicationModule) },
            { path: 'communication/:communiType', loadChildren: () => import('app/modules/admin/announcements/announcements.module').then(m => m.AnnouncementsModule) },
            { path: 'roster-two', loadChildren: () => import('app/modules/admin/ento-design/ento-design.module').then(m => m.EntoDesignModule) },
            { path: 'roster-design-three', loadChildren: () => import('app/modules/admin/roster-three/roster-three.module').then(m => m.RosterThreeModule) },
            { path: 'roster-four-design', loadChildren: () => import('app/modules/admin/roster-four/roster-four.module').then(m => m.RosterFourModule) },
            { path: 'ento-fortnight', loadChildren: () => import('app/modules/admin/ento-fortnight-design/ento-fortnight-design.module').then(m => m.EntoFortnightDesignModule) },
            { path: 'users/admins', loadChildren: () => import('app/modules/admin/admins/admins.module').then(m => m.AdminsModule) },
            { path: 'users/sales-person', loadChildren: () => import('app/modules/admin/admins/admins.module').then(m => m.AdminsModule) },
            { path: 'users/customers', loadChildren: () => import('app/modules/admin/customer/customer.module').then(m => m.CustomerModule) },
            { path: 'users/contractor', loadChildren: () => import('app/modules/admin/contractor/contractor.module').then(m => m.ContractorModule) },
            { path: 'users/potential-new-staff', loadChildren: () => import('app/modules/admin/potetial-new-guard/potetial-new-guard.module').then(m => m.PotetialNewGuardModule) },
            { path: 'users/staff', loadChildren: () => import('app/modules/admin/users/users.module').then(m => m.UsersModule) },
            { path: 'staff-messages', loadChildren: () => import('app/modules/admin/staff-messages/staff-messages.module').then(m => m.StaffMessagesModule) },
            { path: 'Log-user-activities', loadChildren: () => import('app/modules/admin/log-user-activities/log-user-activities.module').then(m => m.LogUserActivitiesModule) },
            { path: 'staff-documentation', loadChildren: () => import('app/modules/admin/guard-license/guard-license.module').then(m => m.GuardLicenseModule) },
            { path: 'sms-portal/chat-history', loadChildren: () => import('app/modules/admin/chat-history/chat-history.module').then(m => m.ChatHistoryModule) },
            { path: 'sms-portal/quick-sms', loadChildren: () => import('app/modules/admin/quicksms/quicksms.module').then(m => m.QuicksmsModule) },
            { path: 'sms-portal/sms-portal-history', loadChildren: () => import('app/modules/admin/smsportal-history/smsportal-history.module').then(m => m.SMSPortalHistoryModule) },
            { path: 'sms-portal/template', loadChildren: () => import('app/modules/admin/template/template.module').then(m => m.TemplateModule) },
            { path: 'email-portal/quick-email', loadChildren: () => import('app/modules/admin/quick-email/quick-email.module').then(m => m.QuickEmailModule) },
            { path: 'email-portal/email-template', loadChildren: () => import('app/modules/admin/email-template/email-template.module').then(m => m.EmailTemplateModule) },
            { path: 'email-portal/email-signature', loadChildren: () => import('app/modules/admin/email-signature/email-signature.module').then(m => m.EmailSignatureModule) },
            { path: 'calls/calls', loadChildren: () => import('app/modules/admin/calling/calling.module').then(m => m.CallingModule) },
            { path: 'calls/dashboard-calls', loadChildren: () => import('app/modules/admin/call-dashboard/call-dashboard.module').then(m => m.CallDashboardModule) },
            { path: 'calls/calls-history', loadChildren: () => import('app/modules/admin/calling-history/calling-history.module').then(m => m.CallingHistoryModule) },
            { path: 'form-builder', loadChildren: () => import('app/modules/admin/form-builder/form-builder.module').then(m => m.FormBuilderModule) },
            { path: 'myforms', loadChildren: () => import('app/modules/admin/custom-forms/custom-forms.module').then(m => m.CustomFormsModule) },
            { path: 'email-settings', loadChildren: () => import('app/modules/admin/email-settings/email-settings.module').then(m => m.EmailSettingsModule) },
            // { path: 'reports', loadChildren: () => import('app/modules/admin/reports/reports.module').then(m => m.ReportsModule) },
            { path: 'reports', loadChildren: () => import('app/modules/admin/users-reports/users-reports.module').then(m => m.UsersReportsModule) },
            { path: 'users', pathMatch: 'full', redirectTo: 'users/staff' }, { path: 'guard-staff', loadChildren: () => import('app/modules/admin/guard-staff/guard-staff.module').then(m => m.GuardStaffModule) },
            { path: 'portal-setting', loadChildren: () => import('app/modules/admin/portal-setting/portal-setting.module').then(m => m.PortalSettingModule) },
            { path: 'rates', loadChildren: () => import('app/modules/admin/rates/rates.module').then(m => m.RatesModule) },
            { path: 'accounts', loadChildren: () => import('app/modules/admin/accounts/accounts.module').then(m => m.AccountsModule) },
            { path: 'sms-portal', loadChildren: () => import('app/modules/admin/sms-portal/sms-portal.module').then(m => m.SmsPortalModule) },
            { path: 'email-portal', loadChildren: () => import('app/modules/admin/email-portal/email-portal.module').then(m => m.EmailPortalModule) },
            { path: 'calls', loadChildren: () => import('app/modules/admin/calls/calls.module').then(m => m.CallsModule) },
            { path: 'crm', loadChildren: () => import('app/modules/admin/crm/crm.module').then(m => m.CrmModule) },
            { path: 'stagging', loadChildren: () => import('app/modules/admin/scrumboard/scrumboard.module').then(m => m.ScrumboardModule) },
            { path: 'staff-logs', loadChildren: () => import('app/modules/admin/staff-logs/staff-logs.module').then(m => m.StaffLogsModule) },

            // new experience with route 
            { path: 'operations', loadChildren: () => import('app/modules/admin/operations-modules/operations-modules.module').then(m => m.OperationsModulesModule) },


            { path: 'mails', loadChildren: () => import('app/modules/admin/emails/emails.module').then(m => m.EmailsModule) },
            { path: 'email', loadChildren: () => import('app/modules/admin/outlook/outlook.module').then(m => m.OutlookModule) },
            { path: 'profile', loadChildren: () => import('app/modules/admin/profile-page/profile-page.module').then(m => m.ProfilePageModule) },
            { path: 'users/file-manager', loadChildren: () => import('app/modules/admin/file-manager/file-manager.module').then(m => m.FileManagerModule) },

            // customer and contactor and other
            { path: 'document-setting', loadChildren: () => import('app/modules/admin/other-user-common-card/other-user-common-card.module').then(m => m.OtherUserCommonCardModule) },
            { path: 'tasks', loadChildren: () => import('app/modules/admin/tasks/tasks.module').then(m => m.TasksModule) },
            { path: 'support', loadChildren: () => import('app/modules/admin/settings/settings.module').then(m => m.SettingsModule) },
            { path: 'google-authenticator', loadChildren: () => import('app/modules/admin/google-authenticator/google-authenticator.module').then(m => m.GoogleAuthenticatorModule) },
            { path: 'chat-app', loadChildren: () => import('app/modules/admin/chat-app/chat-app.module').then(m => m.ChatAppModule) },
            { path: 'induction/:id', loadChildren: () => import('app/modules/admin/induction/induction.module').then(m => m.InductionModule) },
            { path: 'company-documents', loadChildren: () => import('app/modules/admin/company-doc/company-doc.module').then(m => m.CompanyDocModule) },
            { path: 'files-list/:id/:subfolder', loadChildren: () => import('app/modules/admin/files-list/files-list.module').then(m => m.FilesListModule) },
            { path: 'runsheet-job-roster/:id', loadChildren: () => import('app/modules/admin/runsheet-job-roster/runsheet-job-roster.module').then(m => m.RunsheetJobRosterModule) },
            { path: 'users/patrol-car-details', loadChildren: () => import('app/modules/admin/patrol-car-details/patrol-car-details.module').then(m => m.PatrolCarDetailsModule) },
            { path: 'mailbox', loadChildren: () => import('app/modules/admin/mailbox/mailbox.module').then(m => m.MailboxModule) },
            // { path: 'three', loadChildren: () => import('app/modules/admin/roster-three/roster-three.module').then(m => m.RosterThreeModule) },
            // { path: 'four', loadChildren: () => import('app/modules/admin/roster-four/roster-four.module').then(m => m.RosterFourModule) },
            // { path: 'ento', loadChildren: () => import('app/modules/admin/ento-design/ento-design.module').then(m => m.EntoDesignModule) },

        ]

    },
]; 

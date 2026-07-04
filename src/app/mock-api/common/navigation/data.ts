

/* tslint:disable:max-line-length */
import { FuseNavigationItem } from '@fuse/components/navigation';

export const defaultNavigation: FuseNavigationItem[] = [


];
export const compactNavigation: FuseNavigationItem[] = [

    // ChildPage: child page mean if secific sidebar page have some page that we can acces to specific page
    // Action: action mean if sidebar page have some action like crud
    // this child name should be matched all users component array users
    {
        id: 'dashboard',
        title: 'WFM Dashboard',
        type: 'basic',
        icon: 'mat_outline:dashboard',
        link: '/dashboard',
    },
    {
        id: 'wfm',
        title: 'WFM Tools',
        type: 'basic',
        icon: 'edit_calendar',
        link: '/operations',
        // childPage: ['Staff Roster/Scheduling', 'Locations', 'Staff Documents', 'Leave Management', 'Visa Status', 'New Entry/Query', 'APP Status', 'Time Clock', 'Internal Staff Activity Log', 'Runsheet', 'Patrol Roster', 'Alarm Dispatch System']
        // childPage: ['Staff Roster/Scheduling', 'Locations', 'Staff Documents', 'Leave Management', 'Visa Status', 'New Entry/Query', 'APP Status', 'Time Clock', 'Internal Staff Activity Log']
        childPage: ['Staff Roster/Scheduling', 'Branches']
    },
    // {
    //     id: 'crm',
    //     title: 'CRM',
    //     type: 'basic',
    //     icon: 'mat_outline:account_tree',
    //     link: '/crm-dashboard',
    //     childPage: ['Leads', 'Financials', 'Tasks']
    // },
    {
        id: 'allUsers',
        title: 'Onboarding',
        type: 'basic',
        icon: 'mat_outline:supervisor_account',
        link: '/users',
        // childPage: ['Admins', 'Customers', 'Contractors', 'Sales Person', 'Other Staff', 'Potential Staff', 'File Manager', 'Patrol Car Details']
        childPage: ['Other Staff']
    },
    // {
    //     id: 'staff logs',
    //     title: 'Staff Logs',
    //     type: 'basic',
    //     icon: 'mat_outline:supervisor_account',
    //     link: '/staff-logs',
    //     childPage: ['APP Status', 'Time Clock', 'Internal Staff Activity Log']
    // },
    {
        id: 'reports',
        title: 'Reports',
        type: 'basic',
        icon: 'mat_outline:report',
        link: '/reports',
        // childPage: ['SignIn-Out Report', 'Quick Paysheet', 'Multi Report', 'Audit Report', 'Admin Log', 'Green & Welfare Call', 'Profit & Loss Report', 
        //     'Leave Report', 'CRM Overview Report', 'CRM Customer Report', 'Monthly PnL Report', 'Adhoc Hours Shift Report']
        childPage: ['Staff Report', 'Complete Paysheet', 'Time Sheet']
    },
    // {
    //     id: 'communication',
    //     title: 'Communications',
    //     type: 'basic',
    //     icon: 'mat_outline:contact_mail',
    //     link: '/communication',
    //     // childPage: ['Chat Box', 'Announcement', 'Induction', 'Quick SMS', 'SMS Portal History', 'Template', 'Quick Email', 'Email Template', 'Email Signature', 'Outlook', 'Chat History', 'Calls Dashboard', 'Calls History', 'Calls Recordings']
    //     childPage: ['Announcement', 'Induction']
    // },
    {
        id: 'accounts',
        title: 'Accounts',
        type: 'basic',
        icon: 'heroicons_outline:currency-dollar',
        link: '/accounts',
        // childPage: ['Charge Rates', 'Pay Rates', 'Invoice', 'Award Pay Rates']
        childPage: ['Charge Rates', 'Pay Rates', 'Award Rates', 'Upload Payslip']
    },
    // {
    //     id: 'myforms',
    //     title: 'Forms',
    //     type: 'basic',
    //     icon: 'mat_outline:folder_open',
    //     link: '/myforms',
    //     action: ['create', 'read', 'update', 'delete'],
    // },
    // {
    //     id: 'Company Documents',
    //     title: 'Company Documents',
    //     type: 'basic',
    //     icon: 'mat_outline:folder_open',
    //     link: '/company-documents',
    //     action: ['create', 'read', 'update', 'delete'],

    // },
    {
        id: 'Settings',
        title: 'Settings',
        type: 'basic',
        icon: 'mat_outline:settings',
        link: '/portal-setting',
        // childPage: ['Company Profile', 'Color Settings', 'Roles & Permissions', 'Api Settings', 'Public Holidays', 'Compliance'],
        childPage: ['Color Settings', 'Roles & Permissions', 'Public Holidays'],
    },
    // {
    //     id: 'Document Setting',
    //     title: 'Document Setting',
    //     type: 'basic',
    //     icon: 'heroicons_outline:view-boards',
    //     link: '/document-setting',
    //     childPage: ['Personal Detail & Documents'],
    // },
];
export const futuristicNavigation: FuseNavigationItem[] = [

];
export const horizontalNavigation: FuseNavigationItem[] = [
    // {
    //     id: 'dashboard',
    //     title: 'Dashboard',
    //     type: 'basic',
    //     icon: 'mat_outline:dashboard',
    //     link: '/crm-dashboard'
    // },
    // {
    //     id: 'time-sheet',
    //     title: 'Time Sheet',
    //     type: 'basic',
    //     icon: 'mat_outline:access_time',
    //     link: '/time-sheet'
    // },
    // {
    //     id: 'sites',
    //     title: 'Sites',
    //     type: 'basic',
    //     icon: 'mat_outline:work_outline',
    //     link: '/sites'
    // },
    // {
    //     id: 'activity-log',
    //     title: 'Activity Log',
    //     type: 'basic',
    //     icon: 'mat_outline:timer',
    //     link: '/activity-log'
    // },
    // {
    //     id: 'leave-management',
    //     title: 'Leave Management',
    //     type: 'basic',
    //     icon: 'mat_outline:access_time',
    //     link: '/leave-management'
    // },
    // {
    //     id: 'time-clock',
    //     title: 'Time Clock',
    //     type: 'basic',
    //     icon: 'mat_outline:access_time',
    //     link: '/time-clock'
    // },
    // {
    //     id: 'allUsers',
    //     title: 'Users',
    //     type: 'aside',
    //     icon: 'heroicons_outline:user-group',
    //     children: [
    //         {
    //             id: 'admins',
    //             title: 'Administrators',
    //             type: 'basic',
    //             icon: 'mat_outline:supervisor_account',
    //         },
    //         {
    //             id: 'customer',
    //             title: 'Customers',
    //             type: 'basic',
    //             icon: 'mat_outline:supervisor_account',
    //         },

    //         {
    //             id: 'contactor',
    //             title: 'Contactor',
    //             type: 'basic',
    //             icon: 'mat_outline:supervisor_account',
    //         },
    //         {
    //             id: 'permission',
    //             title: 'Permissions',
    //             type: 'basic',
    //             icon: 'mat_outline:verified_user',
    //         },
    //     ]
    // },
    // {
    //     id: 'guard-staff',
    //     title: 'Staff',
    //     type: 'aside',
    //     icon: 'heroicons_outline:user-group',
    //     children: [
    //         {
    //             id: 'staff',
    //             title: 'Current Staff',
    //             type: 'basic',
    //             icon: 'mat_outline:supervisor_account',
    //         },
    //         {
    //             id: 'potential-new-guard',
    //             title: 'Potential New Staff',
    //             type: 'basic',
    //             icon: 'mat_outline:supervisor_account',
    //         },
    //     ]
    // },

    // {
    //     id: 'communication',
    //     title: 'Communication',
    //     type: 'basic',
    //     icon: 'mat_outline:contact_mail',
    //     link: '/communication'
    // },
    // {
    //     id: 'rates',
    //     title: 'Rates',
    //     type: 'aside',
    //     icon: 'heroicons_outline:currency-dollar',
    //     children: [
    //         {
    //             id: 'chargerates',
    //             title: 'Charge Rates',
    //             type: 'basic',
    //             icon: 'heroicons_outline:currency-dollar',
    //         },
    //         {
    //             id: 'payrates',
    //             title: 'Pay Rates',
    //             type: 'basic',
    //             icon: 'heroicons_outline:currency-dollar',
    //         },
    //     ]
    // },
    // {
    //     id: 'reports',
    //     title: 'Reports',
    //     type: 'aside',
    //     icon: 'mat_outline:report',
    //     children: [
    //         {
    //             id: 'taskreport',
    //             title: 'Task Report',
    //             type: 'basic',
    //             icon: 'mat_outline:auto_graph',
    //         },
    //         {
    //             id: 'contractorreport',
    //             title: 'Contractor Report',
    //             type: 'basic',
    //             icon: 'mat_outline:auto_graph',
    //         },
    //         {
    //             id: 'customerreport',
    //             title: 'Customer Report',
    //             type: 'basic',
    //             icon: 'mat_outline:auto_graph',
    //         },
    //         {
    //             id: 'guardreport',
    //             title: 'Staff Report',
    //             type: 'basic',
    //             icon: 'mat_outline:auto_graph',
    //         },
    //         {
    //             id: 'invoicereport',
    //             title: 'Invoice Report',
    //             type: 'basic',
    //             icon: 'mat_outline:auto_graph',
    //         },
    //         {
    //             id: 'quickpaysheet',
    //             title: 'Quick Paysheet',
    //             type: 'basic',
    //             icon: 'mat_outline:auto_graph',
    //         },
    //         {
    //             id: 'incidentreportpage',
    //             title: 'Incident Report Page',
    //             type: 'basic',
    //             icon: 'mat_outline:auto_graph',
    //         },
    //         {
    //             id: 'completepaysheet',
    //             title: 'Complete Paysheet',
    //             type: 'basic',
    //             icon: 'mat_outline:auto_graph',
    //         },
    //         {
    //             id: 'divisionconsolidation',
    //             title: 'Division Consolidation',
    //             type: 'basic',
    //             icon: 'mat_outline:auto_graph',
    //         },
    //     ]

    // },

    // {
    //     id: 'Portal Settings',
    //     title: 'Portal Settings',
    //     type: 'basic',
    //     icon: 'mat_outline:settings',
    //     children: [

    //         {
    //             id: 'colorsettings',
    //             title: 'Color Settings',
    //             type: 'basic',
    //             icon: 'mat_outline:settings_suggest',
    //         },
    //     ]
    // },
];

export interface Mail {
    id?: string;
    type?: string;
    from?: {
        avatar?: string;
        contact?: string;
        emailAddress?:{address?:string};
    };
    to?: string;
    cc?: string[];
    ccCount?: number;
    bcc?: string[];
    bccCount?: number;
    date?: string;
    subject?: string;
    content?: string;
    attachments?: {
        type?: string;
        name?: string;
        size?: number;
        preview?: string;
        downloadUrl?: string;
    }[];
    starred?: boolean;
    important?: boolean;
    unread?: boolean;
    folder?: string;
    labels?: string[];
    isRead?: boolean;
    isDraft?: boolean;
    conversationId?: string;
    importance?: string;
    categories?: any;
    ccRecipients?: any[]
    bccRecipients?: any[]
    hasAttachments?: boolean;
    flag?: {
        flagStatus?: string;
    };
    body?: {
        contentType?: string;
        content?: string;
    };
    toRecipients?: {
        emailAddress: {
            address: string;
        }
    }[];
}


export interface MailCategory {
    type: 'folder' | 'filter' | 'label';
    name: string;
}

export interface MailFolder {
    id: string;
    title?: string; // title is used as displayName in your component, ensure consistency
    slug?: string;
    icon?: string;
    count?: number;
    childFolderCount?: number;
    displayName?: string;
    isHidden?: boolean;
    parentFolderId?: string;
    sizeInBytes?: number;
    totalItemCount?: number;
    unreadItemCount?: number;
}

export interface MailFilter {
    id: string;
    title: string;
    slug: string;
    icon: string;
    displayName?: string;
    totalItemCount?: number;
    unreadItemCount?: number;   
}

export interface MailLabel {
    id: string;
    title: string;
    slug: string;
    color: string;
}

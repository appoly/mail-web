// Email address interface
export interface EmailAddress {
    name: string;
    address: string;
}

export interface EmailAttachment {
    id: string;
    name: string;
    path: string;
    human_readable_size: string;
}

// Email interface for EmailPreview component
export interface EmailPreview {
    id: string;
    subject: string;
    from: EmailAddress[];
    to: EmailAddress[];
    body_html: string;
    body_text: string;
    read: number;
    share_enabled: number;
    share_url?: string;
    created_at: string;
    updated_at: string;
    attachments?: EmailAttachment[];
}

// Generic Email interface that can be used across components
export interface Email {
    id: string;
    subject: string;
    from: EmailAddress[];
    to: EmailAddress[];
    cc?: EmailAddress[];
    bcc?: EmailAddress[];
    body_html: string;
    body_text: string;
    read: number;
    share_enabled: number;
    created_at: string;
    updated_at: string;
    headers?: string;
    share_url?: string;
    attachments_count?: number;
}

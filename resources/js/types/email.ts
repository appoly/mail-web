// Email address interface
export interface EmailAddress {
    name: string
    address: string
}

// Email interface for EmailDetails component
export interface EmailDetails {
    id: string
    subject: string
    from: EmailAddress[]
    to: EmailAddress[]
    cc?: EmailAddress[]
    bcc?: EmailAddress[]
    body_html: string
    body_text: string
    read: number
    share_enabled: number
    share_url?: string
    created_at: string
    updated_at: string
    headers?: string
    attachments?: { filename: string; size: string }[]
    events?: { type: string; description: string; timestamp: string }[]
}

// Email interface for EmailPreview component
export interface EmailPreview {
    id: string
    subject: string
    from: EmailAddress[]
    to: EmailAddress[]
    body_html: string
    body_text: string
    read: number
    share_enabled: number
    share_url?: string
    created_at: string
    updated_at: string
    attachments?: any[]
}

// Generic Email interface that can be used across components
export interface Email {
    id: string
    subject: string
    from: EmailAddress[]
    to: EmailAddress[]
    cc?: EmailAddress[]
    bcc?: EmailAddress[]
    body_html: string
    body_text: string
    read: number
    share_enabled: number
    created_at: string
    updated_at: string
    headers?: string
    share_url?: string
    attachments?: any[]
    events?: { type: string; description: string; timestamp: string }[]
}

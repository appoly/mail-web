// Email interface for EmailDetails component
export interface EmailDetails {
    id: string
    subject: string
    from: string
    to: string
    cc?: string
    bcc?: string
    date: string
    status: string
    headers?: string
    attachments?: { filename: string; size: string }[]
    events?: { type: string; description: string; timestamp: string }[]
}

// Email interface for EmailPreview component
export interface EmailPreview {
    id: number | string
    subject: string
    from: string
    to: string
    date: string
    read: boolean
    attachments: any[]
    content: string
}

// Generic Email interface that can be used across components
export interface Email {
    id: number | string
    subject: string
    from: string
    to: string
    date: string
    cc?: string
    bcc?: string
    read?: boolean
    status?: string
    headers?: string
    preview?: string
    content?: string
    attachments?: any[]
    events?: { type: string; description: string; timestamp: string }[]
}

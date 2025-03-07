<script setup lang="ts">
import { AlertCircle } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Separator } from '@/components/ui/separator'
import { Email, EmailAddress } from '@/types/email'
import { formatDistanceToNow, parseISO } from 'date-fns'

defineProps<{
    emails: Array<Email>,
    selectedEmail: Email | null,
    isLoading: boolean,
    error: Error | null,
    isMobile: boolean
}>()

defineEmits(['update:selectedEmail'])

// Helper function to format email addresses
const formatEmailAddresses = (addresses: EmailAddress[]): string => {
    return addresses.map(addr => addr.name ? `${addr.name} <${addr.address}>` : addr.address).join(', ')
}

// Helper function to format date
const formatDate = (dateString: string): string => {
    try {
        return formatDistanceToNow(parseISO(dateString), { addSuffix: true })
    } catch (e) {
        return dateString
    }
}

// Helper function to truncate text
const truncateText = (text: string, maxLength: number = 60): string => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, 57) + '...';
}
</script>

<template>
    <div class="flex h-full flex-col">
        <div v-if="error" class="text-destructive p-4 text-center">
            <AlertCircle class="h-8 w-8 mx-auto mb-2" />
            <p>Failed to load emails</p>
            <p class="text-sm text-muted-foreground">{{ error.message }}</p>
        </div>

        <template v-else>
            <div class="flex items-center justify-between p-4">
                <h2 class="text-lg font-semibold">Emails</h2>
                <div class="text-sm text-muted-foreground">{{ emails.length }} emails</div>
            </div>
            <Separator />
            
            <div v-if="isLoading" class="p-4 space-y-4">
                <div v-for="(_, i) in 10" :key="i" class="space-y-2">
                    <Skeleton class="h-4 w-3/4" />
                    <Skeleton class="h-4 w-1/2" />
                    <Skeleton class="h-4 w-1/4" />
                </div>
            </div>

            <div v-else-if="emails.length === 0" class="p-8 text-center text-muted-foreground">
                <p>No emails found</p>
            </div>

            <ScrollArea v-else class="flex-1">
                <div class="flex flex-col">
                    <div
                        v-for="email in emails"
                        :key="email.id"
                        :class="[
                            'flex cursor-pointer flex-col gap-1 border-b p-4 hover:bg-muted/50',
                            selectedEmail?.id === email.id ? 'bg-muted' : ''
                        ]"
                        @click="$emit('update:selectedEmail', email)"
                    >
                        <div class="flex items-center justify-between">
                            <div class="font-medium">{{ formatEmailAddresses(email.to) }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ formatDate(email.created_at) }}
                            </div>
                        </div>
                        <div class="text-sm font-medium">{{ email.subject }}</div>
                        <div class="text-xs text-muted-foreground">{{ truncateText(email.body_text) }}</div>
                    </div>
                </div>
            </ScrollArea>
        </template>
    </div>
</template>
